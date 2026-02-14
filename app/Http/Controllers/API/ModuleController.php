<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ObraModulo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ModuleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ObraModulo::activos()->with('items.recursos');
        
        if ($request->has('search')) {
            $query->where('nombre', 'like', "%{$request->search}%");
        }
        
        $modulos = $query->paginate($request->get('per_page', 25));
        // En el método index, después de obtener los módulos:
        $modulos->each(function ($modulo) {
            $modulo->precio_total = $modulo->items->sum('precio_base');
        });
        
        return response()->json([
            'data' => $modulos->items(),
            'meta' => [
                'current_page' => $modulos->currentPage(),
                'last_page' => $modulos->lastPage(),
                'per_page' => $modulos->perPage(),
                'total' => $modulos->total()
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|unique:obra_modulos,codigo',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean', // 👈 Añadir validación para activo
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:obra_items,id',
            'items.*.orden' => 'nullable|integer',
            'items.*.rendimiento' => 'nullable|numeric|min:0'
        ]);

        $modulo = ObraModulo::create([
            'codigo' => $validated['codigo'],
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null,
            'activo' => $validated['activo'] ?? true // 👈 Por defecto activo
        ]);

        // Adjuntar items con orden
        // 👇 AÑADIR ITEMS CON RENDIMIENTO
        $itemsData = [];
        foreach ($validated['items'] as $index => $item) {
            $itemsData[$item['item_id']] = [
                'orden' => $item['orden'] ?? $index,
                'rendimiento' => $item['rendimiento'] ?? 0 // 👈 AÑADIR RENDIMIENTO
            ];
        }
        $modulo->items()->attach($itemsData);
        /*$itemsData = [];
        foreach ($validated['items'] as $index => $item) {
            $itemsData[$item['item_id']] = ['orden' => $item['orden'] ?? $index];
        }
        $modulo->items()->attach($itemsData);*/

        return response()->json([
            'message' => 'Módulo creado exitosamente',
            'data' => $modulo->load('items.recursos')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ObraModulo $modulo)
    {
        if (!$modulo->activo) {
            return response()->json(['error' => 'Módulo no encontrado'], 404);
        }
        // O en el método show:
        $modulo->precio_total = $modulo->items->sum('precio_base');
        
        return response()->json(['data' => $modulo->load('items.recursos')]);
    }

    /**
     * Update the specified resource in storage.
     */
    
    public function update(Request $request, $id)
    {
        $modulo = ObraModulo::findOrFail($id);
        
        $validated = $request->validate([
            'codigo' => [
                'sometimes',
                'required',
                'string',
                Rule::unique('obra_modulos', 'codigo')->ignore($modulo->id)
            ],
            'nombre' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean', // 👈 Añadir validación para activo
            'items' => 'sometimes|array',
            'items.*.item_id' => 'required_with:items|exists:obra_items,id',
            'items.*.orden' => 'nullable|integer',
            'items.*.rendimiento' => 'nullable|numeric|min:0'
        ]);

        // Actualizar campos básicos
        $updateData = [];
        if (isset($validated['codigo'])) {
            $updateData['codigo'] = $validated['codigo'];
        }
        if (isset($validated['nombre'])) {
            $updateData['nombre'] = $validated['nombre'];
        }
        if (isset($validated['descripcion'])) {
            $updateData['descripcion'] = $validated['descripcion'];
        }
        if (isset($validated['activo'])) { // 👈 Añadir activo a los datos de actualización
            $updateData['activo'] = $validated['activo'];
        }
        
        if (!empty($updateData)) {
            $modulo->update($updateData);
        }

        // Actualizar items si existen
        /*if (isset($validated['items']) && !empty($validated['items'])) {
            $modulo->items()->detach();
            $itemsData = [];
            foreach ($validated['items'] as $index => $item) {
                $itemsData[$item['item_id']] = ['orden' => $item['orden'] ?? $index];
            }
            $modulo->items()->attach($itemsData);
        }*/
            // 👇 ACTUALIZAR ITEMS CON RENDIMIENTO
        if (isset($validated['items']) && !empty($validated['items'])) {
            $modulo->items()->detach();
            $itemsData = [];
            foreach ($validated['items'] as $index => $item) {
                $itemsData[$item['item_id']] = [
                    'orden' => $item['orden'] ?? $index,
                    'rendimiento' => $item['rendimiento'] ?? 0 // 👈 AÑADIR RENDIMIENTO
                ];
            }
            $modulo->items()->attach($itemsData);
        }

        return response()->json([
            'message' => 'Módulo actualizado exitosamente',
            'data' => $modulo->load('items.recursos')
        ]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ObraModulo $modulo)
    {
        // Verificar si está en uso en categorías
        if ($modulo->categorias()->exists()) {
            throw ValidationException::withMessages([
                'error' => 'No se puede eliminar el módulo porque está en uso en categorías.'
            ]);
        }

        $modulo->delete();

        return response()->json(['message' => 'Módulo eliminado exitosamente']);
    }

    /**
     * Reorder items within module
     */
    public function reorderItems(Request $request, ObraModulo $modulo)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:obra_items,id',
            'items.*.orden' => 'required|integer'
        ]);

        $modulo->items()->detach();
        $itemsData = [];
        foreach ($request->items as $item) {
            $itemsData[$item['item_id']] = ['orden' => $item['orden']];
        }
        $modulo->items()->attach($itemsData);

        return response()->json([
            'message' => 'Items reordenados exitosamente',
            'data' => $modulo->load('items.recursos')
        ]);
    }
}
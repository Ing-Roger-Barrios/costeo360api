<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ObraItem;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ObraItem::activos()->with('recursos');
        
        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('codigo', 'like', "%{$request->search}%")
                  ->orWhere('descripcion', 'like', "%{$request->search}%");
            });
        }
        
        $items = $query->paginate($request->get('per_page', 25));
        
        return response()->json([
            'data' => $items->items(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total()
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|unique:obra_items,codigo',
            'descripcion' => 'required|string',
            'unidad' => 'required|string|max:50',
            'notas' => 'nullable|string',
            'recursos' => 'required|array|min:1',
            'recursos.*.recurso_id' => 'required|exists:obra_recursos_maestros,id',
            'recursos.*.rendimiento' => 'required|numeric|min:0'
        ]);

        $item = ObraItem::create([
            'codigo' => $validated['codigo'],
            'descripcion' => $validated['descripcion'],
            'unidad' => $validated['unidad'],
            'activo' => true, // 👈 Los items se crean activos por defecto
            'notas' => $validated['notas'] ?? null
        ]);

        // Adjuntar recursos
        $recursosData = [];
        foreach ($validated['recursos'] as $recurso) {
            $recursosData[$recurso['recurso_id']] = ['rendimiento' => $recurso['rendimiento']];
        }
        $item->recursos()->attach($recursosData);

        return response()->json([
            'message' => 'Item creado exitosamente',
            'data' => $item->load('recursos')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ObraItem $item)
    {
        if (!$item->activo) {
            return response()->json(['error' => 'Item no encontrado'], 404);
        }
        
        return response()->json(['data' => $item->load('recursos')]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ObraItem $item)
    {
        $validated = $request->validate([
            'codigo' => 'sometimes|required|string|unique:obra_items,codigo,' . $item->id,
            'descripcion' => 'sometimes|required|string',
            'unidad' => 'sometimes|required|string|max:50',
            'notas' => 'nullable|string',
            'activo' => 'sometimes|boolean', // 👈 CLAVE
            'recursos' => 'sometimes|array',
            'recursos.*.recurso_id' => 'required_with:recursos|exists:obra_recursos_maestros,id',
            'recursos.*.rendimiento' => 'required_with:recursos|numeric|min:0'
        ]);

        $item->update([
            'codigo' => $validated['codigo'] ?? $item->codigo,
            'descripcion' => $validated['descripcion'] ?? $item->descripcion,
            'unidad' => $validated['unidad'] ?? $item->unidad,
            'notas' => $validated['notas'] ?? $item->notas,
            'activo' => $validated['activo'] ?? $item->activo // 👈 CLAVE
        ]);

        // Actualizar recursos si se proporcionan
        if (isset($validated['recursos'])) {
            $item->recursos()->detach();
            $recursosData = [];
            foreach ($validated['recursos'] as $recurso) {
                $recursosData[$recurso['recurso_id']] = ['rendimiento' => $recurso['rendimiento']];
            }
            $item->recursos()->attach($recursosData);
        }

        return response()->json([
            'message' => 'Item actualizado exitosamente',
            'data' => $item->load('recursos')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ObraItem $item)
    {
        // Verificar si está en uso en algún módulo
        if ($item->modulos()->exists()) {
            throw ValidationException::withMessages([
                'error' => 'No se puede eliminar el item porque está en uso en módulos.'
            ]);
        }

        $item->delete();

        return response()->json(['message' => 'Item eliminado exitosamente']);
    }
}
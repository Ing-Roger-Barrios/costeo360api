<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\ObraCategoria;
use App\Models\Version;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ObraCategoria::activos()->with('modulos.items.recursos');
        
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('codigo', 'like', "%{$request->search}%")
                  ->orWhere('nombre', 'like', "%{$request->search}%");
            });
        }
        
        $categorias = $query->paginate($request->get('per_page', 25));
        $categorias->each(function ($categoria) {
            $categoria->precio_total = $categoria->modulos->sum('precio_total');
        });
        
        return response()->json([
            'data' => $categorias->items(),
            'meta' => [
                'current_page' => $categorias->currentPage(),
                'last_page' => $categorias->lastPage(),
                'per_page' => $categorias->perPage(),
                'total' => $categorias->total()
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $validated = $request->validate([
        'codigo' => 'required|string|unique:obra_categorias,codigo',
        'nombre' => 'required|string|max:255',
        'descripcion' => 'nullable|string',
        'activo' => 'boolean'
        // ❌ ELIMINAR la validación de módulos aquí
    ]);

    $categoria = ObraCategoria::create([
        'codigo' => $validated['codigo'],
        'nombre' => $validated['nombre'],
        'descripcion' => $validated['descripcion'],
        'activo' => $validated['activo'] ?? true
    ]);

    // ✅ NO procesar módulos en este método
    // Los módulos se crearán en el proceso de importación separado

    return response()->json([
        'message' => 'Categoría creada exitosamente',
        'data' => $categoria
    ], 201);
}


    /**
     * Display the specified resource.
     */
    public function show(ObraCategoria $categoria)
    {
        if (!$categoria->activo) {
            return response()->json(['error' => 'Categoría no encontrada'], 404);
        }
        $categoria->precio_total = $categoria->modulos->sum('precio_total');
        return response()->json(['data' => $categoria->load('modulos.items.recursos')]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $categoria = ObraCategoria::findOrFail($id);
        
        $validated = $request->validate([
            'codigo' => ['sometimes', 'required', 'string', Rule::unique('obra_categorias', 'codigo')->ignore($categoria->id)],
            'nombre' => 'sometimes|required|string|max:255',
            'activo' => 'boolean',
            'modulos' => 'sometimes|array',
            'modulos.*.modulo_id' => 'required_with:modulos|exists:obra_modulos,id'
        ]);

        $updateData = [];
        if (isset($validated['codigo'])) $updateData['codigo'] = $validated['codigo'];
        if (isset($validated['nombre'])) $updateData['nombre'] = $validated['nombre'];
        if (isset($validated['activo'])) $updateData['activo'] = $validated['activo'];
        
        if (!empty($updateData)) {
            $categoria->update($updateData);
        }

        if (isset($validated['modulos']) && !empty($validated['modulos'])) {
            $categoria->modulos()->detach();
            $modulosData = [];
            foreach ($validated['modulos'] as $modulo) {
                $modulosData[$modulo['modulo_id']] = [];
            }
            $categoria->modulos()->attach($modulosData);
        }

        return response()->json([
            'message' => 'Categoría actualizada exitosamente',
            'data' => $categoria->load('modulos.items.recursos')
        ]);
    }

    public function destroy(ObraCategoria $categoria)
    {
        $categoria->delete();
        return response()->json(['message' => 'Categoría eliminada exitosamente']);
    }

    /**
     * Get full catalog for desktop app
     */
    public function getFullCatalog(Request $request)
    {
        $versionActiva = Version::activa()->first();
        if (!$versionActiva) {
            return response()->json(['error' => 'No hay versión activa'], 404);
        }

        // Obtener región de la solicitud (opcional)
        $regionId = $request->get('region_id');

        $catalogo = ObraCategoria::activas()
            ->with([
                'modulos.items.recursos' => function($query) use ($versionActiva, $regionId) {
                    $query->select('obra_recursos_maestros.*');
                    if ($regionId) {
                        // Incluir precios regionales de la versión
                        $query->addSelect('version_recurso_region.precio_version_regional as precio_version')
                            ->leftJoin('version_recurso_region', function($join) use ($versionActiva, $regionId) {
                                $join->on('obra_recursos_maestros.id', '=', 'version_recurso_region.obra_recurso_maestro_id')
                                    ->where('version_recurso_region.version_id', $versionActiva->id)
                                    ->where('version_recurso_region.region_id', $regionId);
                            });
                    } else {
                        // Precios globales
                        $query->addSelect('version_recurso_maestro.precio_version')
                            ->join('version_recurso_maestro', 'obra_recursos_maestros.id', '=', 'version_recurso_maestro.obra_recurso_maestro_id')
                            ->where('version_recurso_maestro.version_id', $versionActiva->id);
                    }
                }
            ])
            ->orderBy('nombre')
            ->get();

        return response()->json([
            'version' => $versionActiva->version,
            'last_updated' => $versionActiva->fecha_publicacion,
            'region_id' => $regionId,
            'catalogo' => $catalogo
        ]);
    }
}
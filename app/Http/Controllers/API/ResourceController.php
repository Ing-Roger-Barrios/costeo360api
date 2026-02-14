<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResourceResource;
use App\Models\ObraRecursoMaestro;
use App\Models\Region;
use App\Models\Version;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ResourceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ObraRecursoMaestro::activos();
        
        // Filtros
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        
        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('codigo', 'like', "%{$request->search}%")
                  ->orWhere('nombre', 'like', "%{$request->search}%");
            });
        }
        
        $recursos = $query->paginate($request->get('per_page', 25));
        
        return ResourceResource::collection($recursos);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validar que se proporcione version_id
        $validated = $request->validate([
            'version_id' => 'required|exists:versions,id',
            'codigo' => 'required|string|unique:obra_recursos_maestros,codigo',
            'nombre' => 'required|string',
            'tipo' => 'required|string',
            'unidad' => 'required|string',
            'precio_referencia' => 'required|numeric|min:0',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean'
        ]);

        // Crear el recurso maestro
        $recurso = ObraRecursoMaestro::create([
            'codigo' => $validated['codigo'],
            'nombre' => $validated['nombre'],
            'tipo' => $validated['tipo'],
            'unidad' => $validated['unidad'],
            'precio_referencia' => $validated['precio_referencia'],
            'descripcion' => $validated['descripcion'] ?? null,
            'activo' => $validated['activo'] ?? true
        ]);

        // 👇 ACTUALIZAR LA VERSIÓN ACTIVA CON EL NUEVO PRECIO
        $version = Version::findOrFail($validated['version_id']);
        
        // Si la versión está activa, actualizar los precios
        if ($version->activo) {
            // Actualizar precio en la versión
            $version->recursos()->syncWithoutDetaching([
                $recurso->id => ['precio_version' => $recurso->precio_referencia]
            ]);
        }

        return response()->json([
            'message' => 'Recurso creado exitosamente',
            'data' => new ResourceResource($recurso)
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ObraRecursoMaestro $resource)
    {
        if (!$resource->activo) {
            return response()->json(['error' => 'Recurso no encontrado'], 404);
        }
        
        return new ResourceResource($resource);
    }

    /**
     * Update the specified resource in storage.
     */
 

public function update(Request $request, ObraRecursoMaestro $recurso)
{
    // (opcional) logging limpio
 

    $validated = $request->validate([
        'codigo' => [
            'required',
            Rule::unique('obra_recursos_maestros', 'codigo')
                ->ignore($recurso->id),
        ],
        'nombre' => 'required|string|max:255',
        'tipo' => 'required|string|max:50',
        'unidad' => 'required|string|max:20',
        'precio_referencia' => 'required|numeric|min:0.01',
        'descripcion' => 'nullable|string',
        'activo' => 'boolean',
    ]);

    $recurso->update($validated);

    return response()->json([
        'message' => 'Recurso maestro actualizado exitosamente',
        'data' => new ResourceResource($recurso->fresh())
    ]);
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ObraRecursoMaestro $resource)
    {
        // Verificar si está en uso en algún item
        if ($resource->items()->exists()) {
            throw ValidationException::withMessages([
                'error' => 'No se puede eliminar el recurso porque está en uso en items.'
            ]);
        }

        $resource->delete();

        return response()->json(['message' => 'Recurso maestro eliminado exitosamente']);
    }

    /**
     * Bulk update prices
     */
    public function updatePrices(Request $request)
    {
        $request->validate([
            'precios' => 'required|array',
            'precios.*.id' => 'required|exists:obra_recursos_maestros,id',
            'precios.*.precio_referencia' => 'required|numeric|min:0'
        ]);

        foreach ($request->precios as $precioData) {
            ObraRecursoMaestro::where('id', $precioData['id'])
                ->update(['precio_referencia' => $precioData['precio_referencia']]);
        }

        return response()->json(['message' => 'Precios actualizados exitosamente']);
    }
    /**
 * Get regional prices for a resource
 */
    public function getRegionalPrices(ObraRecursoMaestro $resource)
    {
        $precios = DB::table('recurso_maestro_region')
            ->join('regiones', 'recurso_maestro_region.region_id', '=', 'regiones.id')
            ->select('recurso_maestro_region.*', 'regiones.nombre as region_nombre', 'regiones.codigo as region_codigo')
            ->where('obra_recurso_maestro_id', $resource->id)
            ->get();

        return response()->json(['data' => $precios]);
    }

    /**
     * Update regional price for a resource
     */
    public function updateRegionalPrice(ObraRecursoMaestro $resource, Region $region, Request $request)
    {
        $request->validate([
            'precio_regional' => 'required|numeric|min:0'
        ]);

        $precioExistente = DB::table('recurso_maestro_region')
            ->where('obra_recurso_maestro_id', $resource->id)
            ->where('region_id', $region->id)
            ->first();

        if ($precioExistente) {
            DB::table('recurso_maestro_region')
                ->where('obra_recurso_maestro_id', $resource->id)
                ->where('region_id', $region->id)
                ->update(['precio_regional' => $request->precio_regional]);
        } else {
            DB::table('recurso_maestro_region')->insert([
                'obra_recurso_maestro_id' => $resource->id,
                'region_id' => $region->id,
                'precio_regional' => $request->precio_regional,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        $precioActualizado = DB::table('recurso_maestro_region')
            ->join('regiones', 'recurso_maestro_region.region_id', '=', 'regiones.id')
            ->select('recurso_maestro_region.*', 'regiones.nombre as region_nombre', 'regiones.codigo as region_codigo')
            ->where('obra_recurso_maestro_id', $resource->id)
            ->where('region_id', $region->id)
            ->first();

        return response()->json([
            'message' => 'Precio regional actualizado exitosamente',
            'data' => $precioActualizado
        ]);
    }


    /**
     * Delete regional price for a resource
     */
    public function deleteRegionalPrice(ObraRecursoMaestro $resource, Region $region)
    {
        DB::table('recurso_maestro_region')
            ->where('obra_recurso_maestro_id', $resource->id)
            ->where('region_id', $region->id)
            ->delete();

        return response()->json(['message' => 'Precio regional eliminado exitosamente']);
    }
    // En app/Http/Controllers/API/ResourceController.php

    public function getBulkPrices()
    {
        try {
            // Obtener versiones
            $versionActiva = Version::where('activo', true)->first();
            $versionPublicada = Version::where('publicada', true)->first();
            
            // Obtener todos los recursos activos
            $recursos = ObraRecursoMaestro::activos()->get();
            $regiones = Region::activas()->get();
            
            $datosRecursos = [];
            
            foreach ($recursos as $recurso) {
                $recursoData = [
                    'id' => $recurso->id,
                    'codigo' => $recurso->codigo,
                    'nombre' => $recurso->nombre,
                    'tipo' => $recurso->tipo,
                    'unidad' => $recurso->unidad,
                    
                    // 👇 PRECIOS DE LA VERSIÓN PUBLICADA
                    'precios_publicados' => [
                        'global' => null,
                        'regionales' => []
                    ],
                    
                    // 👇 PRECIOS DE LA VERSIÓN ACTIVA (editables)
                    'precios_activos' => [
                        'global' => null,
                        'regionales' => []
                    ]
                ];
                
                // Precios de la versión publicada
                if ($versionPublicada) {
                    $precioGlobalPub = DB::table('version_recurso_maestro')
                        ->where('version_id', $versionPublicada->id)
                        ->where('obra_recurso_maestro_id', $recurso->id)
                        ->first();
                        
                    $recursoData['precios_publicados']['global'] = 
                        $precioGlobalPub?->precio_version ?? $recurso->precio_referencia;
                    
                    // Precios regionales publicados
                    foreach ($regiones as $region) {
                        $precioRegionalPub = DB::table('version_recurso_region')
                            ->where('version_id', $versionPublicada->id)
                            ->where('obra_recurso_maestro_id', $recurso->id)
                            ->where('region_id', $region->id)
                            ->first();
                            
                        $recursoData['precios_publicados']['regionales'][$region->id] = 
                            $precioRegionalPub?->precio_version_regional ?? $recurso->precio_referencia;
                    }
                } else {
                    // Si no hay versión publicada, usar precios base
                    $recursoData['precios_publicados']['global'] = $recurso->precio_referencia;
                    foreach ($regiones as $region) {
                        $recursoData['precios_publicados']['regionales'][$region->id] = $recurso->precio_referencia;
                    }
                }
                
                // Precios de la versión activa
                if ($versionActiva) {
                    $precioGlobalAct = DB::table('version_recurso_maestro')
                        ->where('version_id', $versionActiva->id)
                        ->where('obra_recurso_maestro_id', $recurso->id)
                        ->first();
                        
                    $recursoData['precios_activos']['global'] = 
                        $precioGlobalAct?->precio_version ?? $recursoData['precios_publicados']['global'];
                    
                    // Precios regionales activos
                    foreach ($regiones as $region) {
                        $precioRegionalAct = DB::table('version_recurso_region')
                            ->where('version_id', $versionActiva->id)
                            ->where('obra_recurso_maestro_id', $recurso->id)
                            ->where('region_id', $region->id)
                            ->first();
                            
                        $recursoData['precios_activos']['regionales'][$region->id] = 
                            $precioRegionalAct?->precio_version_regional ?? 
                            $recursoData['precios_publicados']['regionales'][$region->id];
                    }
                } else {
                    // Si no hay versión activa, usar precios publicados como base editable
                    $recursoData['precios_activos']['global'] = $recursoData['precios_publicados']['global'];
                    foreach ($regiones as $region) {
                        $recursoData['precios_activos']['regionales'][$region->id] = 
                            $recursoData['precios_publicados']['regionales'][$region->id];
                    }
                }
                
                $datosRecursos[] = $recursoData;
            }
            
            return response()->json([
                'data' => $datosRecursos,
                'version_activa_id' => $versionActiva?->id,
                'version_publicada_id' => $versionPublicada?->id,
                'regions' => $regiones
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en getBulkPrices: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar los precios'], 500);
        }
    }

    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'version_id' => 'required|exists:versions,id',
            'updates' => 'required|array'
        ]);
        
        try {
            DB::beginTransaction();
            
            foreach ($validated['updates'] as $update) {
                // Validar estructura del update
                if (!isset($update['recurso_id']) || !isset($update['precios'])) {
                    continue;
                }
                
                $recursoId = $update['recurso_id'];
                $precios = $update['precios'];
                
                // Actualizar precio global
                if (isset($precios['global'])) {
                    DB::table('version_recurso_maestro')
                        ->updateOrInsert(
                            ['version_id' => $validated['version_id'], 'obra_recurso_maestro_id' => $recursoId],
                            ['precio_version' => $precios['global'], 'updated_at' => now()]
                        );
                }
                
                // Actualizar precios regionales
                if (isset($precios['regionales']) && is_array($precios['regionales'])) {
                    foreach ($precios['regionales'] as $regionId => $precioRegional) {
                        DB::table('version_recurso_region')
                            ->updateOrInsert(
                                [
                                    'version_id' => $validated['version_id'],
                                    'obra_recurso_maestro_id' => $recursoId,
                                    'region_id' => $regionId
                                ],
                                ['precio_version_regional' => $precioRegional, 'updated_at' => now()]
                            );
                    }
                }
            }
            
            DB::commit();
            
            return response()->json(['message' => 'Precios actualizados exitosamente']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en bulkUpdate: ' . $e->getMessage());
            return response()->json(['error' => 'Error al actualizar los precios'], 500);
        }
    }
}
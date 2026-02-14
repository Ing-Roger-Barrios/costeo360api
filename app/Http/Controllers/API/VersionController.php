<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Version;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class VersionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Obtener la versión activa (si existe)
        $versionActiva = Version::where('activo', true)->first();
        
        // Obtener versiones inactivas con búsqueda y paginación
        $query = Version::where('activo', false);
        
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('version', 'like', "%{$request->search}%")
                ->orWhere('nombre', 'like', "%{$request->search}%");
            });
        }
        
        $versionesInactivas = $query->orderBy('updated_at', 'desc')->paginate($request->get('per_page', 25));
        
        // Combinar resultados
        $data = [];
        if ($versionActiva) {
            $data[] = $versionActiva;
        }
        
        $data = array_merge($data, $versionesInactivas->items());
        
        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $versionesInactivas->currentPage(),
                'last_page' => $versionesInactivas->lastPage() + ($versionActiva ? 0 : 0), // Ajustar si es necesario
                'per_page' => $versionesInactivas->perPage(),
                'total' => $versionesInactivas->total() + ($versionActiva ? 1 : 0)
            ]
        ]);
    }
    public function show(Version $version)
    {
        return response()->json([
            'data' => $version
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'version' => 'required|string|max:50|unique:versions,version',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean'
        ]);

        // 👇 USAR EL NUEVO MÉTODO
        $version = Version::crearVersionBasadaEnUltimaPublicada(
            $validated['version'],
            $validated['nombre'],
            $validated['descripcion']
        );

        // Si se quiere activar inmediatamente
        if (!empty($validated['activo'])) {
            $version->activar();
        }

        return response()->json([
            'message' => 'Versión creada exitosamente',
            'data' => $version
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $version = Version::findOrFail($id);
        
        $validated = $request->validate([
            'version' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('versions', 'version')->ignore($version->id)],
            'nombre' => 'sometimes|required|string|max:255',
            'fecha_publicacion' => 'sometimes|required|date',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean'
        ]);

        // Si se está activando esta versión
        if (!empty($validated['activo']) && $validated['activo'] !== $version->activo) {
            $version->activar();
        } else {
            // Actualizar otros campos normalmente
            $updateData = [];
            if (isset($validated['version'])) $updateData['version'] = $validated['version'];
            if (isset($validated['nombre'])) $updateData['nombre'] = $validated['nombre'];
            if (isset($validated['fecha_publicacion'])) $updateData['fecha_publicacion'] = $validated['fecha_publicacion'];
            if (isset($validated['descripcion'])) $updateData['descripcion'] = $validated['descripcion'];
            
            if (!empty($updateData)) {
                $version->update($updateData);
            }
        }

        return response()->json([
            'message' => 'Versión actualizada exitosamente',
            'data' => $version->fresh()
        ]);
    }

    public function destroy(Version $version)
    {
        if ($version->recursos()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar la versión porque tiene recursos asociados.'
            ], 422);
        }

        $version->delete();
        return response()->json(['message' => 'Versión eliminada exitosamente']);
    }

    // 👇 NUEVO MÉTODO: Activar versión
    public function activate(Version $version)
    {
        $version->activar();
        return response()->json([
            'message' => 'Versión activada exitosamente',
            'data' => $version->fresh()
        ]);
    }
    public function publish(Request $request)
{
    $validated = $request->validate([
        'version' => 'required|string|max:50|unique:versions,version',
        'nombre' => 'required|string|max:255',
        'descripcion' => 'nullable|string'
    ]);

    // Usar tu método existente
    $version = Version::publicarNuevaVersionConRegiones(
        $validated['version'],
        $validated['nombre'],
        $validated['descripcion']
    );

    return response()->json([
        'message' => 'Versión publicada exitosamente',
        'data' => $version
    ], 201);
}

    /**
     * Get current active version info.
     */
    // Obtener versión activa (para trabajo)
    public function getActiveVersion()
    {
        $version = Version::activa()->first();
        return response()->json(['data' => $version]);
    }
    // Obtener versión publicada (para escritorio)
    public function getPublishedVersion()
    {
        $version = Version::where('publicada', true)->first();
        
        return response()->json([
            'data' => $version
        ]);
    }

    /*public function publishExistingVersion(Request $request)
    {
        $validated = $request->validate([
            'version_id' => 'required|exists:versions,id',
            'nombre' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string'
        ]);

        try {
            $version = Version::findOrFail($validated['version_id']);
            
            // Solo actualizar campos específicos de ESTA versión
            if (!empty($validated['nombre'])) {
                $version->nombre = $validated['nombre'];
            }
            if (!empty($validated['descripcion'])) {
                $version->descripcion = $validated['descripcion'];
            }
            
            // Establecer fecha de publicación SOLO para esta versión
            $version->fecha_publicacion = now();
            
            // Guardar cambios
            $version->save();
            
            // Activar si es necesario
            if ($request->boolean('make_active')) {
                $version->activar(); // Este método ya está corregido
            }
            
            return response()->json([
                'message' => 'Versión publicada exitosamente',
                'data' => $version
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al publicar versión: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al publicar la versión'
            ], 500);
        }
    }*/
    // Publicar la versión activa actual
    // En app/Http/Controllers/API/VersionController.php

    public function publishActiveVersion(Request $request)
    {
        try {
            // Obtener la versión activa
            $versionActiva = Version::where('activo', true)->first();
            
            if (!$versionActiva) {
                return response()->json([
                    'message' => 'No hay una versión activa para publicar'
                ], 400);
            }

            // Actualizar metadatos si se proporcionan
            if ($request->has('nombre')) {
                $versionActiva->nombre = $request->nombre;
            }
            if ($request->has('descripcion')) {
                $versionActiva->descripcion = $request->descripcion;
            }

            // Publicar la versión (usando el método del modelo)
            $versionActiva->publicar();

            return response()->json([
                'message' => 'Versión publicada exitosamente',
                'data' => $versionActiva
            ]);

        } catch (\Exception $e) {
            Log::error('Error al publicar versión activa: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al publicar la versión'
            ], 500);
        }
    }
}
<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RegionController extends Controller
{
    public function index()
    {
        $regions = Region::all();
        return response()->json(['data' => $regions]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|unique:regiones,codigo',
            'nombre' => 'required|string|max:255',
            'pais' => 'nullable|string|max:255',
            'activo' => 'boolean'
        ]);

        $region = Region::create($validated);

        return response()->json([
            'message' => 'Región creada exitosamente',
            'data' => $region
        ], 201);
    }

    public function show(Region $region)
    {
        return response()->json(['data' => $region]);
    }

    public function update(Request $request, Region $region)
    {
        $validated = $request->validate([
            'codigo' => 'sometimes|required|string|unique:regiones,codigo,' . $region->id,
            'nombre' => 'sometimes|required|string|max:255',
            'pais' => 'nullable|string|max:255',
            'activo' => 'boolean'
        ]);

        $region->update($validated);

        return response()->json([
            'message' => 'Región actualizada exitosamente',
            'data' => $region
        ]);
    }

    public function destroy(Region $region)
    {
        // Verificar si está en uso
        if ($region->recursos()->exists() || $region->versionesRecursos()->exists()) {
            throw ValidationException::withMessages([
                'error' => 'No se puede eliminar la región porque está en uso.'
            ]);
        }

        $region->delete();

        return response()->json(['message' => 'Región eliminada exitosamente']);
    }
}
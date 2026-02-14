<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'descripcion' => $this->descripcion,
            'unidad' => $this->unidad,
            'notas' => $this->notas,
            'activo' => (bool) $this->activo,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Incluir recursos si están cargados
            'recursos' => ResourceResource::collection($this->whenLoaded('recursos')),
            
            // Calcular precio base si se solicita
            'precio_base' => $this->when($request->has('include_precio_base'), function () {
                return $this->recursos->sum(function ($recurso) {
                    $precio = $recurso->pivot->precio_version ?? $recurso->precio_referencia;
                    return $recurso->pivot->rendimiento * $precio;
                });
            }),
            
            // Rendimientos en el pivot
            'rendimiento' => $this->whenPivotLoaded('obra_item_recursos', function () {
                return (float) $this->pivot->rendimiento;
            }),
        ];
    }
}
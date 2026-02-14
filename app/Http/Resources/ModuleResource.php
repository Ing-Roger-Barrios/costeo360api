<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ModuleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'activo' => (bool) $this->activo,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Incluir items si están cargados
            'items' => ItemResource::collection($this->whenLoaded('items')),
            
            // Orden en el pivot
            'orden' => $this->whenPivotLoaded('obra_modulo_items', function () {
                return (int) $this->pivot->orden;
            }),
        ];
    }
}
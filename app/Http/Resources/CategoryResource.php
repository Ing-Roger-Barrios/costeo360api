<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            
            // Incluir módulos si están cargados
            'modulos' => ModuleResource::collection($this->whenLoaded('modulos')),
            
            // Orden en el pivot
            'orden' => $this->whenPivotLoaded('obra_categoria_modulos', function () {
                return (int) $this->pivot->orden;
            }),
        ];
    }
}
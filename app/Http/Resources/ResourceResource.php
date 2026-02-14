<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ResourceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'tipo' => $this->tipo,
            'unidad' => $this->unidad,
            'precio_referencia' => (float) $this->precio_referencia,
            'descripcion' => $this->descripcion,
            'activo' => (bool) $this->activo,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Si se solicita el precio de una versión específica
            'precio_version' => $this->whenPivotLoaded('version_recurso_maestro', function () {
                return (float) $this->pivot->precio_version;
            }),
        ];
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Region extends Model
{
       // 👇 ESPECIFICAR EL NOMBRE CORRECTO DE LA TABLA
    protected $table = 'regiones';
    protected $fillable = ['codigo', 'nombre', 'pais', 'activo'];
    
    protected $casts = [
        'activo' => 'boolean'
    ];

    public function recursos()
    {
        return $this->belongsToMany(ObraRecursoMaestro::class, 'recurso_maestro_region')
                    ->withPivot('precio_regional')
                    ->withTimestamps();
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }
}
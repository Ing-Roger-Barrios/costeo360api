<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObraItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'descripcion',
        'unidad', 
        'notas',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    // Relación: Un item pertenece a muchos recursos maestros
    public function recursos()
    {
        return $this->belongsToMany(ObraRecursoMaestro::class, 'obra_item_recursos')
                    ->withPivot('rendimiento')
                    ->withTimestamps();
    }

    // Relación: Un item puede estar en muchos módulos
    public function modulos()
    {
        return $this->belongsToMany(ObraModulo::class, 'obra_modulo_items')
                    ->withPivot('orden')
                    ->orderBy('orden')
                    ->withTimestamps();
    }

    // Scope para items activos
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    // Calcular precio unitario base (sin norma)
    public function calcularPrecioBase()
    {
        return $this->recursos->sum(function ($recurso) {
            return $recurso->pivot->rendimiento * $recurso->precio_referencia;
        });
    }
}
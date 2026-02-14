<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObraCategoria extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion', 
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    // Relación: Una categoría tiene muchos módulos
    public function modulos()
    {
        return $this->belongsToMany(ObraModulo::class, 'obra_categoria_modulos')
                    ->withPivot('orden')
                    ->orderBy('pivot_orden') // Ordenar por campo pivot
                    ->withTimestamps();
                  
    }

    // Scope para categorías activas
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
    public function getPrecioTotalAttribute()
    {
        return $this->modulos->sum('precio_total');
    }

}
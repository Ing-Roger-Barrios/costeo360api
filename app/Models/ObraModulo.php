<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObraModulo extends Model
{
    use HasFactory;

    protected $table = 'obra_modulos';
    
    // 👇 DEFINIR EXPLÍCITAMENTE LA CLAVE PRIMARIA
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    public function items()
    {
        return $this->belongsToMany(
            ObraItem::class,           // Modelo relacionado
            'obra_modulo_items',       // Tabla pivot
            'obra_modulo_id',          // Columna foránea para este modelo
            'obra_item_id'             // Columna foránea para el modelo relacionado
        )
        ->withPivot('orden', 'rendimiento')
        ->orderBy('orden')
        ->withTimestamps();
    }

    public function categorias()
    {
        return $this->belongsToMany(ObraCategoria::class, 'obra_categoria_modulos')
                    ->withPivot('orden')
                    ->withTimestamps();
    }

    /**
     * Calcular precio total del módulo
     */
    public function calcularPrecioTotal(): array
    {
        $itemsConPrecio = [];
        $precioTotalModulo = 0;

        foreach ($this->items as $item) {
            $rendimientoEnModulo = $item->pivot->rendimiento ?? 1;
            $precioItem = $item->calcularPrecioEnModulo($rendimientoEnModulo);
            
            $itemsConPrecio[] = [
                'item' => $item,
                'rendimiento_en_modulo' => $rendimientoEnModulo,
                'precio_unitario' => $precioItem['precio_unitario'],
                'precio_total' => $precioItem['precio_total'],
                'sabs_detalle' => $precioItem['sabs_detalle']
            ];
            
            $precioTotalModulo += $precioItem['precio_total'];
        }

        return [
            'items' => $itemsConPrecio,
            'precio_total_modulo' => round($precioTotalModulo, 2)
        ];
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
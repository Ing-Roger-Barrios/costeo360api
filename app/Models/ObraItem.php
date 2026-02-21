<?php

namespace App\Models;

use App\Services\PriceCalculator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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
        'activo' => 'boolean',
    ];

    // Relación: Un item pertenece a muchos recursos maestros
   /* public function recursos()
    {
        return $this->belongsToMany(ObraRecursoMaestro::class, 'obra_item_recursos')
                    ->withPivot('rendimiento')
                    ->withTimestamps();
    }

    // Relación: Un item puede estar en muchos módulos
    public function modulos()
    {
        return $this->belongsToMany(ObraModulo::class, 'obra_modulo_items')
                    ->withPivot(['orden', 'rendimiento'])
                    ->orderBy('orden')
                    ->withTimestamps();
    }*/

    
    public function recursos()
    {
        return $this->belongsToMany(
            ObraRecursoMaestro::class,
            'obra_item_recursos',
            'obra_item_id',
            'obra_recurso_maestro_id'
        )->withPivot(['rendimiento'])->withTimestamps();
    }
    public function modulos()
    {
        return $this->belongsToMany(
            ObraModulo::class,
            'obra_modulo_items',
            'obra_item_id',
            'obra_modulo_id'
        )->withPivot(['orden', 'rendimiento'])->orderBy('pivot_orden')->withTimestamps();
    }

    /**
     * Calcular precio UNITARIO del item (por 1 unidad)
     * Este precio es independiente del módulo
     */
    public function calcularPrecioUnitario(): array
    {
        try {
            // 👉 Verificar si tiene recursos
        if ($this->recursos->count() === 0) {
            return [
                'recursos' => collect(),
                'totales' => ['materiales' => 0, 'mano_obra' => 0, 'equipo' => 0],
                'sabs_detalle' => PriceCalculator::calculateSabsUnitPrice(0, 0, 0),
                'precio_unitario' => 0
            ];
        }
            // Obtener recursos con precios
            $recursos = $this->recursos->map(function ($recurso) {
                return [
                    'id' => $recurso->id,
                    'codigo' => $recurso->codigo,
                    'nombre' => $recurso->nombre,
                    'tipo' => $recurso->tipo,
                    'unidad' => $recurso->unidad,
                    'precio_unitario' => $recurso->precio_unitario ?? 0,
                    'pivot_rendimiento' => $recurso->pivot->rendimiento ?? 0,
                    'parcial' => ($recurso->pivot->rendimiento ?? 0) * ($recurso->precio_unitario ?? 0)
                ];
            });

            // Calcular totales por tipo
            $totales = PriceCalculator::calculateResourceTotals($recursos->toArray());
            
            // Calcular precio SABS
            $detalleSabs = PriceCalculator::calculateSabsUnitPrice(
                $totales['materiales'],
                $totales['mano_obra'],
                $totales['equipo']
            );

            return [
                'recursos' => $recursos,
                'totales' => $totales,
                'sabs_detalle' => $detalleSabs,
                'precio_unitario' => $detalleSabs['Q_precio_unitario']
            ];
        } catch (\Throwable $e) {
        Log::error("Error calculando precio item {$this->id}: " . $e->getMessage());
        return [
            'recursos' => collect(),
            'totales' => ['materiales' => 0, 'mano_obra' => 0, 'equipo' => 0],
            'sabs_detalle' => PriceCalculator::calculateSabsUnitPrice(0, 0, 0),
            'precio_unitario' => 0
        ];
    }
    }

    /**
     * Calcular precio TOTAL del item EN UN MÓDULO ESPECÍFICO
     */
    public function calcularPrecioEnModulo($rendimientoEnModulo): array
    {
        $precioData = $this->calcularPrecioUnitario();
        
        $precioTotal = 0;
        if ($precioData['precio_unitario'] > 0 && $rendimientoEnModulo > 0) {
            $precioTotal = PriceCalculator::calculateItemTotalInModule(
                $precioData['precio_unitario'],
                $rendimientoEnModulo
            );
        }

        return [
            ...$precioData,
            'rendimiento_en_modulo' => $rendimientoEnModulo,
            'precio_total' => $precioTotal
        ];
    }


    // Scope para items activos
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
    public function getPrecioBaseAttribute()
{
    return Cache::remember("item_precio_{$this->id}", 3600, function() {
        return $this->calcularPrecioUnitario()['precio_unitario'];
    });
}

    // Calcular precio unitario base (sin norma)
    public function calcularPrecioBase()
    {
        return $this->recursos->sum(function ($recurso) {
            return $recurso->pivot->rendimiento * $recurso->precio_referencia;
        });
    }
}
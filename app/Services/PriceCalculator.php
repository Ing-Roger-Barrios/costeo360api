<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class PriceCalculator
{
    /**
     * Calcular precio unitario según norma SABS
     * 
     * @param float $materialesTotal Total de materiales
     * @param float $manoObraTotal Total de mano de obra
     * @param float $equipoTotal Total de equipo
     * @return array Detalle del cálculo y precio final
     */
    public static function calculateSabsUnitPrice($materialesTotal, $manoObraTotal, $equipoTotal): array
    {
        // Validación de inputs
        $materialesTotal = max(0, (float) $materialesTotal);
        $manoObraTotal = max(0, (float) $manoObraTotal);
        $equipoTotal = max(0, (float) $equipoTotal);

        // A = Materiales
        $A = $materialesTotal;

        // B = Mano de Obra
        $B = $manoObraTotal;

        // C = Equipo
        $C = $equipoTotal;

        // D = Total Materiales
        $D = $A;

        // E = Subtotal Mano de Obra
        $E = $B;

        // F = Cargas Sociales (55%)
        $F = $E * (55 / 100);

        // O = IVA (14.94% sobre mano de obra con cargas sociales) - NO se suma al precio final
        $O = ($E + $F) * (14.94 / 100);

        // G = Total Mano de Obra
        $G = $E + $F + $O;

        // H = Herramientas menores (5%)
        $H = $G * (5 / 100);

        // I = Total Herramientas y Equipo
        $I = $C + $H;

        // J = Subtotal
        $J = $D + $G + $I;

        // K = Imprevistos (0% según norma SABS)
        $K = $J * (0 / 100);

        // L = Gastos generales (10%)
        $L = $J * (10 / 100);

        // M = Utilidad (10% sobre J + L)
        $M = ($J + $L) * (10 / 100);

        // N = Parcial
        $N = $J + $L + $M;

        // P = IT (3.06%)
        $P = $N * (3.06 / 100);

        // Q = Total Precio Unitario
        $Q = $N + $P;

        // Redondear a 2 decimales
        return  [
            'A_materiales' => round($A, 4),
            'B_mano_obra' => round($B, 4),
            'C_equipo' => round($C, 4),
            'D_total_materiales' => round($D, 4),
            'E_subtotal_mano_obra' => round($E, 4),
            'F_cargas_sociales' => round($F, 4),
            'G_total_mano_obra' => round($G, 4),
            'H_herramientas_menores' => round($H, 4),
            'I_total_herramientas_equipo' => round($I, 4),
            'J_subtotal' => round($J, 4),
            'K_imprevistos' => round($K, 4),
            'L_gastos_generales' => round($L, 4),
            'M_utilidad' => round($M, 4),
            'N_parcial' => round($N, 4),
            'O_iva' => round($O, 4), // Solo informativo, no se suma
            'P_it' => round($P, 4),
            'Q_precio_unitario' => round($Q, 4),
        ];

         
    }

    /**
     * Calcular precio TOTAL del item en un módulo específico
     * = Precio unitario × Rendimiento en el módulo
     */
    public static function calculateItemTotalInModule($precioUnitario, $rendimientoEnModulo): float
    {
        $rendimientoEnModulo = max(0.000001, (float) $rendimientoEnModulo);
        return round($precioUnitario * $rendimientoEnModulo, 2);
    }

    /**
     * Calcular totales por tipo de recurso para un item
     */
    public static function calculateResourceTotals($recursos): array
    {
        $totales = [
            'materiales' => 0,
            'mano_obra' => 0,
            'equipo' => 0
        ];

        foreach ($recursos as $recurso) {
            // Parcial = rendimiento_recurso × precio_unitario
            $rendimientoRecurso = $recurso['pivot_rendimiento'] ?? $recurso['rendimiento'] ?? 0;
            $precioUnitario = $recurso['precio_unitario'] ?? $recurso['precio'] ?? 0;
            $parcial = $rendimientoRecurso * $precioUnitario;
            
            // Clasificar por tipo
            $tipo = strtolower($recurso['tipo'] ?? '');
            
            if (str_contains($tipo, 'material')) {
                $totales['materiales'] += $parcial;
            } elseif (str_contains($tipo, 'mano') || str_contains($tipo, 'obra')) {
                $totales['mano_obra'] += $parcial;
            } elseif (str_contains($tipo, 'equipo')) {
                $totales['equipo'] += $parcial;
            }
        }

        return [
            'materiales' => round($totales['materiales'], 2),
            'mano_obra' => round($totales['mano_obra'], 2),
            'equipo' => round($totales['equipo'], 2),
        ];
    }
}
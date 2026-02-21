<!-- components/SabsPriceCalculator.vue -->
<template>
  <div class="bg-gray-50 rounded-lg p-4 mt-4">
    <h3 class="text-lg font-semibold text-gray-800 mb-3">Cálculo de Precio SABS</h3>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
      <div class="bg-white p-3 rounded-lg border border-gray-200">
        <p class="text-sm text-gray-500">Materiales</p>
        <p class="text-lg font-bold text-green-600">
          {{ formatCurrency(totales.materiales) }}
        </p>
      </div>
      <div class="bg-white p-3 rounded-lg border border-gray-200">
        <p class="text-sm text-gray-500">Mano de Obra</p>
        <p class="text-lg font-bold text-blue-600">
          {{ formatCurrency(totales.mano_obra) }}
        </p>
      </div>
      <div class="bg-white p-3 rounded-lg border border-gray-200">
        <p class="text-sm text-gray-500">Equipo</p>
        <p class="text-lg font-bold text-purple-600">
          {{ formatCurrency(totales.equipo) }}
        </p>
      </div>
    </div>

    <div class="border-t pt-4">
      <div class="flex justify-between items-center mb-2">
        <span class="font-medium">Precio Unitario (SABS)</span>
        <span class="text-xl font-bold text-primary">
          {{ formatCurrency(precioUnitario) }}
        </span>
      </div>
      
      <div class="flex justify-between items-center mb-2">
        <span class="font-medium">Rendimiento del Item</span>
        <span class="font-medium">{{ rendimientoItem }} {{ unidad }}</span>
      </div>
      
      <div class="flex justify-between items-center pt-2 border-t-2 border-gray-200">
        <span class="text-lg font-bold">Precio Total</span>
        <span class="text-2xl font-bold text-green-600">
          {{ formatCurrency(precioTotal) }}
        </span>
      </div>
    </div>

    <button 
      @click="showDetails = !showDetails"
      class="mt-3 text-sm text-primary hover:underline flex items-center"
    >
      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
      </svg>
      {{ showDetails ? 'Ocultar detalles' : 'Ver detalles del cálculo' }}
    </button>

    <div v-if="showDetails" class="mt-4 text-sm space-y-2">
      <div class="grid grid-cols-2 gap-2">
        <div>D = Materiales:</div>
        <div class="text-right font-medium">{{ formatCurrency(sabsDetalle.D_total_materiales) }}</div>
        
        <div>G = Mano de Obra + Cargas Sociales (55%):</div>
        <div class="text-right font-medium">{{ formatCurrency(sabsDetalle.G_total_mano_obra) }}</div>
        
        <div>I = Equipo + Herramientas (5%):</div>
        <div class="text-right font-medium">{{ formatCurrency(sabsDetalle.I_total_herramientas_equipo) }}</div>
        
        <div>J = Subtotal (D + G + I):</div>
        <div class="text-right font-medium">{{ formatCurrency(sabsDetalle.J_subtotal) }}</div>
        
        <div>L = Gastos Generales (10%):</div>
        <div class="text-right font-medium">{{ formatCurrency(sabsDetalle.L_gastos_generales) }}</div>
        
        <div>M = Utilidad (10%):</div>
        <div class="text-right font-medium">{{ formatCurrency(sabsDetalle.M_utilidad) }}</div>
        
        <div>N = Parcial (J + L + M):</div>
        <div class="text-right font-medium">{{ formatCurrency(sabsDetalle.N_parcial) }}</div>
        
        <div>P = IT (3.06%):</div>
        <div class="text-right font-medium">{{ formatCurrency(sabsDetalle.P_it) }}</div>
        
        <div class="font-bold pt-2 border-t">Q = Precio Unitario:</div>
        <div class="text-right font-bold text-primary pt-2 border-t">{{ formatCurrency(sabsDetalle.Q_precio_unitario) }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  recursos: {
    type: Array,
    default: () => []
  },
  rendimientoItem: {
    type: Number,
    default: 1
  },
  unidad: {
    type: String,
    default: 'unidad'
  }
});

const showDetails = ref(false);

// Calcular totales por tipo
const totales = computed(() => {
  const totals = { materiales: 0, mano_obra: 0, equipo: 0 };
  
  props.recursos.forEach(recurso => {
    const parcial = (recurso.pivot_rendimiento || 0) * (recurso.precio_unitario || 0);
    const tipo = recurso.tipo?.toLowerCase() || '';
    
    if (tipo.includes('material')) {
      totals.materiales += parcial;
    } else if (tipo.includes('mano') || tipo.includes('obra')) {
      totals.mano_obra += parcial;
    } else if (tipo.includes('equipo')) {
      totals.equipo += parcial;
    }
  });
  
  return {
    materiales: parseFloat(totals.materiales.toFixed(4)),
    mano_obra: parseFloat(totals.mano_obra.toFixed(4)),
    equipo: parseFloat(totals.equipo.toFixed(4))
  };
});

// Calcular precio SABS (simulación - en producción se haría en backend)
const sabsDetalle = computed(() => {
  const A = totales.value.materiales;
  const B = totales.value.mano_obra;
  const C = totales.value.equipo;
  
  const D = A;
  const E = B;
  const F = E * 0.55;
  const G = E + F;
  const H = G * 0.05;
  const I = C + H;
  const J = D + G + I;
  const L = J * 0.10;
  const M = (J + L) * 0.10;
  const N = J + L + M;
  const P = N * 0.0306;
  const Q = N + P;
  
  return {
    D_total_materiales: parseFloat(D.toFixed(4)),
    G_total_mano_obra: parseFloat(G.toFixed(4)),
    I_total_herramientas_equipo: parseFloat(I.toFixed(4)),
    J_subtotal: parseFloat(J.toFixed(4)),
    L_gastos_generales: parseFloat(L.toFixed(4)),
    M_utilidad: parseFloat(M.toFixed(4)),
    N_parcial: parseFloat(N.toFixed(4)),
    P_it: parseFloat(P.toFixed(4)),
    Q_precio_unitario: parseFloat(Q.toFixed(4))
  };
});

const precioUnitario = computed(() => sabsDetalle.value.Q_precio_unitario);
const precioTotal = computed(() => parseFloat((precioUnitario.value * props.rendimientoItem).toFixed(2)));

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB',
    minimumFractionDigits: 2
  }).format(amount);
};
</script>
<template>
  <div 
    class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 cursor-move transition-colors"
    draggable="true"
    @dragstart="onDragStart"
    @dragend="onDragEnd"
  >
    <div class="flex items-center space-x-3">
      <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
        <span class="text-xs font-semibold text-blue-800">{{ resource.tipo.charAt(0) }}</span>
      </div>
      <div class="flex-1 min-w-0">
        <p class="text-sm font-medium text-gray-900 truncate">{{ resource.codigo }}</p>
        <p class="text-xs text-gray-500 truncate">{{ resource.nombre }}</p>
        <p class="text-xs text-green-600 font-medium mt-1">
          {{ formatCurrency(resource.precio_referencia) }} / {{ resource.unidad }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue';

const props = defineProps({
  resource: Object
});

const onDragStart = (event) => {
    // 👇 Almacenar el recurso en el dataTransfer
  event.dataTransfer.setData('application/json', JSON.stringify(props.resource));
  event.dataTransfer.effectAllowed = 'copy';
  // 👇 Añadir feedback visual
  event.target.classList.add('opacity-50');
};

const onDragEnd = (event) => {
  // 👇 Restaurar estilo
  event.target.classList.remove('opacity-50');
};

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB',
    minimumFractionDigits: 2
  }).format(amount);
};
</script>
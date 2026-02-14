<template>
  <div 
    class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 cursor-move transition-colors"
    draggable="true"
    @dragstart="onDragStart"
    @dragend="onDragEnd"
  >
    <div class="flex items-center space-x-3">
      <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
        <span class="text-xs font-semibold text-green-800">I</span>
      </div>
      <div class="flex-1 min-w-0">
        <p class="text-sm font-medium text-gray-900 truncate">{{ item.codigo }}</p>
        <p class="text-xs text-gray-500 truncate">{{ item.descripcion }}</p>
        <p class="text-xs text-green-600 font-medium mt-1">
          {{ formatCurrency(item.precio_base) }} / {{ item.unidad }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue';

const props = defineProps({
  item: Object
});

const onDragStart = (event) => {
  event.dataTransfer.setData('application/json', JSON.stringify(props.item));
  event.dataTransfer.effectAllowed = 'copy';
};

const onDragEnd = (event) => {
  // Opcional: limpiar estado visual
};

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB',
    minimumFractionDigits: 2
  }).format(amount);
};
</script>
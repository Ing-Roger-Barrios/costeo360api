<template>
  <div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
      <h3 class="text-lg font-semibold">Lista de Regiones</h3>
      <p class="text-sm text-gray-500">Gestiona las regiones disponibles en el sistema</p>
    </div>

    <div v-if="loading" class="p-6 text-center">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
      <p class="mt-2 text-gray-500">Cargando regiones...</p>
    </div>

    <div v-else-if="regions.length === 0" class="p-6 text-center">
      <p class="text-gray-500">No hay regiones registradas</p>
    </div>

    <div v-else class="divide-y divide-gray-200">
      <div
        v-for="region in regions"
        :key="region.id"
        class="p-4 hover:bg-gray-50 transition-colors duration-150"
      >
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
              <span class="font-semibold text-blue-800">{{ region.codigo }}</span>
            </div>
            <div>
              <h4 class="font-medium text-gray-900">{{ region.nombre }}</h4>
              <p class="text-sm text-gray-500">{{ region.pais }}</p>
            </div>
          </div>

          <div class="flex items-center space-x-3">
            <span :class="[
              'px-2 py-1 rounded-full text-xs font-medium',
              region.activo 
                ? 'bg-green-100 text-green-800' 
                : 'bg-red-100 text-red-800'
            ]">
              {{ region.activo ? 'Activa' : 'Inactiva' }}
            </span>

            <button
              @click="$emit('edit', region)"
              class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-full transition-colors"
              title="Editar"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
              </svg>
            </button>

            <button
              @click="$emit('delete', region)"
              class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-full transition-colors"
              title="Eliminar"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  regions: Array,
  loading: Boolean
});

defineEmits(['edit', 'delete']);
</script>
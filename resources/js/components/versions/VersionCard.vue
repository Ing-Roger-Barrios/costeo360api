<template>
  <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
    <div class="flex items-center justify-between">
      <!-- Información principal -->
      <div class="flex items-center space-x-4 min-w-0">
        <div class="flex-shrink-0 w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
          <span class="font-semibold text-purple-800 text-xs">V</span>
        </div>
        
        <div class="min-w-0">
          <div class="flex items-center space-x-2">
            <h4 class="font-medium text-gray-900">{{ version.version }}</h4>
            <!-- 👇 INDICADORES DE ESTADO -->
            <div class="flex space-x-1">
              <span 
                v-if="version.activo" 
                class="px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                title="Versión activa (trabajo actual)"
              >
                Activa
              </span>
              <span 
                v-if="version.publicada" 
                class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                title="Versión publicada (para escritorio)"
              >
                Publicada
              </span>
            </div>
          </div>
          <p class="text-sm text-gray-600 mt-1 truncate">{{ version.nombre }}</p>
          <p class="text-xs text-gray-500 mt-1">
            {{ formatDate(version.updated_at) }}
          </p>
          <p class="text-xs text-gray-400 mt-1 truncate">{{ version.descripcion }}</p>
        </div>
      </div>

      <!-- Acciones -->
      <div class="flex items-center space-x-2">
        <!-- Botón de activar (solo si no está activa) -->
        <button
          v-if="!version.activo"
          @click="$emit('activate', version)"
          class="p-1.5 text-green-600 hover:text-green-800 hover:bg-green-50 rounded-full transition-colors"
          title="Activar versión para trabajo"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </button>

        <!-- Botón de publicar (solo si es la versión activa y no está publicada) -->
        <button
          v-else-if="version.activo && !version.publicada"
          @click="$emit('publish', version)"
          class="p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-full transition-colors"
          title="Publicar versión para escritorio"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
        </button>

        <!-- Botón de editar -->
        <button
          @click="$emit('edit', version)"
          class="p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-full transition-colors"
          title="Editar"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
          </svg>
        </button>

        <!-- Botón de eliminar -->
        <button
          @click="$emit('delete', version)"
          class="p-1.5 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-full transition-colors"
          title="Eliminar"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue';

const props = defineProps({
  version: Object
});

const formatDate = (dateString) => {
  if (!dateString) return 'Sin fecha';
  const date = new Date(dateString);
  return date.toLocaleDateString('es-BO', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};
</script>
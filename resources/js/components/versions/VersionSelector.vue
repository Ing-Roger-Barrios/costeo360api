<template>
  <div class="flex items-center space-x-6">
    

    <!-- Última versión publicada (app escritorio) -->
    <div class="flex items-center space-x-2">
      <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M9 11a2 2 0 012-2m-2 2v6a2 2 0 002 2m-2-2h6"></path>
      </svg>
      <span class="text-xs font-medium text-gray-700">Escritorio:</span>
      
      <div v-if="loading" class="text-xs text-gray-500">
        Cargando...
      </div>
      
      <div v-else-if="publishedVersion" class="flex items-center space-x-1"> <!-- 👈 CAMBIAR A publishedVersion -->
        <span class="px-1.5 py-0.5 bg-blue-100 text-blue-800 text-xs font-medium rounded">
          {{ publishedVersion.version }}
        </span>
        <span class="text-xs text-gray-600 max-w-[120px] truncate">{{ publishedVersion.nombre }}</span>
      </div>
      
      <div v-else class="text-xs text-gray-500">
        Sin versiones
      </div>
    </div>

    <!-- Versión activa (trabajo actual) -->
    <div class="flex items-center space-x-2">
      <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
      </svg>
      <span class="text-xs font-medium text-gray-700">Trabajo:</span>
      
      <div v-if="loading" class="text-xs text-gray-500">
        Cargando...
      </div>
      
      <div v-else-if="currentVersion" class="flex items-center space-x-1">
        <span class="px-1.5 py-0.5 bg-green-100 text-green-800 text-xs font-medium rounded">
          {{ currentVersion.version }}
        </span>
        <span class="text-xs text-gray-600 max-w-[120px] truncate">{{ currentVersion.nombre }}</span>
      </div>
      
      <div v-else class="text-xs text-red-600 font-medium">
        ⚠️ Sin versión
      </div>
    </div>

    <!-- Botón para abrir selector -->
    <button
      @click="openVersionSelector"
      class="p-1 text-gray-600 hover:text-gray-800"
      title="Gestionar versiones"
    >
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c-.94 1.543.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
      </svg>
    </button>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useActiveVersionStore } from '../../stores/activeVersion';

const activeVersionStore = useActiveVersionStore();
const loading = ref(false);

// 👇 CORREGIR LAS REFERENCIAS
const currentVersion = computed(() => activeVersionStore.currentVersion);
const publishedVersion = computed(() => activeVersionStore.publishedVersion); // 👈 CAMBIAR A publishedVersion

onMounted(() => {
  loadVersionsInfo();
});

const loadVersionsInfo = async () => {
  loading.value = true;
  try {
    await activeVersionStore.fetchAllVersionsInfo();
  } finally {
    loading.value = false;
  }
};

const openVersionSelector = () => {
  window.location.href = '/dashboard/versions';
};
</script>
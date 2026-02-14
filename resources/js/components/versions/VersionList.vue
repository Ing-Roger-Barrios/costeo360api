<template>
  <div class="bg-white rounded-lg shadow-md overflow-hidden">
    <!-- Header con búsqueda -->
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h3 class="text-lg font-semibold text-gray-900">Lista de Versiones</h3>
          <p class="text-sm text-gray-500">Total: {{ pagination.total }} versiones</p>
        </div>
        
        <div class="relative">
          <input
            v-model="searchQuery"
            @input="debounceSearch"
            type="text"
            placeholder="Buscar por nombre..."
            class="pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 w-full sm:w-64"
          />
          <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
        </div>
      </div>
    </div>

    <!-- Loading state -->
    <div v-if="loading" class="p-8 text-center">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
      <p class="mt-2 text-gray-500">Cargando versiones...</p>
    </div>

    <!-- Empty state -->
    <div v-else-if="versions.length === 0" class="p-8 text-center">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No se encontraron versiones</h3>
      <p class="mt-1 text-sm text-gray-500">Crea tu primera versión usando el botón de arriba.</p>
    </div>

    <!-- Lista de versiones -->
    <div v-else class="divide-y divide-gray-200 p-4">
      <VersionCard
        v-for="version in versions"
        :key="version.id"
        :version="version"
        @edit="$emit('edit', version)"
        @delete="$emit('delete', version)"
        @activate="$emit('activate', version)"
        @publish="$emit('publish', version)"  
      />
    </div>

    <!-- Paginación -->
    <div v-if="pagination.last_page > 1" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
      <div class="flex items-center justify-between">
        <div class="text-sm text-gray-700">
          Mostrando {{ (pagination.current_page - 1) * pagination.per_page + 1 }} 
          a {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }} 
          de {{ pagination.total }} resultados
        </div>
        
        <div class="flex space-x-2">
          <button
            :disabled="pagination.current_page <= 1"
            @click="changePage(pagination.current_page - 1)"
            class="px-3 py-1 rounded-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Anterior
          </button>
          
          <button
            v-for="page in visiblePages"
            :key="page"
            @click="changePage(page)"
            :class="[
              'px-3 py-1 rounded-md text-sm font-medium',
              page === pagination.current_page
                ? 'bg-blue-600 text-white'
                : 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50'
            ]"
          >
            {{ page }}
          </button>
          
          <button
            :disabled="pagination.current_page >= pagination.last_page"
            @click="changePage(pagination.current_page + 1)"
            class="px-3 py-1 rounded-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Siguiente
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useVersionsStore } from '../../stores/versions';
import VersionCard from './VersionCard.vue';

const props = defineProps({
  loading: Boolean
});

 
const emit = defineEmits(['edit', 'delete', 'activate', 'publish']); // 👈 AÑADIR publish
const versionsStore = useVersionsStore();

// Local state
const searchQuery = ref('');

// Computed properties
const versions = computed(() => versionsStore.versions);
const pagination = computed(() => versionsStore.pagination);

// Debounce search
let searchTimeout = null;
const debounceSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    applyFilters();
  }, 300);
};

// Apply filters
const applyFilters = async () => {
  const filters = {
    search: searchQuery.value
  };
  await versionsStore.fetchVersions(filters);
};

// Pagination
const visiblePages = computed(() => {
  const currentPage = pagination.value.current_page;
  const totalPages = pagination.value.last_page;
  const pages = [];
  
  if (totalPages <= 5) {
    for (let i = 1; i <= totalPages; i++) {
      pages.push(i);
    }
  } else {
    pages.push(1);
    if (currentPage > 3) pages.push('...');
    const start = Math.max(2, currentPage - 1);
    const end = Math.min(totalPages - 1, currentPage + 1);
    for (let i = start; i <= end; i++) {
      pages.push(i);
    }
    if (currentPage < totalPages - 2) pages.push('...');
    pages.push(totalPages);
  }
  
  return pages;
});

const changePage = async (page) => {
  if (page < 1 || page > pagination.value.last_page) return;
  await versionsStore.updateFilter('page', page);
};

// Load initial data
onMounted(async () => {
  await versionsStore.fetchVersions();
});

// Watch for external filter changes
watch(() => versionsStore.filters, (newFilters) => {
  searchQuery.value = newFilters.search || '';
}, { deep: true });
</script>
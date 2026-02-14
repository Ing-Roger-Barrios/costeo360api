<template>
  <div class="bg-white rounded-lg shadow-md overflow-hidden">
    <!-- Header con búsqueda -->
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h3 class="text-lg font-semibold text-gray-900">Lista de Categorías</h3>
          <p class="text-sm text-gray-500">Total: {{ pagination.total }} categorías</p>
        </div>
        
        <div class="relative">
          <input
            v-model="searchQuery"
            @input="debounceSearch"
            type="text"
            placeholder="Buscar por código o nombre..."
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
      <p class="mt-2 text-gray-500">Cargando categorías...</p>
    </div>

    <!-- Empty state -->
    <div v-else-if="categories.length === 0" class="p-8 text-center">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M9 11a2 2 0 012-2m-2 2v6a2 2 0 002 2m-2-2h6"></path>
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No se encontraron categorías</h3>
      <p class="mt-1 text-sm text-gray-500">Crea tu primera categoría usando el botón de arriba.</p>
    </div>

    <!-- Lista de categorías -->
    <div v-else class="divide-y divide-gray-200">
      <div
        v-for="category in categories"
        :key="category.id"
        class="p-4 hover:bg-gray-50 transition-colors duration-150"
      >
        <div class="flex items-center justify-between">
          <!-- Información principal -->
          <div class="flex items-center space-x-4 min-w-0">
            <div class="flex-shrink-0 w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
              <span class="font-semibold text-orange-800 text-xs">C</span>
            </div>
            
            <div class="min-w-0">
              <div class="flex items-center space-x-2">
                <h4 class="font-medium text-gray-900 truncate">{{ category.codigo }}</h4>
              </div>
              <p class="text-sm text-gray-600 truncate">{{ category.nombre }}</p>
              <div class="flex items-center space-x-4 mt-1">
                <span class="text-xs text-gray-500">
                  {{ category.modulos?.length || 0 }} módulos
                </span>
                <span class="text-xs text-gray-500">
                  Precio: <span class="font-medium text-green-600">{{ formatCurrency(category.precio_total) }}</span>
                </span>
              </div>
            </div>
          </div>

          <!-- Acciones y estado -->
          <div class="flex items-center space-x-3">
            <span :class="[
              'px-2 py-1 rounded-full text-xs font-medium',
              category.activo 
                ? 'bg-green-100 text-green-800' 
                : 'bg-red-100 text-red-800'
            ]">
              {{ category.activo ? 'Activa' : 'Inactiva' }}
            </span>

            <button
              @click="$emit('edit', category)"
              class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-full transition-colors"
              title="Editar"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
              </svg>
            </button>

            <button
              @click="$emit('delete', category)"
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
import { useCategoriesStore } from '../../stores/categories';

const props = defineProps({
  loading: Boolean
});

const emit = defineEmits(['edit', 'delete']);

const categoriesStore = useCategoriesStore();

// Local state
const searchQuery = ref('');

// Computed properties
const categories = computed(() => categoriesStore.categories);
const pagination = computed(() => categoriesStore.pagination);

// Calcular precio total de la categoría
const calculateCategoryPrice = (category) => {
  return category.modulos?.reduce((total, modulo) => {
    return total + (modulo.precio_total || 0);
  }, 0) || 0;
};

// Agregar precio_total a cada categoría
const categoriesWithPrices = computed(() => {
  return categories.value.map(category => ({
    ...category,
    precio_total: calculateCategoryPrice(category)
  }));
});

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
  await categoriesStore.fetchCategories(filters);
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
  await categoriesStore.updateFilter('page', page);
};

// Format currency
const formatCurrency = (amount) => {
  if (!amount) return 'Bs 0.00';
  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB',
    minimumFractionDigits: 2
  }).format(amount);
};

// Load initial data
onMounted(async () => {
  await categoriesStore.fetchCategories();
});

// Watch for external filter changes
watch(() => categoriesStore.filters, (newFilters) => {
  searchQuery.value = newFilters.search || '';
}, { deep: true });
</script>
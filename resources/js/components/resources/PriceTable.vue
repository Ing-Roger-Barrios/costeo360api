<template>
  <div class="bg-white rounded-lg shadow-md overflow-hidden">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h3 class="text-lg font-semibold text-gray-900">Gestión Masiva de Precios</h3>
          <!--<p class="text-sm text-gray-500">
            <span v-if="versionPublicadaId" class="text-blue-600">Versión Publicada: {{ versionPublicadaId }}</span>
            <span v-else class="text-gray-500">Sin versión publicada</span>
            |
            <span v-if="versionActivaId" class="text-green-600">Versión Activa: {{ versionActivaId }}</span>
            <span v-else class="text-red-600">⚠️ Sin versión activa</span>
          </p> -->
        </div>

        <!-- Filtros -->
        <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
          <!-- Buscador -->
          <div class="relative">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Buscar por código o nombre..."
              class="pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 w-full sm:w-64"
              @input="debounceSearch"
            />
            <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
          </div>
          
          <!-- Filtro por tipo -->
          <select
            v-model="selectedType"
            class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Todos los tipos</option>
            <option 
              v-for="type in availableTypes" 
              :key="type"
              :value="type"
            >
              {{ type }}
            </option>
          </select>
          
          <!-- Botón limpiar filtros -->
          <button
            @click="clearFilters"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 flex items-center space-x-1"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            <span>Limpiar</span>
          </button>
        </div>
        
        <div class="flex space-x-2">
          <button
            @click="saveAllPrices"
            :disabled="saving || !hasChanges"
            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 disabled:opacity-50 flex items-center space-x-2"
          >
            <svg v-if="saving" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ saving ? 'Guardando...' : 'Guardar Cambios' }}</span>
          </button>
        </div>
      </div>
       <!-- Conteo de resultados -->
      <div class="mt-2 text-xs text-gray-500">
        Mostrando {{ filteredResources.length }} de {{ resources.length }} recursos
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="p-8 text-center">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
      <p class="mt-2 text-gray-500">Cargando recursos y precios...</p>
    </div>

    <!-- Tabla de precios -->
    <div v-else class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50 sticky top-0 z-10">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unidad</th>
            
            <!-- Precios publicados (solo lectura) -->
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-blue-50">
              Publicado Global
            </th>
            <th 
              v-for="region in regions" 
              :key="'pub-' + region.id"
              class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-blue-50 min-w-[120px]"
            >
              {{ region.nombre }}
            </th>
            
            <!-- Precios activos (editables) -->
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-green-50">
              ActivoGlobal
            </th>
            <th 
              v-for="region in regions" 
              :key="'act-' + region.id"
              class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-green-50 min-w-[120px]"
            >
              {{ region.nombre }}
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="resource in filteredResources" :key="resource.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ resource.codigo }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 max-w-xs truncate">{{ resource.nombre }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ resource.tipo }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ resource.unidad }}</td>
            
            <!-- Precios publicados (solo lectura) -->
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 bg-blue-50">
              {{ formatCurrency(resource.precios_publicados.global) }}
            </td>
            <td 
              v-for="region in regions" 
              :key="'pub-cell-' + resource.id + '-' + region.id"
              class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 bg-blue-50"
            >
              {{ formatCurrency(resource.precios_publicados.regionales[region.id]) }}
            </td>
            
            <!-- Precios activos (editables) -->
            <td class="px-6 py-4 whitespace-nowrap bg-green-50">
              <input
                :value="resource.precios_activos.global"
                @input="updateGlobalPrice(resource.id, $event.target.value)"
                type="number"
                step="0.01"
                min="0"
                class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                :disabled="!versionActivaId"
              />
            </td>
            <td 
              v-for="region in regions" 
              :key="'act-cell-' + resource.id + '-' + region.id"
              class="px-4 py-4 whitespace-nowrap bg-green-50"
            >
              <input
                :value="resource.precios_activos.regionales[region.id]"
                @input="updateRegionalPrice(resource.id, region.id, $event.target.value)"
                type="number"
                step="0.01"
                min="0"
                class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                :disabled="!versionActivaId"
              />
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Advertencia si no hay versión activa -->
    <div v-if="!versionActivaId && !loading" class="px-6 py-4 bg-yellow-50 border-t border-yellow-200">
      <p class="text-sm text-yellow-700">
        ⚠️ No hay una versión activa. Activa una versión para poder editar precios.
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useBulkPricesStore } from '../../stores/bulkPrices';
import Swal from 'sweetalert2';

const bulkPricesStore = useBulkPricesStore();

const resources = ref([]);
const regions = ref([]);
const loading = ref(false);
const saving = ref(false);
const searchQuery = ref('');
const selectedType = ref('');

// Exponer IDs de versiones
const versionActivaId = computed(() => bulkPricesStore.versionActivaId);
const versionPublicadaId = computed(() => bulkPricesStore.versionPublicadaId);

const changes = ref(new Map());

const hasChanges = computed(() => changes.value.size > 0);

// 👇 MÉTODO PARA LIMPIAR FILTROS
const clearFilters = () => {
  searchQuery.value = '';
  selectedType.value = '';
};

// Tipos disponibles
const availableTypes = computed(() => {
  const types = new Set(resources.value.map(r => r.tipo));
  return Array.from(types).sort();
});

// Recursos filtrados
const filteredResources = computed(() => {
  let filtered = [...resources.value];
  
  // Filtro por búsqueda
  if (searchQuery.value) {
    const searchTerm = searchQuery.value.toLowerCase();
    filtered = filtered.filter(resource =>
      resource.codigo.toLowerCase().includes(searchTerm) ||
      resource.nombre.toLowerCase().includes(searchTerm)
    );
  }
  
  // Filtro por tipo
  if (selectedType.value) {
    filtered = filtered.filter(resource => 
      resource.tipo === selectedType.value
    );
  }
  
  return filtered;
});


const formatCurrency = (amount) => {
  if (amount === null || amount === undefined) return 'Bs 0.00';
  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB',
    minimumFractionDigits: 2
  }).format(amount);
};

const loadResources = async () => {
  loading.value = true;
  try {
    await bulkPricesStore.fetchResourcesWithPrices();
    resources.value = bulkPricesStore.resources;
    regions.value = bulkPricesStore.regions;
    changes.value.clear();
  } finally {
    loading.value = false;
  }
};

const updateGlobalPrice = (resourceId, value) => {
  if (!versionActivaId.value) return;
  
  const resource = resources.value.find(r => r.id === resourceId);
  if (resource) {
    resource.precios_activos.global = parseFloat(value) || 0;
    trackChange(resourceId);
  }
};

const updateRegionalPrice = (resourceId, regionId, value) => {
  if (!versionActivaId.value) return;
  
  const resource = resources.value.find(r => r.id === resourceId);
  if (resource) {
    resource.precios_activos.regionales[regionId] = parseFloat(value) || 0;
    trackChange(resourceId);
  }
};

const trackChange = (resourceId) => {
  changes.value.set(resourceId, true);
};

const saveAllPrices = async () => {
  if (!versionActivaId.value) {
    Swal.fire({
      title: 'Sin versión activa',
      text: 'Debes tener una versión activa para guardar cambios.',
      icon: 'warning'
    });
    return;
  }

  const confirmed = await Swal.fire({
    title: '¿Guardar cambios?',
    text: `¿Estás seguro de guardar los cambios en ${changes.value.size} recursos?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, guardar',
    cancelButtonText: 'Cancelar',
    reverseButtons: true
  });

  if (!confirmed.isConfirmed) return;

  try {
    const updates = [];
    
    changes.value.forEach((_, resourceId) => {
      const resource = resources.value.find(r => r.id === resourceId);
      if (resource) {
        updates.push({
          recurso_id: resourceId,
          precios: {
            global: resource.precios_activos.global,
            regionales: resource.precios_activos.regionales
          }
        });
      }
    });

    await bulkPricesStore.savePrices(updates);
    
    await Swal.fire({
      title: '¡Precios guardados!',
      text: 'Los precios han sido actualizados exitosamente.',
      icon: 'success',
      timer: 2000,
      showConfirmButton: false
    });
    
    // Recargar datos
    await loadResources();
    
  } catch (error) {
    Swal.fire({
      title: 'Error',
      text: bulkPricesStore.error || 'Error al guardar los precios',
      icon: 'error'
    });
  }
};

// Filtros
/*const availableTypes = computed(() => {
  const types = new Set(resources.value.map(r => r.tipo));
  return Array.from(types).sort();
});

const filteredResources = computed(() => {
  let filtered = [...resources.value];
  
  if (searchQuery.value) {
    const searchTerm = searchQuery.value.toLowerCase();
    filtered = filtered.filter(resource =>
      resource.codigo.toLowerCase().includes(searchTerm) ||
      resource.nombre.toLowerCase().includes(searchTerm)
    );
  }
  
  if (selectedType.value) {
    filtered = filtered.filter(resource => 
      resource.tipo === selectedType.value
    );
  }
  
  return filtered;
});*/

onMounted(() => {
  loadResources();
});
</script>
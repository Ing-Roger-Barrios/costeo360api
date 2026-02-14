<template>
  <div class="bg-white rounded-lg shadow-md p-6">
    <h3 class="text-lg font-semibold mb-4">
      Precios Regionales - {{ resource.codigo }} - {{ resource.nombre }}
    </h3>

    <div v-if="loading" class="text-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
      <p class="mt-2 text-gray-500">Cargando regiones y precios...</p>
    </div>

    <div v-else-if="error" class="p-4 bg-red-50 text-red-700 rounded-md">
      {{ error }}
    </div>

    <div v-else class="space-y-4">
      <!-- Tabla de precios regionales -->
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Región</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Regional</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="region in regions" :key="region.id">
              <td class="px-6 py-4 whitespace-nowrap">{{ region.nombre }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ region.codigo }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <input
                  :ref="`price-${region.id}`"
                  :value="getRegionalPrice(region.id)"
                  @change="updatePrice(region.id, $event.target.value)"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-32 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="0.00"
                />
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <button
                  v-if="hasRegionalPrice(region.id)"
                  @click="deletePrice(region.id)"
                  class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-full transition-colors"
                  title="Eliminar precio regional"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                </button>
                <span v-else class="text-gray-400">-</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex justify-end space-x-3 pt-4">
        <button
          @click="$emit('close')"
          class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500"
        >
          Cerrar
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRegionalPricesStore } from '../../stores/regionalPrices';

const props = defineProps({
  resource: Object,
  loading: Boolean
});

const emit = defineEmits(['close']);

const regionalPricesStore = useRegionalPricesStore();
const error = ref(null);

// Computed properties
const regions = computed(() => regionalPricesStore.regions);
const regionalPrices = computed(() => regionalPricesStore.regionalPrices);

// Methods
const getRegionalPrice = (regionId) => {
  const price = regionalPrices.value.find(p => p.region_id === regionId);
  return price ? price.precio_regional : '';
};

const hasRegionalPrice = (regionId) => {
  return regionalPrices.value.some(p => p.region_id === regionId);
};

const updatePrice = async (regionId, priceValue) => {
  if (!priceValue || parseFloat(priceValue) <= 0) {
    // Si el precio es inválido, eliminar el precio regional
    if (hasRegionalPrice(regionId)) {
      await deletePrice(regionId);
    }
    return;
  }

  try {
    await regionalPricesStore.updateRegionalPrice(
      props.resource.id, 
      regionId, 
      { precio_regional: parseFloat(priceValue) }
    );
  } catch (err) {
    error.value = regionalPricesStore.error;
  }
};

const deletePrice = async (regionId) => {
  if (confirm(`¿Estás seguro de eliminar el precio regional para ${regions.value.find(r => r.id === regionId)?.nombre}?`)) {
    try {
      await regionalPricesStore.deleteRegionalPrice(props.resource.id, regionId);
    } catch (err) {
      error.value = regionalPricesStore.error;
    }
  }
};

// Load data on mount
onMounted(async () => {
  try {
    await regionalPricesStore.fetchRegions();
    await regionalPricesStore.fetchRegionalPrices(props.resource.id);
  } catch (err) {
    error.value = regionalPricesStore.error;
  }
});
</script>
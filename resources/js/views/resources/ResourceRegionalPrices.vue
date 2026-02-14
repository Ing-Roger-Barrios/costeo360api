<template>
  <div class="max-w-6xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Precios Regionales</h1>
        <p class="text-gray-600">Gestiona precios por región para recursos específicos</p>
      </div>
      
      <button
        @click="$router.back()"
        class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 flex items-center space-x-2"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        <span>Volver</span>
      </button>
    </div>

    <RegionalPriceForm
      :resource="resource"
      :loading="regionalPricesStore.loading"
      @close="$router.back()"
    />

    <Alert
      v-if="alert.message"
      :type="alert.type"
      :message="alert.message"
      @close="alert.message = ''"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useRegionalPricesStore } from '../../stores/regionalPrices';
import RegionalPriceForm from '../../components/resources/RegionalPriceForm.vue';
import Alert from '../../components/ui/Alert.vue';

const route = useRoute();
const regionalPricesStore = useRegionalPricesStore();
const alert = ref({ message: '', type: 'success' });

// Get resource from route params or query
const resource = ref({
  id: route.params.resourceId || route.query.resourceId,
  codigo: route.query.codigo || '',
  nombre: route.query.nombre || ''
});

onMounted(() => {
  if (!resource.value.id) {
    alert.value = { message: 'Recurso no especificado', type: 'error' };
  }
});
</script>
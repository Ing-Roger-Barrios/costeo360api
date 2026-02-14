<template>
  <div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-md p-6">
      <h2 class="text-xl font-semibold mb-4">Importar Módulos e Items</h2>
      
      <div v-if="loading" class="text-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <p class="text-gray-600 mt-2">Importando módulos e items...</p>
      </div>

      <div v-else-if="success" class="text-center py-8">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">¡Importación completada!</h3>
        <p class="text-gray-600">
          {{ stats.modulos }} módulos y {{ stats.items }} items importados.
        </p>
        <button
          @click="goToCategory"
          class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
        >
          Ver categoría
        </button>
      </div>

      <div v-else class="text-center py-8">
        <button
          @click="startImport"
          class="px-6 py-3 bg-green-600 text-white rounded-md hover:bg-green-700 font-medium"
        >
          Iniciar importación de módulos e items
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const route = useRoute();

const loading = ref(false);
const success = ref(false);
const stats = ref({ modulos: 0, items: 0 });

const startImport = async () => {
  const categoryId = route.params.id;
  const extractedPath = localStorage.getItem('ddp_extracted_path');

  if (!extractedPath) {
    alert('No se encontró la ruta de extracción del archivo .DDP');
    return;
  }

  loading.value = true;

  try {
    const response = await axios.post('/api/v1/import/modules-items', {
      category_id: categoryId,
      extracted_path: extractedPath
    });

    success.value = true;
    stats.value = response.data.stats;

  } catch (error) {
    alert('Error al importar: ' + (error.response?.data?.error || 'Error desconocido'));
  } finally {
    loading.value = false;
  }
};

const goToCategory = () => {
  router.push(`/categories/${route.params.id}`);
};
</script>
<template>
  <div class="max-w-6xl mx-auto">
    <div class="mb-6 flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Gestión de Regiones</h1>
        <p class="text-gray-600">Administra las regiones/ciudades para precios regionales</p>
      </div>
      
      <button
        @click="showForm = true; regionsStore.clearCurrentRegion()"
        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center space-x-2"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        <span>Nueva Región</span>
      </button>
    </div>

    <!-- Formulario modal -->
    <div v-if="showForm" class="mb-6">
      <RegionForm
        :region="regionsStore.currentRegion"
        :loading="regionsStore.loading"
        @submit="handleSaveRegion"
        @cancel="showForm = false"
      />
    </div>

    <!-- Lista de regiones -->
    <RegionList
      :regions="regionsStore.regions"
      :loading="regionsStore.loading"
      @edit="handleEditRegion"
      @delete="handleDeleteRegion"
    />

    <!-- Alertas -->
    <Alert
      v-if="alert.message"
      :type="alert.type"
      :message="alert.message"
      @close="alert.message = ''"
    />
  </div>
</template>

<script setup>
import Sidebar from '../../components/layout/Sidebar.vue';
import Header from '../../components/layout/Header.vue';
import { ref, onMounted } from 'vue';
import { useRegionsStore } from '../../stores/regions';
import RegionForm from '../../components/regions/RegionForm.vue';
import RegionList from '../../components/regions/RegionList.vue';
import Alert from '../../components/ui/Alert.vue';

const regionsStore = useRegionsStore();
const showForm = ref(false);
const alert = ref({ message: '', type: 'success' });

onMounted(async () => {
  await regionsStore.fetchRegions();
});

const handleSaveRegion = async (regionData) => {
  try {
    if (regionsStore.currentRegion) {
      await regionsStore.updateRegion(regionsStore.currentRegion.id, regionData);
      alert.value = { message: 'Región actualizada exitosamente', type: 'success' };
    } else {
      await regionsStore.createRegion(regionData);
      alert.value = { message: 'Región creada exitosamente', type: 'success' };
    }
    showForm.value = false;
  } catch (error) {
    alert.value = { message: regionsStore.error || 'Error al guardar la región', type: 'error' };
  }
};

const handleEditRegion = (region) => {
  regionsStore.setCurrentRegion(region);
  showForm.value = true;
};

const handleDeleteRegion = async (region) => {
  if (confirm(`¿Estás seguro de eliminar la región "${region.nombre}"?`)) {
    try {
      await regionsStore.deleteRegion(region.id);
      alert.value = { message: 'Región eliminada exitosamente', type: 'success' };
    } catch (error) {
      alert.value = { message: regionsStore.error || 'Error al eliminar la región', type: 'error' };
    }
  }
};
</script>
``---

## 🔧 **Añadir la ruta**

### **Actualiza `resources/js/router/index.js`**:
```javascript
// Añade esta ruta
{
  path: '/regions',
  name: 'regions',
  component: () => import('../views/regions/RegionsView.vue'),
  meta: { requiresAuth: true }
}
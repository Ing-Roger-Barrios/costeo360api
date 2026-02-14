<template>
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Panel izquierdo: Recursos disponibles -->
    <div class="bg-gray-50 rounded-lg p-4">
      <h3 class="text-lg font-semibold mb-4">Recursos Disponibles</h3>
      
      <!-- 🔍 Buscador de recursos -->
      <div class="mb-4">
        <div class="relative">
          <input
            v-model="resourceSearch"
            type="text"
            placeholder="Buscar recursos..."
            class="w-full px-3 py-2 pl-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            @input="filterResources"
          />
          <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
        </div>
      </div>
      
      <div v-if="loadingResources" class="text-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <p class="mt-2 text-gray-500">Cargando recursos...</p>
      </div>
      
      <div v-else class="space-y-3 max-h-100 overflow-y-auto">
        <ResourceCard
          v-for="resource in filteredResources"
          :key="resource.id"
          :resource="resource"
          @click="selectResource(resource)"
        />
        
        <div v-if="filteredResources.length === 0" class="text-center py-8 text-gray-500">
          No se encontraron recursos
        </div>
      </div>
    </div>

    <!-- Panel derecho: Item actual -->
    <div 
      class="bg-white rounded-lg border  border-gray-200 p-4"
      @dragover.prevent="onDragOver"
      @drop.prevent="onDrop"
    >
      <h3 class="text-lg font-semibold mb-4">Item Actual</h3>
      
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Código *</label>
        <input
          v-model="itemData.codigo"
          type="text"
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Ej: CONC-001"
          required
        />
      </div>
      
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Descripción *</label>
        <input
          v-model="itemData.descripcion"
          type="text"
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Ej: Concreto f'c 175 kg/cm²"
          required
        />
      </div>
      
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Unidad *</label>
        <input
          v-model="itemData.unidad"
          type="text"
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Ej: m³, m², unidad"
          required
        />
      </div>

      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Recursos del Item</label>
        
        <div v-if="itemData.recursos.length === 0" class="text-center py-8 text-gray-500">
          <p>Arrastra recursos aquí o haz clic en los recursos disponibles</p>
        </div>
        
        <div v-else class="space-y-3">
          <div
            v-for="(itemResource, index) in itemData.recursos"
            :key="itemResource.id || index"
            class="border border-gray-200 rounded-lg p-3"
          >
            <div class="flex items-center justify-between">
              <div class="flex-1 min-w-0">
                <p class="font-medium text-gray-900">{{ itemResource.codigo }}</p>
                <p class="text-sm text-gray-500">{{ itemResource.nombre }}</p>
                <div class="mt-2 flex items-center space-x-2">
                  <label class="text-sm text-gray-600">Rendimiento:</label>
                  <input
                    v-model.number="itemResource.rendimiento"
                    type="number"
                    step="0.001"
                    min="0"
                    class="w-24 px-2 py-1 border border-gray-300 rounded text-sm"
                    @change="calculateTotalPrice"
                  />
                  <span class="text-sm text-gray-500">/{{ itemData.unidad }}</span>
                </div>
              </div>
              <button
                @click="removeResource(index)"
                class="p-1 text-red-600 hover:text-red-800"
                title="Eliminar recurso"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-gray-50 p-3 rounded-lg">
        <div class="flex justify-between items-center">
          <span class="font-medium">Precio Total:</span>
          <span class="text-lg font-bold text-green-600">{{ formatCurrency(totalPrice) }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// 👇 IMPORTACIONES COMPLETAS
import { ref, computed, onMounted, watch } from 'vue';
import ResourceCard from './ResourceCard.vue';
import axios from 'axios';

const props = defineProps({
  modelValue: Object,
  item: Object
});

const emit = defineEmits(['update:modelValue']);

// 👇 REFERENCIAS REACTIVAS CORRECTAS
const resourceSearch = ref('');
const availableResources = ref([]);
const filteredResources = ref([]); // 👈 Esta línea faltaba
const loadingResources = ref(false);
const itemData = ref({
  codigo: '',
  descripcion: '',
  unidad: '',
  rendimiento: '',
  notas: '',
  activo: true, // 👈 CLAVE
  recursos: []
});

// Calcular precio total
const totalPrice = computed(() => {
  return itemData.value.recursos.reduce((total, resource) => {
    return total + (resource.rendimiento * resource.precio_referencia);
  }, 0);
});

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB',
    minimumFractionDigits: 2
  }).format(amount);
};

// 👇 FILTRAR RECURSOS (CORREGIDO)
const filterResources = () => {
  const searchTerm = resourceSearch.value.toLowerCase().trim();
  if (!searchTerm) {
    filteredResources.value = availableResources.value;
  } else {
    filteredResources.value = availableResources.value.filter(resource =>
      resource.codigo.toLowerCase().includes(searchTerm) ||
      resource.nombre.toLowerCase().includes(searchTerm) ||
      resource.tipo.toLowerCase().includes(searchTerm)
    );
  }
};

// Cargar recursos disponibles
const loadAvailableResources = async () => {
  loadingResources.value = true;
  try {
    const response = await axios.get('/api/v1/recursos');
    availableResources.value = response.data.data || response.data;
    filteredResources.value = availableResources.value; // 👈 Inicializar correctamente
  } catch (error) {
    console.error('Error loading resources:', error);
  } finally {
    loadingResources.value = false;
  }
};

// Añadir recurso al item
const addResourceToItem = (resource) => {
  const existingIndex = itemData.value.recursos.findIndex(r => r.id === resource.id);
  if (existingIndex === -1) {
    itemData.value.recursos.push({
      ...resource,
      rendimiento: 1.0
    });
    calculateTotalPrice();
  }
};

// Eliminar recurso del item
const removeResource = (index) => {
  itemData.value.recursos.splice(index, 1);
  calculateTotalPrice();
};

// Calcular precio total
const calculateTotalPrice = () => {
  emit('update:modelValue', { ...itemData.value });
};

// Manejar selección de recurso
const selectResource = (resource) => {
  addResourceToItem(resource);
};

// 👇 MÉTODOS PARA DRAG & DROP
const onDragOver = (event) => {
  event.preventDefault();
  event.currentTarget.classList.add('bg-blue-50', 'border-blue-300');
};

const onDrop = (event) => {
  event.preventDefault();
  event.currentTarget.classList.remove('bg-blue-50', 'border-blue-300');
  
  try {
    const resourceData = event.dataTransfer.getData('application/json');
    const resource = JSON.parse(resourceData);
    
    if (resource && resource.id) {
      addResourceToItem(resource);
    }
  } catch (error) {
    console.error('Error al procesar el recurso arrastrado:', error);
  }
};

// Inicializar datos
onMounted(() => {
  loadAvailableResources();
  
  if (props.item) {
    itemData.value = {
      codigo: props.item.codigo || '',
      descripcion: props.item.descripcion || '',
      unidad: props.item.unidad || '',
      notas: props.item.notas || '',
      activo: props.item.activo ?? true, // 👈 CLAVE
      recursos: props.item.recursos?.map(r => ({
        id: r.id,
        codigo: r.codigo,
        nombre: r.nombre,
        tipo: r.tipo,
        unidad: r.unidad,
        precio_referencia: r.precio_referencia,
        rendimiento: r.pivot?.rendimiento || r.rendimiento || 1.0
      })) || []
    };
  } else if (props.modelValue) {
    itemData.value = { ...props.modelValue };
  }
});

// Emitir cambios
watch(itemData, (newVal) => {
  emit('update:modelValue', { ...newVal });
}, { deep: true });
</script>
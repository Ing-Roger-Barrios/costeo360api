<template>
  <div class="flex flex-col lg:flex-row gap-6">
    <!-- Panel izquierdo: Items disponibles -->
    <div class="bg-gray-50 rounded-lg p-4 w-full lg:w-1/2">
      <h3 class="text-lg font-semibold mb-4">Items Disponibles</h3>
      
      <!-- Buscador de items -->
      <div class="mb-4">
        <div class="relative">
          <input
            v-model="itemSearch"
            type="text"
            placeholder="Buscar items..."
            class="w-full px-3 py-2 pl-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            @input="filterItems"
          />
          <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
        </div>
      </div>
      
      <div v-if="loadingItems" class="text-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <p class="mt-2 text-gray-500">Cargando items...</p>
      </div>
      
      <div ref="itemsContainer" class="space-y-3 overflow-y-auto" :style="{ maxHeight: modulePanelHeight + 'px' }">
        <ItemCard
          v-for="item in filteredItems"
          :key="item.id"
          :item="item"
          @click="selectItem(item)"
        />
        
        <div v-if="filteredItems.length === 0" class="text-center py-8 text-gray-500">
          No se encontraron items
        </div>
      </div>
    </div>

    <!-- Panel derecho: Módulo actual -->
    <div 
      ref="modulePanel"
      class="bg-white rounded-lg border border-gray-200 p-4 w-full lg:w-1/2"
      @dragover.prevent="onDragOver"
      @drop.prevent="onDrop"
    >
      <h3 class="text-lg font-semibold mb-4">Módulo Actual</h3>
      
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Código *</label>
        <input
          v-model="moduleData.codigo"
          type="text"
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Ej: MOD-001"
          required
        />
      </div>
      
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
        <input
          v-model="moduleData.nombre"
          type="text"
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Ej: Estructura de Hormigón"
          required
        />
      </div>

      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Items del Módulo</label>
        
        <div v-if="moduleData.items.length === 0" class="text-center py-8 text-gray-500 h-full flex items-center justify-center">
          <p>Arrastra items aquí o haz clic en los items disponibles</p>
        </div>
        
        <div v-else class="space-y-3 h-full overflow-y-auto pb-4">
          <div
            v-for="(moduleItem, index) in moduleData.items"
            :key="moduleItem.id || index"
            class="border border-gray-200 rounded-lg p-3"
          >
            <div class="flex items-center justify-between">
              <div class="flex-1 min-w-0">
                <p class="font-medium text-gray-900">{{ moduleItem.codigo }}</p>
                <p class="text-sm text-gray-500">{{ moduleItem.descripcion }}</p>
                <label class="text-sm text-gray-600">Rendimiento:</label>
                <!-- 👇 CORREGIDO: vincular al rendimiento del item específico -->
                <input 
                    v-model="moduleItem.pivot_rendimiento" 
                    type="number" 
                    step="0.000001"
                    placeholder="Rendimiento"
                    min="0"
                    class="w-24 px-2 py-1 border border-gray-300 rounded text-sm"
                    @input="calculateTotalPrice"
                />
                <p class="text-xs text-green-600 font-medium mt-1">
                  {{ formatCurrency(moduleItem.precio_base) }} / {{ moduleItem.unidad }}
                </p>
              </div>
              <button
                @click="removeItem(index)"
                class="p-1 text-red-600 hover:text-red-800"
                title="Eliminar item"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-gray-50 p-3 rounded-lg mt-4">
        <div class="flex justify-between items-center">
          <span class="font-medium">Precio Total:</span>
          <span class="text-lg font-bold text-green-600">{{ formatCurrency(totalPrice) }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import ItemCard from './ItemCard.vue';
import axios from 'axios';

const props = defineProps({
  modelValue: Object,
  module: Object
});

const emit = defineEmits(['update:modelValue']);

// Referencias DOM
const modulePanel = ref(null);
const itemsContainer = ref(null);
const modulePanelHeight = ref(400);

// Estado
const itemSearch = ref('');
const availableItems = ref([]);
const filteredItems = ref([]);
const loadingItems = ref(false);

const moduleData = ref({
  codigo: '',
  nombre: '',
  activo: true,
  items: []
});

// Calcular precio total
const totalPrice = computed(() => {
  return moduleData.value.items.reduce((total, item) => {
    return total + (item.precio_base || 0);
  }, 0);
});

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB',
    minimumFractionDigits: 2
  }).format(amount);
};

// Filtrar items
const filterItems = () => {
  const searchTerm = itemSearch.value.toLowerCase().trim();
  if (!searchTerm) {
    filteredItems.value = availableItems.value;
  } else {
    filteredItems.value = availableItems.value.filter(item =>
      item.codigo.toLowerCase().includes(searchTerm) ||
      item.descripcion.toLowerCase().includes(searchTerm)
    );
  }
};

// Cargar items disponibles
const loadAvailableItems = async () => {
  loadingItems.value = true;
  try {
    const response = await axios.get('/api/v1/items');
    availableItems.value = response.data.data || response.data;
    filteredItems.value = availableItems.value;
  } catch (error) {
    console.error('Error loading items:', error);
  } finally {
    loadingItems.value = false;
  }
};

// 👇 CORREGIDO: Añadir item con rendimiento
/*const addItemToModule = (item) => {
  const existingIndex = moduleData.value.items.findIndex(i => i.id === item.id);
  if (existingIndex === -1) {
    // Añadir el item con su rendimiento base y campo para pivot
    const itemWithPivot = {
      ...item,
      pivot_rendimiento: item.pivot_rendimiento || item.rendimiento || 0 // Usar rendimiento del item como valor inicial
    };
    moduleData.value.items.push(itemWithPivot);
    calculateTotalPrice();
  }
};*/
const addItemToModule = (item) => {
  if (!item.id) {
    console.error('Item sin ID:', item);
    return;
  }
  
  const existingIndex = moduleData.value.items.findIndex(i => i.id === item.id);
  if (existingIndex === -1) {
    const itemWithRendimiento = {
      ...item,
      pivot_rendimiento: item.pivot_rendimiento || item.rendimiento || 0
    };
    moduleData.value.items.push(itemWithRendimiento);
  }
};

// Eliminar item del módulo
const removeItem = (index) => {
  moduleData.value.items.splice(index, 1);
  calculateTotalPrice();
};

// Calcular precio total
const calculateTotalPrice = () => {
  emit('update:modelValue', { ...moduleData.value });
};

// Manejar selección de item
const selectItem = (item) => {
  addItemToModule(item);
};

// Métodos para drag & drop
const onDragOver = (event) => {
  event.preventDefault();
  event.currentTarget.classList.add('bg-blue-50', 'border-blue-300');
};

const onDrop = (event) => {
  event.preventDefault();
  event.currentTarget.classList.remove('bg-blue-50', 'border-blue-300');
  
  try {
    const itemData = event.dataTransfer.getData('application/json');
    const item = JSON.parse(itemData);
    
    if (item && item.id) {
      addItemToModule(item);
    }
  } catch (error) {
    console.error('Error al procesar el item arrastrado:', error);
  }
};

// Actualizar altura del panel de items
const updatePanelHeight = () => {
  if (modulePanel.value) {
    const height = modulePanel.value.offsetHeight;
    modulePanelHeight.value = Math.max(height, 300);
  }
};

// Observar cambios en el panel del módulo
const observeModulePanel = () => {
  if (!modulePanel.value) return;
  
  const observer = new MutationObserver(() => {
    nextTick(() => {
      updatePanelHeight();
    });
  });
  
  observer.observe(modulePanel.value, {
    childList: true,
    subtree: true,
    attributes: true
  });
  
  window.addEventListener('resize', updatePanelHeight);
  
  return () => {
    observer.disconnect();
    window.removeEventListener('resize', updatePanelHeight);
  };
};

// Inicializar datos
onMounted(() => {
  loadAvailableItems();
  
  if (props.module) {
    // 👇 CORREGIDO: Incluir rendimiento de la relación pivot
    moduleData.value = {
      codigo: props.module.codigo || '',
      nombre: props.module.nombre || '',
      activo: props.module.activo !== false,
      items: props.module.items?.map(item => ({
        ...item,
        pivot_rendimiento: item.pivot?.rendimiento || item.rendimiento || 0
      })) || []
    };
  } else if (props.modelValue) {
    moduleData.value = { ...props.modelValue };
  }
  
  nextTick(() => {
    updatePanelHeight();
    observeModulePanel();
  });
});

// Emitir cambios
watch(moduleData, (newVal) => {
  emit('update:modelValue', { ...newVal });
}, { deep: true });
</script>
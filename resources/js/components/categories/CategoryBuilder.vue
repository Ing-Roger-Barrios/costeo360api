<template>
  <div class="flex flex-col lg:flex-row gap-6">
    <!-- Panel izquierdo: Módulos disponibles -->
    <div class="bg-gray-50 rounded-lg p-4 w-full lg:w-1/2">
      <h3 class="text-lg font-semibold mb-4">Módulos Disponibles</h3>
      
      <!-- Buscador de módulos -->
      <div class="mb-4">
        <div class="relative">
          <input
            v-model="moduleSearch"
            type="text"
            placeholder="Buscar módulos..."
            class="w-full px-3 py-2 pl-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            @input="filterModules"
          />
          <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
        </div>
      </div>
      
      <div v-if="loadingModules" class="text-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <p class="mt-2 text-gray-500">Cargando módulos...</p>
      </div>
      
      <div ref="modulesContainer" class="space-y-3 overflow-y-auto" :style="{ maxHeight: categoryPanelHeight + 'px' }">
        <ModuleCard
          v-for="module in filteredModules"
          :key="module.id"
          :module="module"
          @click="selectModule(module)"
        />
        
        <div v-if="filteredModules.length === 0" class="text-center py-8 text-gray-500">
          No se encontraron módulos
        </div>
      </div>
    </div>

    <!-- Panel derecho: Categoría actual -->
    <div 
      ref="categoryPanel"
      class="bg-white rounded-lg border border-gray-200 p-4 w-full lg:w-1/2"
      @dragover.prevent="onDragOver"
      @drop.prevent="onDrop"
    >
      <h3 class="text-lg font-semibold mb-4">Categoría Actual</h3>
      
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Código *</label>
        <input
          v-model="categoryData.codigo"
          type="text"
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Ej: CAT-001"
          required
        />
      </div>
      
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
        <input
          v-model="categoryData.nombre"
          type="text"
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Ej: Estructuras"
          required
        />
      </div>

      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Módulos de la Categoría</label>
        
        <div v-if="categoryData.modulos.length === 0" class="text-center py-8 text-gray-500 h-full flex items-center justify-center">
          <p>Arrastra módulos aquí o haz clic en los módulos disponibles</p>
        </div>
        
        <div v-else class="space-y-3 h-full overflow-y-auto pb-4">
          <div
            v-for="(categoryModule, index) in categoryData.modulos"
            :key="categoryModule.id || index"
            class="border border-gray-200 rounded-lg p-3"
          >
            <div class="flex items-center justify-between">
              <div class="flex-1 min-w-0">
                <p class="font-medium text-gray-900">{{ categoryModule.codigo }}</p>
                <p class="text-sm text-gray-500">{{ categoryModule.nombre }}</p>
                <p class="text-xs text-green-600 font-medium mt-1">
                  {{ formatCurrency(categoryModule.precio_total) }} / {{ categoryModule.items?.length || 0 }} items
                </p>
              </div>
              <button
                @click="removeModule(index)"
                class="p-1 text-red-600 hover:text-red-800"
                title="Eliminar módulo"
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
import ModuleCard from './ModuleCard.vue';
import axios from 'axios';

const props = defineProps({
  modelValue: Object,
  category: Object
});

const emit = defineEmits(['update:modelValue']);

// Referencias DOM
const categoryPanel = ref(null);
const modulesContainer = ref(null);
const categoryPanelHeight = ref(400);

// Estado
const moduleSearch = ref('');
const availableModules = ref([]);
const filteredModules = ref([]);
const loadingModules = ref(false);

const categoryData = ref({
  codigo: '',
  nombre: '',
  activo: true,
  modulos: []
});

// Calcular precio total
const totalPrice = computed(() => {
  return categoryData.value.modulos.reduce((total, module) => {
    return total + (module.precio_total || 0);
  }, 0);
});

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB',
    minimumFractionDigits: 2
  }).format(amount);
};

// Filtrar módulos
const filterModules = () => {
  const searchTerm = moduleSearch.value.toLowerCase().trim();
  if (!searchTerm) {
    filteredModules.value = availableModules.value;
  } else {
    filteredModules.value = availableModules.value.filter(module =>
      module.codigo.toLowerCase().includes(searchTerm) ||
      module.nombre.toLowerCase().includes(searchTerm)
    );
  }
};

// Cargar módulos disponibles
const loadAvailableModules = async () => {
  loadingModules.value = true;
  try {
    const response = await axios.get('/api/v1/modules');
    availableModules.value = response.data.data || response.data;
    filteredModules.value = availableModules.value;
  } catch (error) {
    console.error('Error loading modules:', error);
  } finally {
    loadingModules.value = false;
  }
};

// Añadir módulo a la categoría
const addModuleToCategory = (module) => {
  const existingIndex = categoryData.value.modulos.findIndex(m => m.id === module.id);
  if (existingIndex === -1) {
    categoryData.value.modulos.push({ ...module });
    calculateTotalPrice();
  }
};

// Eliminar módulo de la categoría
const removeModule = (index) => {
  categoryData.value.modulos.splice(index, 1);
  calculateTotalPrice();
};

// Calcular precio total
const calculateTotalPrice = () => {
  emit('update:modelValue', { ...categoryData.value });
};

// Manejar selección de módulo
const selectModule = (module) => {
  addModuleToCategory(module);
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
    const moduleData = event.dataTransfer.getData('application/json');
    const module = JSON.parse(moduleData);
    
    if (module && module.id) {
      addModuleToCategory(module);
    }
  } catch (error) {
    console.error('Error al procesar el módulo arrastrado:', error);
  }
};

// Actualizar altura del panel de módulos
const updatePanelHeight = () => {
  if (categoryPanel.value) {
    const height = categoryPanel.value.offsetHeight;
    categoryPanelHeight.value = Math.max(height, 300);
  }
};

// Observar cambios en el panel de la categoría
const observeCategoryPanel = () => {
  if (!categoryPanel.value) return;
  
  const observer = new MutationObserver(() => {
    nextTick(() => {
      updatePanelHeight();
    });
  });
  
  observer.observe(categoryPanel.value, {
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
  loadAvailableModules();
  
  if (props.category) {
    categoryData.value = {
      codigo: props.category.codigo || '',
      nombre: props.category.nombre || '',
      activo: props.category.activo !== false,
      modulos: props.category.modulos?.map(module => ({ ...module })) || []
    };
  } else if (props.modelValue) {
    categoryData.value = { 
      ...props.modelValue,
      activo: props.modelValue.activo ?? true
    };
  }
  
  nextTick(() => {
    updatePanelHeight();
    observeCategoryPanel();
  });
});

// Emitir cambios
watch(categoryData, (newVal) => {
  emit('update:modelValue', { ...newVal });
}, { deep: true });
</script>
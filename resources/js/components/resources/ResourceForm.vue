<template>
  <div class="bg-white rounded-lg shadow-md p-6">
    <h3 class="text-lg font-semibold mb-4">
      {{ isEditing ? 'Editar Recurso Maestro' : 'Crear Nuevo Recurso Maestro' }}
    </h3>

    <form @submit.prevent="handleSubmit" class="space-y-4">
      <!-- Código -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Código *
          <span v-if="errors.codigo" class="text-red-600 text-xs ml-2">{{ errors.codigo }}</span>
        </label>
        <input
          v-model="form.codigo"
          type="text"
          :class="['w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2',
                   errors.codigo ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500']"
          placeholder="Ej: CEM-001, HOR-001"
          required
          @input="clearError('codigo')"
        />
        <p class="text-xs text-gray-500 mt-1">Código único para identificar el recurso</p>
      </div>

      <!-- Nombre -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Nombre *
          <span v-if="errors.nombre" class="text-red-600 text-xs ml-2">{{ errors.nombre }}</span>
        </label>
        <input
          v-model="form.nombre"
          type="text"
          :class="['w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2',
                   errors.nombre ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500']"
          placeholder="Ej: Cemento tipo I, Albañil"
          required
          @input="clearError('nombre')"
        />
      </div>

      <!-- Tipo -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Tipo *
          <span v-if="errors.tipo" class="text-red-600 text-xs ml-2">{{ errors.tipo }}</span>
        </label>
        <select
          v-model="form.tipo"
          :class="['w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2',
                   errors.tipo ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500']"
          required
          @change="clearError('tipo')"
        >
          <option value="">Seleccione un tipo</option>
          <option v-for="tipo in tiposDisponibles" :key="tipo.value" :value="tipo.value">
            {{ tipo.label }}
          </option>
        </select>
      </div>

      <!-- Unidad -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Unidad *
          <span v-if="errors.unidad" class="text-red-600 text-xs ml-2">{{ errors.unidad }}</span>
        </label>
        <input
          v-model="form.unidad"
          type="text"
          :class="['w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2',
                   errors.unidad ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500']"
          placeholder="Ej: bolsa, hora, unidad, m³"
          required
          @input="clearError('unidad')"
        />
        <p class="text-xs text-gray-500 mt-1">Unidad de medida del recurso</p>
      </div>

      <!-- Precio de Referencia -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Precio de Referencia *
          <span v-if="errors.precio_referencia" class="text-red-600 text-xs ml-2">{{ errors.precio_referencia }}</span>
        </label>
        <input
          v-model.number="form.precio_referencia"
          type="number"
          step="0.01"
          min="0"
          :class="['w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2',
                   errors.precio_referencia ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500']"
          placeholder="0.00"
          required
          @input="clearError('precio_referencia')"
        />
        <p class="text-xs text-gray-500 mt-1">Precio unitario base del recurso</p>
      </div>

      <!-- Descripción -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
        <textarea
          v-model="form.descripcion"
          rows="3"
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Descripción adicional del recurso (opcional)"
        ></textarea>
      </div>

      <!-- Estado Activo -->
      <div class="flex items-center">
        <input
          v-model="form.activo"
          type="checkbox"
          id="activo"
          class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
        />
        <label for="activo" class="ml-2 block text-sm text-gray-700">
          Recurso activo
        </label>
      </div>

      <!-- Botones -->
      <div class="flex space-x-3 pt-2">
        <button
          type="submit"
          :disabled="loading"
          class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 flex items-center"
        >
          <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ isEditing ? 'Actualizar' : 'Crear' }}
        </button>
        
        <button
          type="button"
          @click="$emit('cancel')"
          class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500"
        >
          Cancelar
        </button>
      </div>
      <!-- En ResourceForm.vue, después del botón de cancelar -->
        <div v-if="isEditing" class="pt-2">
        <button
            type="button"
            @click="$emit('manage-regional-prices', resource)"
            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
        >
            Gestionar Precios Regionales
        </button>
        </div>
    </form>

    <div v-if="error" class="mt-4 p-3 bg-red-50 text-red-700 rounded-md text-sm">
      {{ error }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useResourcesStore } from '../../stores/resources';
import { useActiveVersionStore } from '../../stores/activeVersion'; // 👈 AÑADIR ESTA LÍNEA

const activeVersionStore = useActiveVersionStore(); // 👈 AÑADIR ESTA LÍNEA

const props = defineProps({
  resource: Object,
  loading: Boolean
});

const emit = defineEmits([
  'submit',
  'cancel',
  'manage-regional-prices'
]);


const resourcesStore = useResourcesStore();

// Form state
const form = ref({
  codigo: '',
  nombre: '',
  tipo: '',
  unidad: '',
  precio_referencia: 0,
  descripcion: '',
  activo: true
});

const errors = ref({});
const error = ref(null);
const isEditing = ref(false);

// Computed properties
const tiposDisponibles = computed(() => resourcesStore.tiposDisponibles);

// Watch for prop changes
watch(() => props.resource, (newResource) => {
  if (newResource) {
    form.value = { ...newResource };
    isEditing.value = true;
  } else {
    form.value = {
      codigo: '',
      nombre: '',
      tipo: '',
      unidad: '',
      precio_referencia: 0,
      descripcion: '',
      activo: true
    };
    isEditing.value = false;
  }
  errors.value = {};
  error.value = null;
}, { immediate: true });

// Validation methods
const validate = () => {
  const newErrors = {};
  
  if (!form.value.codigo.trim()) {
    newErrors.codigo = 'El código es requerido';
  }
  
  if (!form.value.nombre.trim()) {
    newErrors.nombre = 'El nombre es requerido';
  }
  
  if (!form.value.tipo) {
    newErrors.tipo = 'El tipo es requerido';
  }
  
  if (!form.value.unidad.trim()) {
    newErrors.unidad = 'La unidad es requerida';
  }
  
  if (form.value.precio_referencia <= 0) {
    newErrors.precio_referencia = 'El precio debe ser mayor que 0';
  }
  
  return newErrors;
};

const clearError = (field) => {
  if (errors.value[field]) {
    delete errors.value[field];
  }
};

const handleSubmit = () => {
  error.value = null;
  errors.value = validate();
  
  if (Object.keys(errors.value).length > 0) {
    // Scroll to first error
    const firstErrorField = Object.keys(errors.value)[0];
    document.querySelector(`[placeholder*="${firstErrorField}"]`)?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    return;
  }
  // 👇 VALIDAR QUE HAYA VERSIÓN ACTIVA
    if (!activeVersionStore.currentVersionId) {
        error.value = 'Debes tener una versión activa para crear recursos.';
        return;
    }

  // Prepare data for API
  const resourceData = {
    codigo: form.value.codigo.trim(),
    nombre: form.value.nombre.trim(),
    tipo: form.value.tipo,
    unidad: form.value.unidad.trim(),
    precio_referencia: parseFloat(form.value.precio_referencia),
    descripcion: form.value.descripcion?.trim() || null,
    activo: form.value.activo,
    version_id: activeVersionStore.currentVersionId // Necesitas importar el store
  };

  emit('submit', resourceData);
};
</script>
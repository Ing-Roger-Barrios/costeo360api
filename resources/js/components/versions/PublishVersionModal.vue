<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Publicar Nueva Versión</h3>
      </div>

      <!-- Formulario -->
      <form @submit.prevent="handleSubmit" class="px-6 py-4 space-y-4">
        <!-- Código de versión -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Código de Versión *
            <span v-if="errors.version" class="text-red-600 text-xs ml-2">{{ errors.version }}</span>
          </label>
          <input
            v-model="form.version"
            type="text"
            :class="['w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2',
                     errors.version ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500']"
            placeholder="Ej: v2.0, 2026-02, etc."
            required
            @input="clearError('version')"
          />
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
            placeholder="Ej: Precios Febrero 2026"
            required
            @input="clearError('nombre')"
          />
        </div>

        <!-- Descripción -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
          <textarea
            v-model="form.descripcion"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Descripción de los cambios en esta versión..."
          ></textarea>
        </div>

        <!-- Fecha de publicación (auto) -->
        <div class="bg-gray-50 p-3 rounded-md">
          <p class="text-sm text-gray-600">
            <strong>Fecha de publicación:</strong> {{ formatDate(new Date()) }}
          </p>
        </div>

        <!-- Botones -->
        <div class="flex space-x-3 pt-2">
          <button
            type="submit"
            :disabled="loading"
            class="flex-1 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 disabled:opacity-50 flex items-center justify-center"
          >
            <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Publicar Versión
          </button>
          
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500"
          >
            Cancelar
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  show: Boolean
});

const emit = defineEmits(['close', 'publish']);

const form = ref({
  version: '',
  nombre: '',
  descripcion: ''
});

const errors = ref({});
const loading = ref(false);

// Validación
const validate = () => {
  const newErrors = {};
  
  if (!form.value.version.trim()) {
    newErrors.version = 'El código de versión es requerido';
  }
  
  if (!form.value.nombre.trim()) {
    newErrors.nombre = 'El nombre es requerido';
  }
  
  return newErrors;
};

const clearError = (field) => {
  if (errors.value[field]) {
    delete errors.value[field];
  }
};

const formatDate = (date) => {
  return date.toLocaleDateString('es-BO', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

const handleSubmit = () => {
  errors.value = validate();
  
  if (Object.keys(errors.value).length > 0) {
    return;
  }

  loading.value = true;
  
  const versionData = {
    version: form.value.version.trim(),
    nombre: form.value.nombre.trim(),
    descripcion: form.value.descripcion?.trim() || null
  };

  emit('publish', versionData);
};
</script>
<template>
  <div class="bg-white rounded-lg shadow-md p-6">
    <h3 class="text-lg font-semibold mb-4">
      {{ isEditing ? 'Editar Versión' : 'Crear Nueva Versión' }}
    </h3>

    <form @submit.prevent="handleSubmit" class="space-y-4">
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
          placeholder="Ej: v1.0, 2026-01, etc."
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
          placeholder="Ej: Precios Enero 2026"
          required
          @input="clearError('nombre')"
        />
      </div>

      <!-- Fecha de publicación -->
      <div>
        <label hidden="true" class="block text-sm font-medium text-gray-700 mb-1">
          Fecha de Publicación *
          <span v-if="errors.fecha_publicacion" class="text-red-600 text-xs ml-2">{{ errors.fecha_publicacion }}</span>
        </label>
        <input hidden="true"
          v-model="form.fecha_publicacion"
          type="date"
          :class="['w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2',
                   errors.fecha_publicacion ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500']"
          required
          @input="clearError('fecha_publicacion')"
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

      <!-- Estado Activo -->
      <div class="flex items-center">
        <input
          v-model="form.activo"
          type="checkbox"
          id="activo"
          class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
        />
        <label for="activo" class="ml-2 block text-sm text-gray-700">
          Versión activa
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
    </form>

    <div v-if="error" class="mt-4 p-3 bg-red-50 text-red-700 rounded-md text-sm">
      {{ error }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
  version: Object,
  loading: Boolean
});

const emit = defineEmits(['submit', 'cancel']);

const form = ref({
  version: '',
  nombre: '',
  fecha_publicacion: '',
  descripcion: '',
  activo: true
});

const errors = ref({});
const error = ref(null);
const isEditing = ref(false);

// Inicializar formulario
watch(() => props.version, (newVersion) => {
  if (newVersion) {
    form.value = {
      version: newVersion.version || '',
      nombre: newVersion.nombre || '',
      fecha_publicacion: newVersion.fecha_publicacion ? newVersion.fecha_publicacion.split('T')[0] : '',
      descripcion: newVersion.descripcion || '',
      activo: Boolean(newVersion.activo)
    };
    isEditing.value = true;
  } else {
    form.value = {
      version: '',
      nombre: '',
      fecha_publicacion: new Date().toISOString().split('T')[0],
      descripcion: '',
      activo: false // 👈 Por defecto INACTIVA
    };
    isEditing.value = false;
  }
  errors.value = {};
  error.value = null;
}, { immediate: true });

// Validación
const validate = () => {
  const newErrors = {};
  
  if (!form.value.version.trim()) {
    newErrors.version = 'El código de versión es requerido';
  }
  
  if (!form.value.nombre.trim()) {
    newErrors.nombre = 'El nombre es requerido';
  }
  
  if (!form.value.fecha_publicacion) {
    newErrors.fecha_publicacion = 'La fecha es requerida';
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
    return;
  }

  const versionData = {
    version: form.value.version.trim(),
    nombre: form.value.nombre.trim(),
    fecha_publicacion: form.value.fecha_publicacion,
    descripcion: form.value.descripcion?.trim() || null,
    activo: form.value.activo
  };

  emit('submit', versionData);
};
</script>
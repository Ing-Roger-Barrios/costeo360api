<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
      <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Publicar Versión Actual</h3>
      </div>

      <form @submit.prevent="handleSubmit" class="px-6 py-4 space-y-4">
        <div class="bg-blue-50 p-4 rounded-md">
          <p class="text-sm text-blue-800">
            <strong>Versión actual:</strong> {{ currentVersion.version }} - {{ currentVersion.nombre }}
          </p>
          <p class="text-xs text-blue-600 mt-1">
            Esta acción publicará la versión en la que estás trabajando actualmente.
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de la versión publicada *</label>
          <input
            v-model="form.nombre"
            type="text"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Ej: Precios Oficiales Febrero 2026"
            required
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
          <textarea
            v-model="form.descripcion"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Descripción de esta versión publicada..."
          ></textarea>
        </div>

        <div class="flex items-center">
          <input
            v-model="form.make_active"
            type="checkbox"
            id="make_active"
            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
          />
          <label for="make_active" class="ml-2 block text-sm text-gray-700">
            Hacer esta versión activa
          </label>
        </div>

        <div class="flex space-x-3 pt-2">
          <button
            type="submit"
            :disabled="loading"
            class="flex-1 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 disabled:opacity-50"
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
  show: Boolean,
  currentVersion: Object
});

const emit = defineEmits(['close', 'publish']);

const form = ref({
  nombre: '',
  descripcion: '',
  make_active: true
});

const loading = ref(false);

const handleSubmit = () => {
  loading.value = true;
  
  const publishData = {
    version_id: props.currentVersion.id,
    nombre: form.value.nombre,
    descripcion: form.value.descripcion,
    make_active: form.value.make_active
  };

  emit('publish', publishData);
};
</script>
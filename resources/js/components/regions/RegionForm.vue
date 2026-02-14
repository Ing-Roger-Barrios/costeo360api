<template>
  <div class="bg-white rounded-lg shadow-md p-6">
    <h3 class="text-lg font-semibold mb-4">
      {{ isEditing ? 'Editar Región' : 'Crear Nueva Región' }}
    </h3>

    <form @submit.prevent="handleSubmit" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Código *</label>
        <input
          v-model="form.codigo"
          type="text"
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Ej: LP, SC, CB"
          required
        />
        <p class="text-xs text-gray-500 mt-1">Código único para la región (máx. 10 caracteres)</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
        <input
          v-model="form.nombre"
          type="text"
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Ej: La Paz, Santa Cruz"
          required
        />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">País</label>
        <input
          v-model="form.pais"
          type="text"
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Bolivia"
        />
      </div>

      <div class="flex items-center">
        <input
          v-model="form.activo"
          type="checkbox"
          id="activo"
          class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
        />
        <label for="activo" class="ml-2 block text-sm text-gray-700">
          Región activa
        </label>
      </div>

      <div class="flex space-x-3 pt-2">
        <button
          type="submit"
          :disabled="loading"
          class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
        >
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

    <div v-if="error" class="mt-4 text-red-600 text-sm">{{ error }}</div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  region: Object,
  loading: Boolean
});

const emit = defineEmits(['submit', 'cancel']);

const form = ref({
  codigo: '',
  nombre: '',
  pais: 'Bolivia',
  activo: true
});

const error = ref(null);
const isEditing = ref(false);

// Sincronizar formulario cuando cambie la región
watch(() => props.region, (newRegion) => {
  if (newRegion) {
    form.value = { ...newRegion };
    isEditing.value = true;
  } else {
    form.value = {
      codigo: '',
      nombre: '',
      pais: 'Bolivia',
      activo: true
    };
    isEditing.value = false;
  }
  error.value = null;
}, { immediate: true });

const handleSubmit = () => {
  error.value = null;
  
  // Validación básica
  if (!form.value.codigo.trim() || !form.value.nombre.trim()) {
    error.value = 'Código y nombre son requeridos';
    return;
  }

  emit('submit', form.value);
};
</script>
<template>
  <div class="bg-white rounded-lg shadow-md p-6">
    <h3 class="text-lg font-semibold mb-4">
      {{ isEditing ? 'Editar Categoría' : 'Crear Nueva Categoría' }}
    </h3>

    <CategoryBuilder 
      v-model="formData"
      :category="category"
    />

    <!-- Control de estado activo/inactivo -->
    <div class="mt-4 pt-4 border-t border-gray-200">
      <div class="flex items-center">
        <input
          v-model="formData.activo"
          type="checkbox"
          id="activo"
          class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
        />
        <label for="activo" class="ml-2 block text-sm text-gray-700">
          Categoría activa
        </label>
        <span class="ml-2 text-xs text-gray-500">
          {{ formData.activo ? 'Esta categoría está disponible para su uso' : 'Esta categoría está deshabilitada' }}
        </span>
      </div>
    </div>

    <div class="flex space-x-3 pt-6">
      <button
        type="button"
        @click="handleSubmit"
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

    <div v-if="error" class="mt-4 p-3 bg-red-50 text-red-700 rounded-md text-sm">
      {{ error }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import CategoryBuilder from './CategoryBuilder.vue';

const props = defineProps({
  category: {
    type: Object,
    default: () => null
  },
  loading: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['submit', 'cancel']);

const formData = ref({
  codigo: '',
  nombre: '',
  activo: true,
  modulos: []
});

const error = ref(null);
const isEditing = computed(() => !!props.category);

watch(() => props.category, (newCategory) => {
  if (newCategory) {
    formData.value = {
      codigo: newCategory.codigo || '',
      nombre: newCategory.nombre || '',
      activo: Boolean(newCategory.activo),
      modulos: newCategory.modulos?.map(module => ({ ...module })) || []
    };
  } else {
    formData.value = {
      codigo: '',
      nombre: '',
      activo: true,
      modulos: []
    };
  }
  error.value = null;
}, { immediate: true });

const handleSubmit = () => {
  error.value = null;
  
  if (!formData.value.codigo.trim()) {
    error.value = 'El código es requerido';
    return;
  }
  
  if (!formData.value.nombre.trim()) {
    error.value = 'El nombre es requerido';
    return;
  }
  
  if (formData.value.modulos.length === 0) {
    error.value = 'La categoría debe tener al menos un módulo';
    return;
  }

  const categoryData = {
    codigo: formData.value.codigo.trim(),
    nombre: formData.value.nombre.trim(),
    activo: formData.value.activo,
    modulos: formData.value.modulos.map(module => ({
      modulo_id: module.id
    }))
  };

  emit('submit', categoryData);
};
</script>
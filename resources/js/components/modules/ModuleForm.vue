<template>
  <div class="bg-white rounded-lg shadow-md p-6">
    <h3 class="text-lg font-semibold mb-4">
      {{ isEditing ? 'Editar Módulo' : 'Crear Nuevo Módulo' }}
    </h3>

    <ModuleBuilder 
      v-model="formData"
      :module="module"
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
          Módulo activo
        </label>
        <span class="ml-2 text-xs text-gray-500">
          {{ formData.activo ? 'Este módulo está disponible para su uso' : 'Este módulo está deshabilitado' }}
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
import ModuleBuilder from './ModuleBuilder.vue';

const props = defineProps({
  module: {
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
  items: []
});

const originalCodigo = ref(null); // ✅ CLAVE
const error = ref(null);
const isEditing = computed(() => !!props.module);

watch(
  () => props.module,
  (newModule) => {
    if (newModule) {
      formData.value = {
        codigo: newModule.codigo || '',
        nombre: newModule.nombre || '',
        activo: Boolean(newModule.activo),
        items: newModule.items?.map(item => ({ ...item })) || []
      };

      originalCodigo.value = newModule.codigo || '';
    } else {
      formData.value = {
        codigo: '',
        nombre: '',
        activo: true,
        items: []
      };

      originalCodigo.value = null;
    }

    error.value = null;
  },
  { immediate: true }
);

const handleSubmit = () => {
  error.value = null;

  if (!formData.value.nombre.trim()) {
    error.value = 'El nombre es requerido';
    return;
  }

  if (formData.value.items.length === 0) {
    error.value = 'El módulo debe tener al menos un item';
    return;
  }

  /*const moduleData = {
    nombre: formData.value.nombre.trim(),
    activo: formData.value.activo,
    items: formData.value.items.map(item => ({
      item_id: item.id,
      orden: index,
      rendimiento: item.pivot_rendimiento || 0 // 👈 enviar como 'rendimiento'
    }))
  };*/
    // 👇 CÓDIGO SEGURO - SIN VARIABLES SUELTAS
  /*const itemsConOrden = [];
  for (let i = 0; i < formData.value.items.length; i++) {
    const item = formData.value.items[i];
    itemsConOrden.push({
      item_id: item.id,
      orden: i,
      rendimiento: item.pivot_rendimiento || 0
    });
  }

  const moduleData = {
    nombre: formData.value.nombre.trim(),
    activo: formData.value.activo,
    items: itemsConOrden
  };*/
    // 👇 MAP() SEGURO
  const moduleData = {
    nombre: formData.value.nombre.trim(),
    activo: formData.value.activo,
    items: formData.value.items.map((item, idx) => ({ // 👈 usar 'idx' en lugar de 'index'
      item_id: item.id,
      orden: idx,
      rendimiento: item.pivot_rendimiento || 0
    }))
  };

  const codigoActual = formData.value.codigo.trim();
    // solo en edición
    if (isEditing.value) {
    moduleData.id = props.module.id;
    }
  if (!isEditing.value || codigoActual !== originalCodigo.value) {
    if (!codigoActual) {
      error.value = 'El código es requerido';
      return;
    }
    moduleData.codigo = codigoActual;
  }

  emit('submit', moduleData);
};
</script>

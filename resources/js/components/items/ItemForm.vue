<template>
  <div class="bg-white rounded-lg shadow-md p-6">
    <h3 class="text-lg font-semibold mb-4">
      {{ isEditing ? 'Editar Item' : 'Crear Nuevo Item' }}
    </h3>

    <ItemBuilder 
      v-model="formData"
      :item="item"
    />

    <!-- 👇 CONTROL DE ESTADO ACTIVO/INACTIVO - USAR v-model DIRECTAMENTE -->
    <div class="mt-4 pt-4 border-t border-gray-200">
      <div class="flex items-center">
        <input
          v-model="formData.activo"
          type="checkbox"
          id="activo"
          class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
        />
        <label for="activo" class="ml-2 block text-sm text-gray-700">
          Item activo
        </label>
        <span class="ml-2 text-xs text-gray-500">
          {{ formData.activo ? 'Este item está disponible para su uso' : 'Este item está deshabilitado' }}
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
import ItemBuilder from './ItemBuilder.vue';

const props = defineProps({
  item: {
    type: Object,
    default: () => null
  },
  loading: {
    type: Boolean,
    default: false
  }
});
// 👇 NUEVO MÉTODO PARA TOGGLER EL ESTADO
 
const emit = defineEmits(['submit', 'cancel']);

const formData = ref({
  codigo: '',
  descripcion: '',
  unidad: '',
  notas: '',
  activo: true, // 👈 Por defecto activo
  recursos: []
});

const error = ref(null);
const isEditing = computed(() => !!props.item);

// 👇 MEJORAR EL WATCHER
// 👇 ACTUALIZAR PROPIEDADES INDIVIDUALMENTE
const updateFormDataFromItem = (newItem) => {
  if (!newItem) {
    // Reset para nuevo item
    formData.value.codigo = '';
    formData.value.descripcion = '';
    formData.value.unidad = '';
    formData.value.notas = '';
    formData.value.activo = true;
    formData.value.recursos = [];
    return;
  }
  
  // Actualizar cada propiedad individualmente
  formData.value.codigo = newItem.codigo || '';
  formData.value.descripcion = newItem.descripcion || '';
  formData.value.unidad = newItem.unidad || '';
  formData.value.notas = newItem.notas || '';
  formData.value.activo =
  newItem.activo === true ||
  newItem.activo === 1 ||
  newItem.activo === '1';
  
  // Actualizar recursos
  if (newItem.recursos) {
    formData.value.recursos = newItem.recursos.map(r => ({
      id: r.id,
      codigo: r.codigo,
      nombre: r.nombre,
      tipo: r.tipo,
      unidad: r.unidad,
      precio_referencia: r.precio_referencia,
      rendimiento: r.pivot?.rendimiento || r.rendimiento || 1.0
    }));
  } else {
    formData.value.recursos = [];
  }
};

// 👇 WATCHER SIMPLIFICADO
// 👇 WATCHER CORREGIDO - SIN deep: true
watch(() => props.item, (newItem) => {
  if (newItem) {
    // Actualizar directamente las propiedades
    formData.value.codigo = newItem.codigo || '';
    formData.value.descripcion = newItem.descripcion || '';
    formData.value.unidad = newItem.unidad || '';
    formData.value.notas = newItem.notas || '';
    formData.value.activo = Boolean(newItem.activo);
    
    // Actualizar recursos
    formData.value.recursos = newItem.recursos?.map(r => ({
      id: r.id,
      codigo: r.codigo,
      nombre: r.nombre,
      tipo: r.tipo,
      unidad: r.unidad,
      precio_referencia: r.precio_referencia,
      rendimiento: r.pivot?.rendimiento || r.rendimiento || 1.0
    })) || [];
  } else {
    // Reset para nuevo item
    formData.value.codigo = '';
    formData.value.descripcion = '';
    formData.value.unidad = '';
    formData.value.notas = '';
    formData.value.activo = true;
    formData.value.recursos = [];
  }
  error.value = null;
}, { immediate: true });


const handleSubmit = () => {
  error.value = null;
  
  if (!formData.value.codigo.trim()) {
    error.value = 'El código es requerido';
    return;
  }
  
  if (!formData.value.descripcion.trim()) {
    error.value = 'La descripción es requerida';
    return;
  }
  
  if (!formData.value.unidad.trim()) {
    error.value = 'La unidad es requerida';
    return;
  }
  
  if (formData.value.recursos.length === 0) {
    error.value = 'El item debe tener al menos un recurso';
    return;
  }

  const itemData = {
    codigo: formData.value.codigo.trim(),
    descripcion: formData.value.descripcion.trim(),
    unidad: formData.value.unidad.trim(),
    //rendimiento: formData.value.rendimiento.trim(),
    notas: formData.value.notas?.trim() || null,
    activo: formData.value.activo, // 👈 Incluir el estado
    recursos: formData.value.recursos.map(r => ({
      recurso_id: r.id,
      rendimiento: r.rendimiento
    }))
  };

  emit('submit', itemData);
};
</script>
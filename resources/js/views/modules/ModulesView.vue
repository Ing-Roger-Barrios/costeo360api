<template>
  <div class="max-w-6xl mx-auto">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Gestión de Módulos</h1>
        <p class="text-gray-600">Crea y gestiona módulos compuestos por items</p>
      </div>
      
      <button
        @click="showForm = true; modulesStore.clearCurrentModule()"
        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center space-x-2"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        <span>Nuevo Módulo</span>
      </button>
    </div>

    <div v-if="showForm" class="mb-6">
      <ModuleForm
        :module="modulesStore.currentModule"
        :loading="modulesStore.loading"
        @submit="handleSaveModule"
        @cancel="showForm = false"
      />
    </div>

    <ModuleList
      :loading="modulesStore.loading"
      @edit="handleEditModule"
      @delete="handleDeleteModule"
    />

    <Alert
      v-if="alert.message"
      :type="alert.type"
      :message="alert.message"
      @close="alert.message = ''"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useModulesStore } from '../../stores/modules';
import ModuleForm from '../../components/modules/ModuleForm.vue';
import ModuleList from '../../components/modules/ModuleList.vue';
import Alert from '../../components/ui/Alert.vue';
import Swal from 'sweetalert2';

const modulesStore = useModulesStore();
const showForm = ref(false);
const alert = ref({ message: '', type: 'success' });

onMounted(async () => {
  try {
    await modulesStore.fetchModules();
  } catch (error) {
    alert.value = { 
      message: 'Error al cargar los módulos: ' + (modulesStore.error || 'Error desconocido'), 
      type: 'error' 
    };
  }
});

const handleSaveModule = async (moduleData) => {
  try {
    if (modulesStore.currentModule) {
      await modulesStore.updateModule(modulesStore.currentModule.id, moduleData);
      alert.value = { message: 'Módulo actualizado exitosamente', type: 'success' };
    } else {
      await modulesStore.createModule(moduleData);
      alert.value = { message: 'Módulo creado exitosamente', type: 'success' };
    }
    showForm.value = false;
  } catch (error) {
    alert.value = { 
      message: modulesStore.error || 'Error al guardar el módulo', 
      type: 'error' 
    };
  }
};

const handleEditModule = (module) => {
  modulesStore.setCurrentModule(module);
  showForm.value = true;
};

const handleDeleteModule = async (module) => {
  const result = await Swal.fire({
    title: '¿Eliminar módulo?',
    html: `¿Estás seguro de eliminar el módulo "<strong>${module.codigo} - ${module.nombre}</strong>"?<br><br>Esta acción no se puede deshacer.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar',
    reverseButtons: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6b7280'
  });

  if (!result.isConfirmed) return;

  try {
    await modulesStore.deleteModule(module.id);
    await Swal.fire({
      title: '¡Módulo eliminado!',
      text: 'El módulo ha sido eliminado exitosamente.',
      icon: 'success',
      timer: 2000,
      showConfirmButton: false
    });
  } catch (error) {
    Swal.fire({
      title: 'Error',
      text: error.response?.data?.message || 'Error al eliminar el módulo',
      icon: 'error'
    });
  }
};
</script>
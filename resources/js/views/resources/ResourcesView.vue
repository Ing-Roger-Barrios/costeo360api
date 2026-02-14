<template>
  <div class="max-w-6xl mx-auto">
    <!-- Header con botón de acción -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Gestión de Recursos Maestros</h1>
        <p class="text-gray-600">Administra materiales, mano de obra y equipos</p>
      </div>
      
      <button
        @click="showForm = true; resourcesStore.clearCurrentResource()"
        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center space-x-2"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        <span>Nuevo Recurso</span>
      </button>
    </div>

    <!-- Formulario modal -->
    <div v-if="showForm" class="mb-6">
      <ResourceForm
        :resource="resourcesStore.currentResource"
        :loading="resourcesStore.loading"
        @submit="handleSaveResource"
        @cancel="showForm = false"
        @manage-regional-prices="handleManageRegionalPrices"
      />

    </div>

    <!-- Lista de recursos -->
    <ResourceList
      :loading="resourcesStore.loading"
      @edit="handleEditResource"
      @delete="handleDeleteResource"
    />

    <!-- Alertas -->
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
import { useResourcesStore } from '../../stores/resources';
import ResourceForm from '../../components/resources/ResourceForm.vue';
import ResourceList from '../../components/resources/ResourceList.vue';
import Alert from '../../components/ui/Alert.vue';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import { useActiveVersionStore } from '../../stores/activeVersion';

const activeVersionStore = useActiveVersionStore();

const router = useRouter();


const resourcesStore = useResourcesStore();
const showForm = ref(false);
const alert = ref({ message: '', type: 'success' });

// Cargar recursos al montar
onMounted(async () => {
  try {
    await resourcesStore.fetchResources();
  } catch (error) {
    alert.value = { 
      message: 'Error al cargar los recursos: ' + (resourcesStore.error || 'Error desconocido'), 
      type: 'error' 
    };
  }
});

// Antes de crear/actualizar un recurso
const handleSaveResource = async (resourceData) => {
  try {
    // Verificar que haya versión activa
    const activeVersion = activeVersionStore.requireActiveVersion();
    
    // Añadir el ID de versión a los datos
    const dataWithVersion = {
      ...resourceData,
      version_id: activeVersion.id // 👈 Esto asegura que sepas en qué versión estás trabajando
    };
    
    // Continuar con la operación...
    await resourcesStore.createResource(dataWithVersion);
    
  } catch (error) {
    if (error.message === 'No hay una versión activa seleccionada') {
      Swal.fire({
        title: 'Versión no seleccionada',
        text: 'Debes tener una versión activa para realizar esta operación.',
        icon: 'warning',
        confirmButtonText: 'Ir a versiones'
      }).then((result) => {
        if (result.isConfirmed) {
          router.push('/versions');
        }
      });
    } else {
      // Manejar otros errores
      console.error(error);
    }
  }
};

// Editar recurso
const handleEditResource = (resource) => {
  resourcesStore.setCurrentResource(resource);
  showForm.value = true;
};

// Nuevo método
const handleManageRegionalPrices = (resource) => {
  router.push({
    name: 'resource-regional-prices',
    params: { resourceId: resource.id },
    query: { codigo: resource.codigo, nombre: resource.nombre }
  });
};

// Eliminar recurso
const handleDeleteResource = async (resource) => {
  const result = await Swal.fire({
    title: '¿Eliminar recurso?',
    html: `¿Estás seguro de eliminar el recurso "<strong>${resource.codigo} - ${resource.nombre}</strong>"?<br><br>Esta acción no se puede deshacer.`,
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
    await resourcesStore.deleteResource(resource.id);
    await Swal.fire({
      title: '¡Recurso eliminado!',
      text: 'El recurso ha sido eliminado exitosamente.',
      icon: 'success',
      timer: 2000,
      showConfirmButton: false
    });
  } catch (error) {
    Swal.fire({
      title: 'Error',
      text: error.response?.data?.message || 'Error al eliminar el recurso',
      icon: 'error'
    });
  }
};
</script>
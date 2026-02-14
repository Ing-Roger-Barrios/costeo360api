<template>
  <div class="max-w-6xl mx-auto">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Gestión de Versiones</h1>
        <p class="text-gray-600">Administra diferentes versiones de tu catálogo de precios</p>
      </div>
      
      <div class="flex space-x-2">
        <!-- Botón para crear versión vacía -->
        <button
          @click="showForm = true; versionsStore.clearCurrentVersion()"
          class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center space-x-2"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          <span>Nueva Versión</span>
        </button>
        
        <!-- Botón para publicar versión activa -->
        <button
          v-if="activeVersionStore.currentVersion"
          @click="handlePublishActiveVersion"
          class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 flex items-center space-x-2"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <span>Publicar Versión Activa</span>
        </button>
      </div>
    </div>

    <!-- Formulario de versión -->
    <div v-if="showForm" class="mb-6">
      <VersionForm
        :version="versionsStore.currentVersion"
        :loading="versionsStore.loading"
        @submit="handleSaveVersion"
        @cancel="showForm = false"
      />
    </div>

    <!-- Lista de versiones -->
    <VersionList
      :loading="versionsStore.loading"
      @edit="handleEditVersion"
      @delete="handleDeleteVersion"
      @activate="handleActivateVersion"
      @publish="handlePublishVersion"  
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
import { useVersionsStore } from '../../stores/versions';
import { useActiveVersionStore } from '../../stores/activeVersion';
import VersionForm from '../../components/versions/VersionForm.vue';
import VersionList from '../../components/versions/VersionList.vue';
import Alert from '../../components/ui/Alert.vue';
import Swal from 'sweetalert2';

// Stores
const versionsStore = useVersionsStore();
const activeVersionStore = useActiveVersionStore();

// Estados
const showForm = ref(false);
const alert = ref({ message: '', type: 'success' });

// Métodos
onMounted(async () => {
  try {
    await versionsStore.fetchVersions();
    await activeVersionStore.fetchAllVersionsInfo();
  } catch (error) {
    alert.value = { 
      message: 'Error al cargar las versiones', 
      type: 'error' 
    };
  }
});

const handleSaveVersion = async (versionData) => {
  try {
    if (versionsStore.currentVersion) {
      await versionsStore.updateVersion(versionsStore.currentVersion.id, versionData);
      alert.value = { message: 'Versión actualizada exitosamente', type: 'success' };
    } else {
      await versionsStore.createVersion(versionData);
      alert.value = { message: 'Versión creada exitosamente', type: 'success' };
    }
    showForm.value = false;
    await activeVersionStore.fetchAllVersionsInfo(); // Actualizar versión activa
  } catch (error) {
    alert.value = { 
      message: versionsStore.error || 'Error al guardar la versión', 
      type: 'error' 
    };
  }
};

const handleEditVersion = (version) => {
  versionsStore.setCurrentVersion(version);
  showForm.value = true;
};

const handleDeleteVersion = async (version) => {
  const result = await Swal.fire({
    title: '¿Eliminar versión?',
    html: `¿Estás seguro de eliminar la versión "<strong>${version.version} - ${version.nombre}</strong>"?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar',
    reverseButtons: true,
    confirmButtonColor: '#d33'
  });

  if (!result.isConfirmed) return;

  try {
    await versionsStore.deleteVersion(version.id);
    await activeVersionStore.fetchAllVersionsInfo(); // Actualizar versión activa
    alert.value = { message: 'Versión eliminada exitosamente', type: 'success' };
  } catch (error) {
    Swal.fire({
      title: 'Error',
      text: error.response?.data?.message || 'Error al eliminar la versión',
      icon: 'error'
    });
  }
};

const handleActivateVersion = async (version) => {
  const result = await Swal.fire({
    title: '¿Activar versión?',
    html: `¿Estás seguro de activar la versión "<strong>${version.version} - ${version.nombre}</strong>"?`,
    icon: 'info',
    showCancelButton: true,
    confirmButtonText: 'Sí, activar',
    cancelButtonText: 'Cancelar',
    reverseButtons: true
  });

  if (!result.isConfirmed) return;

  try {
    await axios.post(`/api/v1/versions/${version.id}/activate`);
    await versionsStore.fetchVersions();
    await activeVersionStore.fetchAllVersionsInfo(); // Actualizar versión activa
    alert.value = { message: 'Versión activada exitosamente', type: 'success' };
  } catch (error) {
    Swal.fire({
      title: 'Error',
      text: error.response?.data?.message || 'Error al activar la versión',
      icon: 'error'
    });
  }
};

// 👇 MÉTODO PARA PUBLICAR VERSIÓN ACTIVA
const handlePublishActiveVersion = async () => {
  if (!activeVersionStore.currentVersion) {
    return;
  }

  const result = await Swal.fire({
    title: '¿Publicar versión activa?',
    html: `¿Estás seguro de publicar la versión "<strong>${activeVersionStore.currentVersion.version} - ${activeVersionStore.currentVersion.nombre}</strong>"?<br><br>Esta acción hará que esta versión esté disponible para la aplicación de escritorio.`,
    icon: 'info',
    showCancelButton: true,
    confirmButtonText: 'Sí, publicar',
    cancelButtonText: 'Cancelar',
    reverseButtons: true,
    confirmButtonColor: '#10b981'
  });

  if (!result.isConfirmed) {
    return;
  }

  try {
    await axios.post('/api/v1/versions/publish-active');
    await versionsStore.fetchVersions();
    await activeVersionStore.fetchAllVersionsInfo();
    
    await Swal.fire({
      title: '¡Versión publicada!',
      text: 'La versión ha sido publicada exitosamente.',
      icon: 'success',
      timer: 2000,
      showConfirmButton: false
    });
    
  } catch (error) {
    Swal.fire({
      title: 'Error',
      text: error.response?.data?.message || 'Error al publicar la versión',
      icon: 'error'
    });
  }
};
// 👇 AÑADIR EL MÉTODO PARA PUBLICAR DESDE LA LISTA
const handlePublishVersion = async (version) => {
  const result = await Swal.fire({
    title: '¿Publicar versión?',
    html: `¿Estás seguro de publicar la versión "<strong>${version.version} - ${version.nombre}</strong>"?<br><br>Esta acción hará que esta versión esté disponible para la aplicación de escritorio.`,
    icon: 'info',
    showCancelButton: true,
    confirmButtonText: 'Sí, publicar',
    cancelButtonText: 'Cancelar',
    reverseButtons: true,
    confirmButtonColor: '#3b82f6'
  });

  if (!result.isConfirmed) return;

  try {
    // Usar el mismo endpoint que antes
    await axios.post('/api/v1/versions/publish-active');
    
    await versionsStore.fetchVersions();
    await activeVersionStore.fetchAllVersionsInfo();
    
    await Swal.fire({
      title: '¡Versión publicada!',
      text: 'La versión ha sido publicada exitosamente.',
      icon: 'success',
      timer: 2000,
      showConfirmButton: false
    });
    
  } catch (error) {
    Swal.fire({
      title: 'Error',
      text: error.response?.data?.message || 'Error al publicar la versión',
      icon: 'error'
    });
  }
};
</script>
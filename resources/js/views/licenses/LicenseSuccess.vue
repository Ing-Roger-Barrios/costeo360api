
<template>
  <div class="max-w-2xl mx-auto">
    <div class="text-center mb-8">
      <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
      </div>
      
      <h1 class="text-2xl font-bold text-gray-800 mb-2">¡Pago Completado!</h1>
      <p class="text-gray-600">Tu licencia de Costeo360 está ahora activa y lista para usar.</p>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Detalles de tu licencia</h3>
      <div class="space-y-3 text-left">
        <div class="flex justify-between">
          <span class="text-gray-600">Tipo de licencia:</span>
          <span class="font-medium">{{ getLicenseTypeLabel(license?.type) }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-gray-600">Fecha de inicio:</span>
          <span class="font-medium">{{ formatDate(license?.start_date) }}</span>
        </div>
        <div v-if="license?.end_date" class="flex justify-between">
          <span class="text-gray-600">Fecha de vencimiento:</span>
          <span class="font-medium">{{ formatDate(license?.end_date) }}</span>
        </div>
        <div v-else class="flex justify-between">
          <span class="text-gray-600">Duración:</span>
          <span class="font-medium">Vitalicia</span>
        </div>
        <div class="flex justify-between">
          <span class="text-gray-600">Monto pagado:</span>
          <span class="font-medium text-primary">
            Bs {{ parseFloat(license?.amount).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
          </span>
        </div>
      </div>
    </div>

    <div class="flex flex-col sm:flex-row gap-4 justify-center">
      <button 
        @click="downloadApp"
        class="bg-primary text-white px-6 py-3 rounded-md font-medium hover:bg-primary-medium transition-colors flex items-center justify-center"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
        </svg>
        Descargar Costeo360.exe
      </button>
      
      <button 
        @click="goToLicenses"
        class="border border-primary text-primary px-6 py-3 rounded-md font-medium hover:bg-primary hover:text-white transition-colors"
      >
        Ver Mis Licencias
      </button>
    </div>

    <div class="mt-8 text-gray-500 text-sm">
      <p>Recibirás un correo electrónico con los detalles de tu compra.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const license = ref(null);

const getLicenseTypeLabel = (type) => {
  const labels = {
    'monthly': 'Mensual',
    'yearly': 'Anual',
    'lifetime': 'Vitalicia'
  };
  return labels[type] || type;
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('es-BO', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  });
};

const downloadApp = () => {
  // Redirigir a la ruta de descarga de Laravel
  window.location.href = '/downloads/costeo360.exe';
};

const goToLicenses = () => {
  router.push('/licenses');
};

const fetchLicense = async () => {
  try {
    // Obtener la licencia más reciente
    const response = await axios.get('/api/v1/licenses/my');
    const licenses = response.data.licenses || [];
    if (licenses.length > 0) {
      license.value = licenses[0]; // La más reciente
    }
  } catch (error) {
    console.error('Error fetching license:', error);
  }
};

onMounted(() => {
  fetchLicense();
});
</script>

<style scoped>
.bg-primary { background-color: #0C3C61; }
.text-primary { color: #0C3C61; }
</style>
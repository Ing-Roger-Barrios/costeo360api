<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Mis Licencias</h1>
        <p class="text-gray-600 mt-1">Administra tus licencias activas</p>
      </div>
      <button 
        @click="goToPurchase"
        class="bg-primary text-white px-4 py-2 rounded-md hover:bg-primary-medium transition-colors"
      >
        Comprar Nueva Licencia
      </button>
    </div>

    <div v-if="loading" class="text-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto"></div>
      <p class="mt-2 text-gray-600">Cargando licencias...</p>
    </div>

    <div v-else-if="licenses.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
      <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
      </div>
      <h3 class="text-xl font-semibold text-gray-900 mb-2">No tienes licencias</h3>
      <p class="text-gray-600 mb-6">
        Compra tu primera licencia para empezar a usar Costeo360
      </p>
      <button 
        @click="goToPurchase"
        class="bg-primary text-white px-6 py-2 rounded-md hover:bg-primary-medium transition-colors"
      >
        Comprar Licencia Ahora
      </button>
    </div>

    <div v-else class="space-y-4">
      <div 
        v-for="license in licenses" 
        :key="license.id"
        class="bg-white rounded-lg shadow p-6"
      >
        <div class="flex flex-col md:flex-row md:items-center justify-between">
          <div class="mb-4 md:mb-0">
            <h3 class="text-lg font-semibold text-gray-900">
              Licencia {{ getLicenseTypeLabel(license.type) }}
            </h3>
            <p class="text-gray-600 text-sm">
              ID: {{ license.license_key.substring(0, 8) }}...
            </p>
          </div>
          
          <div class="flex flex-col md:items-end space-y-2">
            <span 
              :class="[
                'px-3 py-1 rounded-full text-sm font-medium',
                license.is_valid ? 'bg-green-100 text-green-800' : 
                !license.is_paid ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'
              ]"
            >
              {{ license.is_valid ? 'Activa' : !license.is_paid ? 'Pendiente' : 'Expirada' }}
            </span>
            
            <div class="text-right">
              <p class="font-bold text-primary">
                Bs {{ parseFloat(license.amount).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
              </p>
              <p v-if="license.end_date" class="text-sm text-gray-600">
                Vence: {{ formatDate(license.end_date) }}
                <span v-if="license.is_valid" class="ml-2">
                  ({{ getDaysRemaining(license) }} días restantes)
                </span>
              </p>
              <p v-else class="text-sm text-gray-600">Vitalicia</p>
            </div>
          </div>
        </div>
        
        <div v-if="license.payments && license.payments.length > 0" class="mt-4 pt-4 border-t border-gray-200">
          <h4 class="text-sm font-medium text-gray-700 mb-2">Pagos realizados:</h4>
          <div class="space-y-2">
            <div 
              v-for="payment in license.payments" 
              :key="payment.id"
              class="flex justify-between text-sm"
            >
              <span>ID: {{ payment.payment_id.substring(0, 12) }}...</span>
              <span :class="getPaymentStatusClass(payment.status)">
                {{ payment.status.charAt(0).toUpperCase() + payment.status.slice(1) }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const licenses = ref([]);
const loading = ref(false);

const goToPurchase = () => {
  router.push('/licenses/purchase');
};

const getLicenseTypeLabel = (type) => {
  const labels = {
    'monthly': 'Mensual',
    'yearly': 'Anual',
    'lifetime': 'Vitalicia'
  };
  return labels[type] || type;
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('es-BO', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  });
};

const getDaysRemaining = (license) => {
  if (!license.end_date) return 'Vitalicia';
  
  const now = new Date();
  const endDate = new Date(license.end_date);
  
  if (endDate < now) return 0;
  
  const diffTime = endDate - now;
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  return diffDays;
};

const getPaymentStatusClass = (status) => {
  switch (status) {
    case 'completed':
      return 'text-green-600';
    case 'failed':
      return 'text-red-600';
    case 'pending':
      return 'text-yellow-600';
    default:
      return 'text-gray-600';
  }
};

const fetchLicenses = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/v1/licenses/my');
    licenses.value = response.data.licenses || [];
  } catch (error) {
    console.error('Error fetching licenses:', error);
    // Puedes mostrar un mensaje de error si lo deseas
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchLicenses();
});
</script>

<style scoped>
.bg-primary { background-color: #0C3C61; }
.text-primary { color: #0C3C61; }
</style>
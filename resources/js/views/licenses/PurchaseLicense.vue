<template>
  <div>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-800 mb-2">Elige tu Plan</h1>
      <p class="text-gray-600">Selecciona el plan que mejor se adapte a tus necesidades</p>
    </div>

    <div v-if="existingLicense" class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-8 rounded">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <p class="text-sm text-yellow-700">
            Ya tienes una licencia activa: <strong>{{ getLicenseTypeLabel(existingLicense.type) }}</strong>
            <span v-if="existingLicense.end_date">
              (Vence en {{ getDaysRemaining(existingLicense) }} días)
            </span>
          </p>
        </div>
      </div>
    </div>

    <div class="grid md:grid-cols-3 gap-6">
      <div 
        v-for="plan in plans" 
        :key="plan.type"
        class="bg-white rounded-lg shadow p-6 border-2 hover:border-primary transition-colors cursor-pointer"
        :class="selectedPlan === plan.type ? 'border-primary' : 'border-gray-200'"
        @click="selectPlan(plan.type)"
      >
        <div class="text-center">
          <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ plan.name }}</h3>
          <p class="text-gray-600 mb-4 text-sm">{{ plan.description }}</p>
          
          <div class="mb-4">
            <span class="text-2xl font-bold text-primary">
              Bs {{ parseFloat(plan.price).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
            </span>
            <span v-if="plan.type !== 'lifetime'" class="text-gray-500 text-sm ml-1">/mes</span>
          </div>
          
          <button 
            :class="[
              'w-full py-2 px-4 rounded-md font-medium transition-colors',
              selectedPlan === plan.type 
                ? 'bg-primary text-white' 
                : 'border border-primary text-primary hover:bg-primary hover:text-white'
            ]"
          >
            {{ selectedPlan === plan.type ? 'Seleccionado' : 'Seleccionar' }}
          </button>
        </div>
      </div>
    </div>

    <div class="mt-8 flex justify-center space-x-4">
      <button 
        @click="goBack"
        class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
      >
        ← Volver
      </button>
      
      <button 
        @click="proceedToPayment"
        :disabled="!selectedPlan"
        :class="[
          'px-6 py-2 rounded-md font-medium transition-colors',
          selectedPlan 
            ? 'bg-primary text-white hover:bg-primary-medium' 
            : 'bg-gray-300 text-gray-500 cursor-not-allowed'
        ]"
      >
        Continuar al Pago
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const selectedPlan = ref(null);
const existingLicense = ref(null);

const plans = [
  {
    type: 'monthly',
    name: 'Mensual',
    price: 50.00,
    description: 'Acceso por 30 días'
  },
  {
    type: 'yearly',
    name: 'Anual',
    price: 500.00,
    description: 'Acceso por 365 días (Ahorra 17%)'
  },
  {
    type: 'lifetime',
    name: 'Vitalicia',
    price: 1500.00,
    description: 'Acceso de por vida'
  }
];

const getLicenseTypeLabel = (type) => {
  const labels = {
    'monthly': 'Mensual',
    'yearly': 'Anual',
    'lifetime': 'Vitalicia'
  };
  return labels[type] || type;
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

const selectPlan = (planType) => {
  selectedPlan.value = planType;
};

const goBack = () => {
  router.push('/licenses');
};

const proceedToPayment = () => {
  if (selectedPlan.value) {
    router.push(`/licenses/${selectedPlan.value}/payment`);
  }
};

const checkExistingLicense = async () => {
  try {
    const response = await axios.get('/api/v1/licenses/active');
    existingLicense.value = response.data.license || null;
  } catch (error) {
    console.error('Error checking existing license:', error);
  }
};

onMounted(() => {
  checkExistingLicense();
});
</script>

<style scoped>
.bg-primary { background-color: #0C3C61; }
.text-primary { color: #0C3C61; }
</style>
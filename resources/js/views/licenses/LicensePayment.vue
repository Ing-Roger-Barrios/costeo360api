<template>
  <div class="max-w-2xl mx-auto">
    <div class="text-center mb-8">
      <h1 class="text-2xl font-bold text-gray-800 mb-2">Confirmar Pago</h1>
      <p class="text-gray-600">Completa tu compra para activar tu licencia</p>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <div class="flex justify-between items-center mb-4 pb-4 border-b">
        <h3 class="text-lg font-semibold text-gray-900">Resumen del pedido</h3>
      </div>
      
      <div class="space-y-3">
        <div class="flex justify-between">
          <span class="text-gray-600">Plan:</span>
          <span class="font-medium">{{ getLicenseTypeLabel(planType) }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-gray-600">Duración:</span>
          <span class="font-medium">
            {{ planType === 'lifetime' ? 'Vitalicia' : 
               planType === 'yearly' ? '12 meses' : '1 mes' }}
          </span>
        </div>
        <div class="flex justify-between text-lg font-bold text-primary pt-4 border-t">
          <span>Total:</span>
          <span>Bs {{ parseFloat(getPlanPrice()).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</span>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
      <div class="text-center mb-6">
        <div class="w-16 h-16 bg-primary-light rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
          </svg>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">Método de pago</h3>
        <p class="text-gray-600 text-sm">Simulación de pago para desarrollo</p>
      </div>

      <form @submit.prevent="processPayment" class="space-y-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Correo electrónico para recibo
          </label>
          <input 
            v-model="email"
            type="email"
            disabled
            class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed"
          >
        </div>

        <div class="flex items-center">
          <input 
            v-model="termsAccepted"
            type="checkbox"
            id="terms"
            required
            class="rounded border-gray-300 text-primary focus:ring-primary"
          >
          <label for="terms" class="ml-2 block text-sm text-gray-700">
            Acepto los 
            <a href="#" class="text-primary hover:underline">términos y condiciones</a>
          </label>
        </div>

        <div class="flex justify-center space-x-4">
          <button 
            type="button"
            @click="goBack"
            class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
          >
            ← Cambiar plan
          </button>
          
          <button 
            type="submit"
            :disabled="loading || !termsAccepted"
            class="bg-primary text-white px-6 py-2 rounded-md font-medium hover:bg-primary-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ loading ? 'Procesando...' : `Pagar Bs ${parseFloat(getPlanPrice()).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}` }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const route = useRoute();
const loading = ref(false);
const termsAccepted = ref(false);

// Obtener el tipo de plan de la URL
const planType = computed(() => route.params.type);

const email = computed(() => {
  // Obtener email del store de autenticación
  const authStore = useAuthStore();
  return authStore.user?.email || '';
});

const plans = {
  'monthly': { price: 50.00, name: 'Mensual' },
  'yearly': { price: 500.00, name: 'Anual' },
  'lifetime': { price: 1500.00, name: 'Vitalicia' }
};

const getPlanPrice = () => {
  return plans[planType.value]?.price || 0;
};

const getLicenseTypeLabel = (type) => {
  return plans[type]?.name || type;
};

const goBack = () => {
  router.push('/licenses/purchase');
};

const processPayment = async () => {
  if (!termsAccepted.value) return;
  
  loading.value = true;
  
  try {
    // Crear la licencia primero
    const licenseResponse = await axios.post('/api/v1/licenses', {
      plan_type: planType.value
    });
    
    const licenseId = licenseResponse.data.license.id;
    
    // Procesar el pago
    const paymentResponse = await axios.post(`/api/v1/licenses/${licenseId}/process-payment`, {
      payment_method: 'simulated'
    });
    
    // Redirigir a éxito
    router.push('/licenses/success');
    
  } catch (error) {
    console.error('Error processing payment:', error);
    alert('Error al procesar el pago. Por favor, inténtalo nuevamente.');
  } finally {
    loading.value = false;
  }
};

// Importar el store de autenticación
import { useAuthStore } from '../../stores/auth';
</script>

<style scoped>
.bg-primary { background-color: #0C3C61; }
.bg-primary-light { background-color: #1A5A8A; }
.text-primary { color: #0C3C61; }
</style>
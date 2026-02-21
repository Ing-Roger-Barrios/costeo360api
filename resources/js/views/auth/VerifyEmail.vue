<!-- views/auth/VerifyEmail.vue -->
<template>
  <AuthCard>
    <h2 class="text-2xl font-bold text-center mb-6">Verificar Correo Electrónico</h2>
    
    <div v-if="verifying" class="text-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto mb-4"></div>
      <p class="text-gray-600">Verificando tu correo...</p>
    </div>

    <div v-else-if="verified">
      <div class="text-center mb-6">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        
        <h3 class="text-xl font-semibold text-gray-900 mb-2">¡Correo Verificado!</h3>
        <p class="text-gray-600">
          Tu correo electrónico ha sido verificado exitosamente.
        </p>
      </div>
      
      <div class="flex justify-center">
        <button 
          @click="goToDashboard"
          class="bg-primary text-white px-6 py-2 rounded-md hover:bg-primary-medium transition-colors"
        >
          Ir al Dashboard
        </button>
      </div>
    </div>

    <div v-else-if="error" class="text-center">
      <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
      </div>
      
      <h3 class="text-xl font-semibold text-gray-900 mb-2">Error de Verificación</h3>
      <p class="text-gray-600 mb-6">{{ error }}</p>
      
      <div class="space-y-4">
        <button 
          @click="handleResend"
          :disabled="resending"
          class="w-full bg-primary text-white py-2 px-4 rounded-md hover:bg-primary-medium transition-colors disabled:opacity-50"
        >
          {{ resending ? 'Reenviando...' : 'Reenviar Email de Verificación' }}
        </button>
        
        <button 
          @click="goToDashboard"
          class="w-full border border-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-50 transition-colors"
        >
          Ir al Dashboard
        </button>
      </div>
    </div>

    <div v-else class="text-center">
      <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
        </svg>
      </div>
      
      <h3 class="text-xl font-semibold text-gray-900 mb-2">Revisa tu Email</h3>
      <p class="text-gray-600 mb-6">
        Hemos enviado un email de verificación a:
      </p>
      <p class="font-medium text-gray-900 break-all mb-6">
        {{ authStore.user?.email }}
      </p>
      
      <p class="text-gray-600 text-sm mb-6">
        Haz clic en el enlace del email para verificar tu cuenta.
      </p>
      
      <div class="space-y-4">
        <button 
          @click="handleVerify"
          :disabled="checking"
          class="w-full bg-primary text-white py-2 px-4 rounded-md hover:bg-primary-medium transition-colors disabled:opacity-50"
        >
          {{ checking ? 'Verificando...' : 'Ya verifiqué mi correo' }}
        </button>
        
        <button 
          @click="handleResend"
          :disabled="resending"
          class="w-full border border-primary text-primary py-2 px-4 rounded-md hover:bg-primary hover:text-white transition-colors disabled:opacity-50"
        >
          {{ resending ? 'Reenviando...' : 'Reenviar Email' }}
        </button>
        
        <button 
          @click="goToDashboard"
          class="w-full border border-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-50 transition-colors"
        >
          Ir al Dashboard
        </button>
      </div>
    </div>
  </AuthCard>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import AuthCard from '../../components/AuthCard.vue';
import axios from 'axios';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const verifying = ref(false);
const verified = ref(false);
const error = ref(null);
const checking = ref(false);
const resending = ref(false);

const token = route.query.token;
const userId = route.query.user_id;

const handleVerify = async () => {
  if (!token || !userId) {
    error.value = 'Token o ID de usuario no proporcionados';
    return;
  }

  checking.value = true;
  error.value = null;

  try {
    // 👇 CAMBIAR DE POST A GET
    const response = await axios.get('/api/v1/auth/verify-email', {
      params: {
        token: token,
        user_id: userId
      }
    });

    if (response.data.email_verified) {
      verified.value = true;
      await authStore.checkAuth();
      
      setTimeout(() => {
        goToDashboard();
      }, 2000);
    }
  } catch (err) {
    console.error('Error verificando email:', err.response?.data);
    error.value = err.response?.data?.error || 'Error al verificar el correo';
  } finally {
    checking.value = false;
  }
};

const handleResend = async () => {
  resending.value = true;
  error.value = null;

  try {
    await axios.post('/api/v1/auth/resend-verification');
    alert('Email de verificación reenviado exitosamente. Por favor, revisa tu bandeja de entrada.');
  } catch (err) {
    error.value = err.response?.data?.error || 'Error al reenviar el email';
  } finally {
    resending.value = false;
  }
};

const goToDashboard = () => {
  if (authStore.isAdmin) {
    router.push('/dashboard');
  } else if (authStore.isUser) {
    router.push('/licenses');
  } else {
    router.push('/');
  }
};

onMounted(() => {
  // Si llegamos con token, verificar automáticamente
  if (token && userId) {
    handleVerify();
  }
});
</script>
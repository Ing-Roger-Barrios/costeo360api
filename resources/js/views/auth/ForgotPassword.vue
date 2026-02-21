<!-- views/auth/ForgotPassword.vue -->
<template>
  <AuthCard>
    <h2 class="text-2xl font-bold text-center mb-6">¿Olvidaste tu contraseña?</h2>
    
    <p class="text-gray-600 text-center mb-6">
      Ingresa tu correo electrónico y te enviaremos instrucciones para restablecer tu contraseña.
    </p>
    
    <form @submit.prevent="handleForgotPassword" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
        <input 
          v-model="form.email"
          type="email"
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
          required
        />
      </div>
      
      <button 
        type="submit"
        :disabled="loading"
        class="w-full btn-primary py-2 px-4 rounded-md hover:bg-primary-medium focus:outline-none focus:ring-2 focus:ring-primary disabled:opacity-50"
      >
        {{ loading ? 'Enviando...' : 'Enviar instrucciones' }}
      </button>
    </form>
    
    <div class="mt-4 text-center">
      <router-link to="/login" class="text-primary font-medium hover:text-primary-medium">
        ← Volver al login
      </router-link>
    </div>
    
    <div v-if="error" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
      {{ error }}
    </div>
    
    <div v-if="success" class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">
      {{ success }}
    </div>
  </AuthCard>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import AuthCard from '../../components/AuthCard.vue';

const form = ref({ email: '' });
const loading = ref(false);
const error = ref(null);
const success = ref(null);
const router = useRouter();

const handleForgotPassword = async () => {
  loading.value = true;
  error.value = null;
  success.value = null;
  
  try {
    await axios.post('/api/v1/auth/forgot-password', form.value);
    success.value = 'Se ha enviado un email con instrucciones para restablecer tu contraseña';
    form.value.email = '';
  } catch (err) {
    error.value = err.response?.data?.error || 'Error al enviar el email';
  } finally {
    loading.value = false;
  }
};
</script>
<!-- views/auth/ResetPassword.vue -->
<template>
  <AuthCard>
    <h2 class="text-2xl font-bold text-center mb-6">Restablecer Contraseña</h2>
    
    <form @submit.prevent="handleResetPassword" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nueva Contraseña</label>
        <input 
          v-model="form.password"
          type="password"
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
          required
          minlength="8"
        />
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Contraseña</label>
        <input 
          v-model="form.password_confirmation"
          type="password"
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
          required
        />
      </div>
      
      <button 
        type="submit"
        :disabled="loading"
        class="w-full btn-primary py-2 px-4 rounded-md hover:bg-primary-medium focus:outline-none focus:ring-2 focus:ring-primary disabled:opacity-50"
      >
        {{ loading ? 'Restableciendo...' : 'Restablecer Contraseña' }}
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
  </AuthCard>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import AuthCard from '../../components/AuthCard.vue';

const form = ref({ 
  token: '', 
  email: '', 
  password: '', 
  password_confirmation: '' 
});
const loading = ref(false);
const error = ref(null);
const router = useRouter();
const route = useRoute();

onMounted(() => {
  // Obtener token y email de los query params
  form.value.token = route.query.token || '';
  form.value.email = route.query.email || '';
});

const handleResetPassword = async () => {
  if (form.value.password !== form.value.password_confirmation) {
    error.value = 'Las contraseñas no coinciden';
    return;
  }
  
  loading.value = true;
  error.value = null;
  
  try {
    await axios.post('/api/v1/auth/reset-password', form.value);
    alert('Contraseña restablecida exitosamente. Ahora puedes iniciar sesión.');
    router.push('/login');
  } catch (err) {
    error.value = err.response?.data?.error || 'Error al restablecer la contraseña';
  } finally {
    loading.value = false;
  }
};
</script>
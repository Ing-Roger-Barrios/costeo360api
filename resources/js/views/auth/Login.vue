<!-- views/auth/Login.vue -->
<template>
  <AuthCard>
    <h2 class="text-2xl font-bold text-center mb-6">Iniciar Sesión</h2>
    
    <form @submit.prevent="handleLogin" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
        <input 
          v-model="form.email"
          type="email"
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
          required
        />
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
        <input 
          v-model="form.password"
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
        {{ loading ? 'Iniciando...' : 'Iniciar Sesión' }}
      </button>

    </form>
    
    <div class="mt-4 text-center">
      <p class="text-gray-600">
        ¿No tienes cuenta? 
        <router-link to="/register" class="text-primary font-medium hover:text-primary-medium">
          Regístrate aquí
        </router-link>
      </p>
      <p class="text-gray-600 mt-2">
        ¿Olvidaste tu contraseña? 
        <router-link to="/forgot-password" class="text-primary font-medium hover:text-primary-medium">
          Restablecer
        </router-link>
      </p>
    </div>
    
    <div v-if="error" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
      {{ error }}
    </div>
  </AuthCard>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import AuthCard from '../../components/AuthCard.vue';

const form = ref({ email: '', password: '' });
const loading = ref(false);
const error = ref(null);
const router = useRouter();
const authStore = useAuthStore();

const handleLogin = async () => {
  loading.value = true;
  error.value = null;
  
  try {
    await authStore.login(form.value);
    router.push('/');
  } catch (err) {
    error.value = err.response?.data?.message || 'Error al iniciar sesión';
  } finally {
    loading.value = false;
  }
};
</script>
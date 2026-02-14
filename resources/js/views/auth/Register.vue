<!-- views/auth/Register.vue -->
<template>
  <AuthCard>
    <h2 class="text-2xl font-bold text-center mb-6">Crear Cuenta</h2>
    
    <form @submit.prevent="handleRegister" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre Completo</label>
        <input 
          v-model="form.name"
          type="text"
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
          required
        />
      </div>
      
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
        {{ loading ? 'Creando cuenta...' : 'Crear Cuenta' }}
      </button>
    </form>
    
    <div class="mt-4 text-center">
      <p class="text-gray-600">
        ¿Ya tienes cuenta? 
        <router-link to="/login" class="text-primary font-medium hover:text-primary-medium">
          Inicia sesión aquí
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
import axios from 'axios';
import AuthCard from '../../components/AuthCard.vue';

const form = ref({ 
  name: '', 
  email: '', 
  password: '', 
  password_confirmation: '' 
});
const loading = ref(false);
const error = ref(null);
const router = useRouter();

const handleRegister = async () => {
  // Validación básica
  if (form.value.password !== form.value.password_confirmation) {
    error.value = 'Las contraseñas no coinciden';
    return;
  }
  
  loading.value = true;
  error.value = null;
  
  try {
    const response = await axios.post('/api/v1/auth/register', form.value);
    
    // Auto-login después del registro
    const loginResponse = await axios.post('/api/v1/auth/login', {
      email: form.value.email,
      password: form.value.password
    });
    
    localStorage.setItem('token', loginResponse.data.access_token);
    localStorage.setItem('user', JSON.stringify(loginResponse.data.user));
    
    router.push('/');
  } catch (err) {
    error.value = err.response?.data?.message || 'Error al crear la cuenta';
  } finally {
    loading.value = false;
  }
};
</script>
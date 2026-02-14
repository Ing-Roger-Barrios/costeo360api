<!-- components/DownloadSection.vue -->
<template>
  <section id="descarga" class="py-16 gradient-bg text-white">
    <div class="container mx-auto px-4 text-center">
      <h2 class="text-3xl md:text-4xl font-bold mb-6">Descarga Costeo360</h2>
      <p class="text-xl mb-8 max-w-2xl mx-auto text-blue-100">
        Obtén acceso inmediato a la versión más reciente de nuestro software de escritorio.
      </p>
      
      <div class="bg-slate-400 bg-opacity-20 rounded-lg p-8 max-w-md mx-auto">
        <div v-if="!authStore.isAuthenticated">
          <!-- Usuario no autenticado -->
          <div class="mb-6">
            <svg class="w-16 h-16 text-yellow-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
            <h3 class="text-xl font-semibold mb-2">Requiere cuenta</h3>
            <p class="text-blue-100">Regístrate o inicia sesión para descargar Costeo360.</p>
          </div>
          
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <router-link to="/register" class="btn-primary px-6 py-2 rounded-md font-medium">
              Registrarse
            </router-link>
            <router-link to="/login" class="bg-transparent border-2 border-white text-white px-6 py-2 rounded-md font-medium hover:bg-white hover:text-primary transition-colors">
              Iniciar Sesión
            </router-link>
          </div>
        </div>
        
        <div v-else-if="authStore.isAdmin">
          <!-- Administradores no pueden descargar -->
          <div class="mb-6">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
            <h3 class="text-xl font-semibold mb-2">Acceso restringido</h3>
            <p class="text-blue-100">Esta funcionalidad está disponible solo para usuarios con licencia.</p>
          </div>
          
          <router-link to="/dashboard" class="bg-transparent border-2 border-white text-white px-6 py-2 rounded-md font-medium hover:bg-white hover:text-primary transition-colors">
            Ir al Dashboard
          </router-link>
        </div>
        
        <div v-else>
          <!-- Usuario autenticado -->
          <template v-if="hasValidLicense">
            <div class="mb-4">
              <svg class="w-16 h-16 text-green-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <h3 class="text-xl font-semibold mb-2">¡Listo para descargar!</h3>
              <p class="text-blue-100">Hola, {{ authStore.user?.name }}. Tu cuenta está activa.</p>
              <p class="text-green-200 text-sm mt-2">
                Licencia {{ activeLicenseType }} activa
              </p>
            </div>
            
            <button 
              @click="handleDownload"
              :disabled="downloading"
              class="btn-primary px-8 py-3 rounded-lg text-lg font-semibold inline-flex items-center disabled:opacity-50"
            >
              <svg v-if="!downloading" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
              </svg>
              <svg v-if="downloading" class="animate-spin w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
              </svg>
              {{ downloading ? 'Iniciando descarga...' : 'Descargar Costeo360.exe' }}
            </button>
          </template>
          
          <template v-else>
            <div class="mb-4">
              <svg class="w-16 h-16 text-yellow-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
              </svg>
              <h3 class="text-xl font-semibold mb-2">Licencia requerida</h3>
              <p class="text-blue-100">Necesitas una licencia activa para descargar el software.</p>
            </div>
            
            <router-link to="/my-licenses" class="btn-primary px-8 py-3 rounded-lg text-lg font-semibold inline-flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
              </svg>
              Comprar Licencia
            </router-link>
          </template>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import axios from 'axios';

const authStore = useAuthStore();
const downloading = ref(false);
const hasValidLicense = ref(false);
const activeLicenseType = ref('');

const checkLicense = async () => {
  if (!authStore.isAuthenticated || authStore.isAdmin) {
    hasValidLicense.value = false;
    return;
  }
  
  try {
    const response = await axios.get('/api/v1/licenses/active');
    if (response.data.license && response.data.license.is_valid) {
      hasValidLicense.value = true;
      activeLicenseType.value = getLicenseTypeLabel(response.data.license.type);
    } else {
      hasValidLicense.value = false;
    }
  } catch (error) {
    hasValidLicense.value = false;
  }
};

const getLicenseTypeLabel = (type) => {
  const labels = {
    'monthly': 'Mensual',
    'yearly': 'Anual',
    'lifetime': 'Vitalicia'
  };
  return labels[type] || type;
};

const handleDownload = async () => {
  downloading.value = true;
  
  try {
    // Obtener URL de descarga
    const response = await axios.get('/api/v1/download/desktop');
    
    if (response.data.download_url) {
      // Iniciar descarga
      window.location.href = response.data.download_url;
    }
  } catch (error) {
    if (error.response?.status === 403) {
      // Redirigir a licencias
      window.location.href = '/my-licenses';
    } else {
      alert('Error al iniciar la descarga. Por favor, inténtalo nuevamente.');
    }
  } finally {
    downloading.value = false;
  }
};

onMounted(() => {
  checkLicense();
});
</script>

<style scoped>
.bg-primary { background-color: #0C3C61; }
.text-primary { color: #0C3C61; }
.gradient-bg {
  background: linear-gradient(135deg, #0C3C61 0%, #2B7BB9 100%);
}
.btn-primary {
  background-color: #0C3C61;
  color: white;
  transition: all 0.3s ease;
}
.btn-primary:hover {
  background-color: #1A5A8A;
  transform: translateY(-2px);
}
</style>
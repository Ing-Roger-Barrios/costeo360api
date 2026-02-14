<!-- components/Header.vue -->
<template>
  <header class="bg-white shadow-sm border-b border-gray-200">
    <div class="container mx-auto px-4 py-3 flex items-center justify-between">
      <!-- Logo y título -->
      <div class="flex items-center space-x-3">
        <div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center">
          <span class="text-white font-bold text-xl">C</span>
        </div>
        <div>
          <h1 class="text-xl font-bold text-primary">Costeo360</h1>
          <p v-if="subtitle" class="text-xs text-gray-500">{{ subtitle }}</p>
        </div>
      </div>
      
      <!-- Título de la página -->
      <h2 class="text-lg font-semibold text-gray-800 capitalize hidden md:block">
        {{ pageTitle }}
      </h2>
      
      <!-- Información del usuario -->
      <div class="flex items-center space-x-4">
        <div class="relative">
          <button 
            @click="toggleUserMenu"
            class="flex items-center space-x-2 text-gray-700 hover:text-gray-900 focus:outline-none"
          >
            <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
              {{ userInitials }}
            </div>
            <span class="hidden md:block">{{ authStore.user?.name }}</span>
          </button>

          <div v-show="showUserMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
            <button
              @click="handleLogout"
              class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
            >
              Cerrar Sesión
            </button>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const props = defineProps({
  pageTitle: {
    type: String,
    default: 'Costeo360'
  },
  subtitle: {
    type: String,
    default: ''
  }
});

const router = useRouter();
const authStore = useAuthStore();
const showUserMenu = ref(false);

const userInitials = computed(() => {
  if (!authStore.user) return 'U';
  return authStore.user.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
});

const toggleUserMenu = () => {
  showUserMenu.value = !showUserMenu.value;
};

const handleLogout = async () => {
  await authStore.logout();
  router.push('/');
};

// Cerrar menú al hacer clic fuera
onMounted(() => {
  document.addEventListener('click', (event) => {
    const button = document.querySelector('.flex.items-center.space-x-2');
    if (button && !button.contains(event.target)) {
      showUserMenu.value = false;
    }
  });
});
</script>

<style scoped>
.bg-primary { background-color: #0C3C61; }
.text-primary { color: #0C3C61; }
</style>
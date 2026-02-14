<template>
  <header class="bg-white shadow-sm border-b border-gray-200">
    <div class="flex items-center justify-between px-6 py-3">
      <div>
        <h2 class="text-lg font-semibold text-gray-800 capitalize">
          {{ currentPageTitle }}
        </h2>
      </div>
            
      <!-- 👇 SELECTOR DE VERSIÓN EN EL HEADER -->
      <VersionSelector />
      
      <div class="flex items-center space-x-4">
        <div class="relative" ref="userMenuContainer">
          <button 
            ref="userButton"
            @click="toggleUserMenu"
            class="flex items-center space-x-2 text-gray-700 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
          >
            <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
              {{ userInitials }}
            </div>
            <span class="hidden md:block">{{ user?.name }}</span>
            <svg 
              class="w-4 h-4 transform transition-transform duration-200"
              :class="{ 'rotate-180': showUserMenu }"
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </button>

          <transition name="menu-fade">
            <div 
              v-show="showUserMenu" 
              class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200"
            >
              <div class="px-4 py-2 border-b border-gray-200">
                <p class="text-xs text-gray-500">Conectado como</p>
                <p class="text-sm font-medium text-gray-900 truncate">{{ user?.name }}</p>
                <p class="text-xs text-gray-400 truncate">{{ user?.email }}</p>
              </div>
              
              <button
                @click="handleLogout"
                class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors"
              >
                <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                Cerrar Sesión
              </button>
            </div>
          </transition>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import VersionSelector from '../versions/VersionSelector.vue';

const router = useRouter();
const authStore = useAuthStore();
const showUserMenu = ref(false);
const userMenuContainer = ref(null);
const userButton = ref(null);

const pageTitles = {
  'dashboard': 'Dashboard',
  'resources': 'Recursos Maestros',
  'items': 'Items',
  'modules': 'Módulos',
  'categories': 'Categorías',
  'versions': 'Versiones',
  'regions': 'Regiones'
};

const currentPageTitle = computed(() => {
  const routeName = router.currentRoute.value.name;
  return pageTitles[routeName] || 'Costeo360';
});

const user = computed(() => authStore.user);
const userInitials = computed(() => {
  if (!user.value) return 'U';
  return user.value.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
});

const toggleUserMenu = () => {
  showUserMenu.value = !showUserMenu.value;
};

const handleLogout = async () => {
  showUserMenu.value = false;
  await authStore.logout();
  router.push('/');
};

// Cerrar menú al hacer clic fuera
const handleClickOutside = (event) => {
  if (userMenuContainer.value && !userMenuContainer.value.contains(event.target)) {
    showUserMenu.value = false;
  }
};

// Cerrar menú al cambiar de ruta
const handleRouteChange = () => {
  showUserMenu.value = false;
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
  router.afterEach(handleRouteChange);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
  router.afterEach(() => {}); // Limpiar listener
});
</script>

<style scoped>
.bg-primary { background-color: #0C3C61; }
.text-primary { color: #0C3C61; }

/* Animación del menú */
.menu-fade-enter-active,
.menu-fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.menu-fade-enter-from,
.menu-fade-leave-to {
  opacity: 0;
  transform: translateY(-5px);
}
</style>
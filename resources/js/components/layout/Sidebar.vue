<template>
  <div class="w-64 bg-gray-800 text-white flex flex-col">
    <!-- Logo/Header del sidebar -->
    <div class="p-4 border-b border-gray-700">
      <h1 class="text-xl font-bold">Costeo360</h1>
      <p class="text-xs text-gray-400">Administración</p>
    </div>

    <!-- Navegación principal -->
    <nav class="flex-1 px-2 py-4">
      <ul class="space-y-1">
        <li>
          <router-link 
            :to="{ name: 'dashboard' }"
            class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200"
            :class="isActive('dashboard') 
              ? 'bg-blue-600 text-white' 
              : 'text-gray-300 hover:bg-gray-700 hover:text-white'"
          >
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            Dashboard
          </router-link>
        </li>
        <!-- imporataciones .ddp -->
        <li>
          <router-link 
            :to="{ name: 'import-ddp' }" 
            class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200"
            :class="isActive('import-ddp') 
              ? 'bg-blue-600 text-white' 
              : 'text-gray-300 hover:bg-gray-700 hover:text-white'"
          >
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
            </svg>
            Importar Proyecto (.DDP)
          </router-link>
        </li>
        <!-- Gestión de Regiones -->
        <li>
          <router-link 
            :to="{ name: 'regions' }"
            class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200"
            :class="isActive('regions') 
              ? 'bg-blue-600 text-white' 
              : 'text-gray-300 hover:bg-gray-700 hover:text-white'"
          >
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.447-.894L15 4m0 13V4m0 0L9 7"></path>
            </svg>
            Regiones
          </router-link>
        </li>

        <!-- 👇 AÑADIDO: Gestión de Recursos Maestros -->
        <li>
          <router-link 
            :to="{ name: 'resources' }"
            class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200"
            :class="isActive('resources') 
              ? 'bg-blue-600 text-white' 
              : 'text-gray-300 hover:bg-gray-700 hover:text-white'"
          >
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M9 11a2 2 0 012-2m-2 2v6a2 2 0 002 2m-2-2h6"></path>
            </svg>
            Recursos Maestros
          </router-link>
        </li>
        <!-- 👇Gestión de Recursos Maestros Masivos -->
        <li>
          <router-link 
            :to="{ name: 'bulk-prices' }" 
            
            class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200"
            :class="isActive('bulk-prices') 
              ? 'bg-blue-600 text-white' 
              : 'text-gray-300 hover:bg-gray-700 hover:text-white'"
          >
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            Gestión Masiva de Precios
          </router-link>
        </li>

        <!-- Gestión de Items -->
        <li>
          <router-link 
            :to="{ name: 'items' }"
            class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200"
            :class="isActive('items') 
              ? 'bg-blue-600 text-white' 
              : 'text-gray-300 hover:bg-gray-700 hover:text-white'"
          >
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            Items
          </router-link>
        </li>

        <!-- Gestión de Módulos -->
        <li>
          <router-link 
            :to="{ name: 'modules' }"
            class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200"
            :class="isActive('modules') 
              ? 'bg-blue-600 text-white' 
              : 'text-gray-300 hover:bg-gray-700 hover:text-white'"
          >
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
            </svg>
            Módulos
          </router-link>
        </li>

        <!-- Gestión de Categorías -->
        <li>
          <router-link 
            :to="{ name: 'categories' }"
            class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200"
            :class="isActive('categories') 
              ? 'bg-blue-600 text-white' 
              : 'text-gray-300 hover:bg-gray-700 hover:text-white'"
          >
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M9 11a2 2 0 012-2m-2 2v6a2 2 0 002 2m-2-2h6"></path>
            </svg>
            Categorías
          </router-link>
        </li>

        <!-- Gestión de Versiones -->
        <li>
          <router-link 
            :to="{ name: 'versions' }"
            class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200"
            :class="isActive('versions') 
              ? 'bg-blue-600 text-white' 
              : 'text-gray-300 hover:bg-gray-700 hover:text-white'"
          >
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Versiones
          </router-link>
        </li>
      </ul>
    </nav>

    <!-- Footer del sidebar -->
    <div class="px-4 py-3 border-t border-gray-700 text-xs text-gray-400">
      PresupuestoPro v2.0
    </div>
  </div>
</template>

<script setup>
import { useRoute } from 'vue-router';

const route = useRoute();

const isActive = (routeName) => {
  return route.name === routeName;
};
</script>
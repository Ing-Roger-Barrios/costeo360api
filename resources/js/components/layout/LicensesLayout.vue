<!-- layouts/LicensesLayout.vue -->
<template>
  <div class="min-h-screen flex flex-col bg-gray-50">
    
    <!-- Header -->
    <Header :page-title="pageTitle" subtitle="Gestión de Licencias" />
    
    <!-- Contenido principal -->
    <main class="flex-grow container mx-auto px-4 py-8">
      <router-view />
    </main>
    
    <!-- Footer Premium -->
    <footer class="mt-auto bg-primary text-white">
        <div class="container mx-auto px-6 py-8">
            
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
            
            <!-- Marca -->
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 bg-white/20 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-sm">C</span>
                </div>
                <span class="font-semibold tracking-wide text-lg">
                Costeo360
                </span>
            </div>

            <!-- Links -->
            <div class="flex items-center space-x-8 text-sm text-blue-200">
                <a href="#" class="hover:text-white transition-colors duration-300">
                <!--Términos-->
                </a>
                <a href="#" class="hover:text-white transition-colors duration-300">
                <!--Privacidad-->
                </a>
                <a href="#" class="hover:text-white transition-colors duration-300">
                <!--Soporte-->
                </a>
            </div>

            <!-- Copyright -->
            <div class="text-sm text-blue-300 text-center md:text-right">
                © {{ currentYear }} Costeo360. Todos los derechos reservados.
            </div>

            </div>

        </div>
        </footer>


  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import Header from '../Header.vue';


const route = useRoute();

const currentYear = computed(() => new Date().getFullYear());

const pageTitle = computed(() => {
  const titles = {
    'my-licenses': 'Mis Licencias',
    'purchase-license': 'Comprar Licencia',
    'license-payment': 'Confirmar Pago',
    'license-success': 'Pago Exitoso'
  };
  return titles[route.name] || 'Licencias';
});
</script>

<style scoped>
.bg-primary { background-color: #0C3C61; }
</style>
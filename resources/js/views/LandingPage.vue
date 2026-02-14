<!-- views/LandingPage.vue -->
<template>
  <div class="min-h-screen  flex flex-col  bg-gray-50">
    
    <!-- Header -->
    <header class="bg-white shadow-sm">
      <div class="container mx-auto px-4 py-4 flex justify-between items-center">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center">
            <span class="text-white font-bold text-xl">C</span>
          </div>
          <h1 class="text-2xl font-bold text-primary">Costeo360</h1>
        </div>
        
        <nav class="hidden md:flex space-x-6">
          <a href="#mision" class="text-gray-700 hover:text-primary transition-colors">Misión</a>
          <a href="#vision" class="text-gray-700 hover:text-primary transition-colors">Visión</a>
          <a href="#caracteristicas" class="text-gray-700 hover:text-primary transition-colors">Características</a>
          <a href="#descarga" class="text-gray-700 hover:text-primary transition-colors">Descarga</a>
        </nav>
        
        <div class="flex items-center space-x-4">
          <template v-if="authStore.isAuthenticated">
            <span class="hidden md:inline text-gray-700">Hola, {{ authStore.user?.name }}</span>
            
            <template v-if="authStore.isAdmin">
              <router-link to="/dashboard" class="btn-primary px-4 py-2 rounded-md text-sm font-medium">
                Dashboard Admin
              </router-link>
            </template>
            <template v-else-if="authStore.isUser">
              <router-link to="/licenses" class="btn-primary px-4 py-2 rounded-md text-sm font-medium">
                Mis Licencias
              </router-link>
            </template>
            
            <button 
              @click="handleLogout"
              class="btn-outline px-4 py-2 rounded-md text-sm font-medium"
            >
              Cerrar Sesión
            </button>
          </template>
          <template v-else>
            <router-link to="/login" class="btn-outline px-4 py-2 rounded-md text-sm font-medium">
              Iniciar Sesión
            </router-link>
            <router-link to="/register" class="btn-primary px-4 py-2 rounded-md text-sm font-medium hidden md:inline">
              Registrarse
            </router-link>
          </template>
        </div>
      </div>
    </header>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="flex-grow"> 

        <!-- Hero Section -->
        <section class="gradient-bg text-white py-20">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-6xl font-bold mb-6">
            Presupuestos Profesionales <br>
            <span class="text-yellow-300">al alcance de tu mano</span>
            </h1>
            <p class="text-xl md:text-2xl mb-8 max-w-3xl mx-auto text-blue-100">
            Costeo360 es la solución integral para el cálculo preciso de costos en proyectos de construcción y obra civil.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
            <a href="#descarga" class="btn-primary px-8 py-3 rounded-lg text-lg font-semibold">
                Descargar Ahora
            </a>
            <a href="#caracteristicas" class="bg-transparent border-2 border-white text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-white hover:text-primary transition-colors">
                Ver Características
            </a>
            </div>
        </div>
        </section>

        <!-- Mision Section -->
        <section id="mision" class="py-16 bg-white">
            <div class="container mx-auto px-4">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-primary mb-4">Nuestra Misión</h2>
                    <div class="w-20 h-1 bg-primary-medium mx-auto"></div>
                </div>
                <div class="max-w-4xl mx-auto text-center">
                    <p class="text-lg text-gray-700 leading-relaxed">
                        Proporcionar a profesionales de la construcción una herramienta intuitiva, precisa y eficiente 
                        para el cálculo de costos, optimización de recursos y gestión de presupuestos en tiempo real.
                    </p>
                </div>
            </div>
        </section>

        <!-- Vision Section -->
        <section id="vision" class="py-16 bg-gray-50">
            <div class="container mx-auto px-4">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-primary mb-4">Nuestra Visión</h2>
                    <div class="w-20 h-1 bg-primary-medium mx-auto"></div>
                </div>
                <div class="max-w-4xl mx-auto text-center">
                    <p class="text-lg text-gray-700 leading-relaxed">
                        Ser el software líder en Latinoamérica para la gestión de costos en la industria de la construcción, 
                        transformando la manera en que los profesionales planifican, presupuestan y ejecutan sus proyectos.
                    </p>
                </div>
            </div>
        </section>

        <!-- Caracteristicas Section -->
        <section id="caracteristicas" class="py-16 bg-white">
            <div class="container mx-auto px-4">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-primary mb-4">Características Principales</h2>
                    <div class="w-20 h-1 bg-primary-medium mx-auto"></div>
                </div>
                
                <div class="grid md:grid-cols-3 gap-8">
                    <div class="bg-white p-6 rounded-lg shadow-lg border border-gray-200 hover:shadow-xl transition-shadow">
                        <div class="w-12 h-12 bg-primary-medium rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h6m-6-7h6m-6 3h6m3-11H9a3 3 0 00-3 3v10a3 3 0 003 3h12a3 3 0 003-3V9a3 3 0 00-3-3z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-primary mb-2">Importación Prescom</h3>
                        <p class="text-gray-600">Importa proyectos completos desde archivos .DDP de Prescom sin perder ningún detalle.</p>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow-lg border border-gray-200 hover:shadow-xl transition-shadow">
                        <div class="w-12 h-12 bg-primary-medium rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-primary mb-2">Gestión Inteligente</h3>
                        <p class="text-gray-600">Administra categorías, módulos, items y recursos con relaciones inteligentes y rendimientos precisos.</p>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow-lg border border-gray-200 hover:shadow-xl transition-shadow">
                        <div class="w-12 h-12 bg-primary-medium rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-primary mb-2">Precios Regionales</h3>
                        <p class="text-gray-600">Gestiona precios por región y actualiza costos en tiempo real según tu ubicación geográfica.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Descarga (componente nuevo) -->
        <DownloadSection />

    </main>

    <!-- Footer -->
    <footer class="bg-gradient-to-b from-[#0C3C61] to-[#082B46] text-white mt-auto">
        <div class="container mx-auto px-6 py-12">
            
            <!-- Grid principal -->
            <div class="grid md:grid-cols-4 gap-10 text-center md:text-left">
            
            <!-- Marca -->
            <div>
                <div class="flex items-center justify-center md:justify-start space-x-3 mb-4">
                <div class="w-10 h-10 bg-slate-400 bg-opacity-20 rounded-lg flex items-center justify-center">
                    <span class="font-bold text-lg">C</span>
                </div>
                <h3 class="text-2xl font-bold">Costeo360</h3>
                </div>
                <p class="text-blue-200 text-sm leading-relaxed">
                Software profesional para presupuestos y costeo de obras.
                Precisión, control y eficiencia en cada proyecto.
                </p>
            </div>

            <!-- Producto -->
            <div>
                <h4 class="font-semibold text-lg mb-4">Producto</h4>
                <ul class="space-y-2 text-blue-200 text-sm">
                <li><a href="#caracteristicas" class="hover:text-white transition">Características</a></li>
                <li><a href="#descarga" class="hover:text-white transition">Descargar</a></li>
                <li><a href="#" class="hover:text-white transition">Actualizaciones</a></li>
                </ul>
            </div>

            <!-- Empresa -->
            <div>
                <h4 class="font-semibold text-lg mb-4">Empresa</h4>
                <ul class="space-y-2 text-blue-200 text-sm">
                <li><a href="#mision" class="hover:text-white transition">Misión</a></li>
                <li><a href="#vision" class="hover:text-white transition">Visión</a></li>
                <li><a href="#" class="hover:text-white transition">Contacto</a></li>
                </ul>
            </div>

            <!-- Redes -->
            <div>
                <h4 class="font-semibold text-lg mb-4">Síguenos</h4>
                <div class="flex justify-center md:justify-start space-x-4">
                
                <!-- Facebook -->
                <a href="#" class="bg-slate-400 bg-opacity-20 p-3 rounded-lg hover:bg-opacity-40 transition">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M22 12a10 10 0 10-11.5 9.9v-7H8v-3h2.5V9.5c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.4h-1.2c-1.2 0-1.6.8-1.6 1.5V12H17l-.5 3h-2.7v7A10 10 0 0022 12z"/>
                    </svg>
                </a>

                <!-- Instagram -->
                <a href="#" class="bg-slate-400 bg-opacity-20 p-3 rounded-lg hover:bg-opacity-40 transition">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M7 2C4.2 2 2 4.2 2 7v10c0 2.8 2.2 5 5 5h10c2.8 0 5-2.2 5-5V7c0-2.8-2.2-5-5-5H7zm5 5a5 5 0 110 10 5 5 0 010-10zm6.5-.8a1.3 1.3 0 11-2.6 0 1.3 1.3 0 012.6 0z"/>
                    </svg>
                </a>

                <!-- LinkedIn -->
                <a href="#" class="bg-slate-400 bg-opacity-20 p-3 rounded-lg hover:bg-opacity-40 transition">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M4.98 3.5C4.98 4.88 3.86 6 2.48 6S0 4.88 0 3.5 1.12 1 2.48 1 4.98 2.12 4.98 3.5zM.5 8h4V24h-4V8zm7.5 0h3.8v2.2h.1c.5-.9 1.8-2.2 3.7-2.2 4 0 4.7 2.6 4.7 6V24h-4v-7.3c0-1.7 0-4-2.4-4s-2.8 1.9-2.8 3.8V24h-4V8z"/>
                    </svg>
                </a>

                </div>
            </div>

            </div>

            <!-- Línea divisoria -->
            <div class="border-t border-blue-400 mt-10 pt-6 text-center text-blue-200 text-sm">
            © 2026 Costeo360. Todos los derechos reservados.
            </div>

        </div>
        </footer>

  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import DownloadSection from '../components/DownloadSection.vue';

const authStore = useAuthStore();

const handleLogout = async () => {
  await authStore.logout();
};

// Estilos globales
onMounted(() => {
  const style = document.createElement('style');
  style.textContent = `
    .bg-primary { background-color: #0C3C61; }
    .bg-primary-light { background-color: #1A5A8A; }
    .bg-primary-medium { background-color: #2B7BB9; }
    .text-primary { color: #0C3C61; }
    .btn-primary {
      background-color: #0C3C61;
      color: white;
      transition: all 0.3s ease;
    }
    .btn-primary:hover {
      background-color: #1A5A8A;
      transform: translateY(-2px);
    }
    .btn-outline {
      border: 2px solid #0C3C61;
      color: #0C3C61;
      transition: all 0.3s ease;
    }
    .btn-outline:hover {
      background-color: #0C3C61;
      color: white;
    }
    .gradient-bg {
      background: linear-gradient(135deg, #0C3C61 0%, #2B7BB9 100%);
    }
  `;
  document.head.appendChild(style);
});
</script>
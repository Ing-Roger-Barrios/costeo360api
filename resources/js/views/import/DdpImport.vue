<template>
  <div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-md p-6">
      <h2 class="text-xl font-semibold mb-4">Importar Proyecto (.DDP)</h2>
      
      <!-- File upload -->
      <div 
        class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-blue-400 transition-colors"
        @dragover.prevent
        @drop.prevent="handleDrop"
        @click="$refs.fileInput.click()"
      >
        <input 
          ref="fileInput"
          type="file" 
          accept=".ddp" 
          class="hidden"
          @change="handleFileSelect"
        />
        
        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
        </svg>
        
        <p class="text-gray-600">
          Arrastra tu archivo .DDP aquí o haz clic para seleccionarlo
        </p>
        <p class="text-sm text-gray-500 mt-2">
          Solo archivos .DDP (Proyectos Prescom)
        </p>
      </div>

      <!-- Preview -->
      <div v-if="preview" class="mt-6 p-4 bg-gray-50 rounded-lg">
        <h3 class="font-medium text-gray-900 mb-2">Vista previa</h3>
        <div class="space-y-2">
          <div class="flex justify-between">
            <span class="text-gray-600">Categoría:</span>
            <span class="font-medium">{{ preview.categoria }}</span>
          </div>
        </div>
        
        <button
          @click="createCategory"
          class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
        >
          Crear Categoría e Importar Proyecto
        </button>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="mt-4 text-center">
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 mx-auto"></div>
        <p class="text-gray-600 mt-2">Procesando archivo...</p>
      </div>

      <!-- Error -->
      <div v-if="error" class="mt-4 p-3 bg-red-50 text-red-700 rounded-md">
        {{ error }}
      </div>
    </div>
  </div>
</template>

<script setup>
// 👇 SOLO IMPORTS, NADA ASÍNCRONO AQUÍ
import { ref } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import { useRouter } from 'vue-router';

// 👇 SOLO DECLARACIONES DE VARIABLES Y FUNCIONES
const router = useRouter();
const fileInput = ref(null);
const loading = ref(false);
const error = ref(null);
const preview = ref(null);

// 👇 FUNCIONES SÍNCRONAS
const handleDrop = (event) => {
  const files = event.dataTransfer.files;
  if (files.length > 0) {
    processFile(files[0]);
  }
};

const handleFileSelect = (event) => {
  const files = event.target.files;
  if (files.length > 0) {
    processFile(files[0]);
  }
};

// 👇 FUNCIONES ASÍNCRONAS DENTRO DE FUNCIONES (NO DE NIVEL SUPERIOR)
const processFile = async (file) => {
  if (!file.name.toLowerCase().endsWith('.ddp')) {
    error.value = 'Por favor selecciona un archivo .DDP válido';
    return;
  }

  loading.value = true;
  error.value = null;
  preview.value = null;

  const formData = new FormData();
  formData.append('file', file);

  try {
    const response = await axios.post('/api/v1/import/ddp', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    preview.value = response.data.preview;
    localStorage.setItem('ddp_extracted_path', response.data.extracted_path);
    
  } catch (err) {
    error.value = err.response?.data?.error || 'Error al procesar el archivo';
  } finally {
    loading.value = false;
  }
};

const createCategory = async () => {
  const extractedPath = localStorage.getItem('ddp_extracted_path');
  
  if (!extractedPath) {
    await Swal.fire({
      title: 'Error',
      text: 'No se encontró la ruta de extracción del archivo .DDP',
      icon: 'error'
    });
    return;
  }

  if (!preview.value?.categoria) {
    return;
  }

  try {
    const response = await axios.post('/api/v1/import/complete-project', {
      extracted_path: extractedPath,
      category_name: preview.value.categoria
    });

    await Swal.fire({
      title: '¡Proyecto importado!',
      text: 'El proyecto ha sido importado exitosamente.',
      icon: 'success',
      timer: 2000,
      showConfirmButton: false
    });

    router.push('/categories');
    
  } catch (error) {
    console.error('Error detallado:', error.response?.data);
    await Swal.fire({
      title: 'Error',
      text: error.response?.data?.error || 'Error al importar el proyecto',
      icon: 'error'
    });
  }
};
</script>
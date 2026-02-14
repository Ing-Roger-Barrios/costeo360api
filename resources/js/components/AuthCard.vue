<!-- components/AuthCard.vue -->
<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md">
      <div class="flex items-center justify-center mb-6">
        <div class="w-12 h-12 bg-primary rounded-lg flex items-center justify-center">
          <span class="text-white font-bold text-xl">C</span>
        </div>
        <h1 class="text-2xl font-bold text-primary ml-3">Costeo360</h1>
      </div>
      
      <slot></slot>
    </div>
  </div>
</template>

<script setup>
// Estilos globales para el color primary
import { onMounted } from 'vue';

onMounted(() => {
  const style = document.createElement('style');
  style.textContent = `
    .bg-primary { background-color: #0C3C61; }
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
  `;
  document.head.appendChild(style);
});
</script>

<style scoped>
.bg-primary { background-color: #0C3C61; }
.text-primary { color: #0C3C61; }
</style>
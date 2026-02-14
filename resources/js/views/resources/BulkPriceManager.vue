<template>
  <div class="max-w-full mx-auto">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Gestión Masiva de Precios</h1>
      <p class="text-gray-600">Actualiza precios de forma masiva comparando la versión actual con la versión de trabajo.</p>
    </div>

    <PriceTable />

    <Alert
      v-if="alert.message"
      :type="alert.type"
      :message="alert.message"
      @close="alert.message = ''"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import PriceTable from '../../components/resources/PriceTable.vue';
import Alert from '../../components/ui/Alert.vue';

const alert = ref({ message: '', type: 'success' });
</script>
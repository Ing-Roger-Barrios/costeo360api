<template>
  <div 
    v-if="message" 
    :class="[
      'fixed top-4 right-4 z-50 px-4 py-3 rounded-md shadow-lg text-white font-medium',
      type === 'success' ? 'bg-green-600' : 'bg-red-600'
    ]"
  >
    {{ message }}
    <button 
      @click="close" 
      class="ml-2 text-white hover:text-gray-200 focus:outline-none"
    >
      ×
    </button>
  </div>
</template>

<script setup>
defineProps({
  type: {
    type: String,
    default: 'success'
  },
  message: {
    type: String,
    required: true
  }
});

const emit = defineEmits(['close']);

const close = () => {
  emit('close');
};
</script>
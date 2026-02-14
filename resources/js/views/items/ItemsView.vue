<template>
  <div class="max-w-6xl mx-auto">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Gestión de Items</h1>
        <p class="text-gray-600">Crea y gestiona partidas compuestas por recursos</p>
      </div>
      
      <button
        @click="showForm = true; itemsStore.clearCurrentItem()"
        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center space-x-2"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        <span>Nuevo Item</span>
      </button>
    </div>

    <div v-if="showForm" class="mb-6">
      <ItemForm
        :item="itemsStore.currentItem"
        :loading="itemsStore.loading"
        @submit="handleSaveItem"
        @cancel="showForm = false"
      />
    </div>

    <ItemList
      :loading="itemsStore.loading"
      @edit="handleEditItem"
      @delete="handleDeleteItem"
    />

    <Alert
      v-if="alert.message"
      :type="alert.type"
      :message="alert.message"
      @close="alert.message = ''"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useItemsStore } from '../../stores/items';
import ItemForm from '../../components/items/ItemForm.vue';
import ItemList from '../../components/items/ItemList.vue';
import Alert from '../../components/ui/Alert.vue';
import Swal from 'sweetalert2';

const itemsStore = useItemsStore();
const showForm = ref(false);
const alert = ref({ message: '', type: 'success' });

onMounted(async () => {
  try {
    await itemsStore.fetchItems();
  } catch (error) {
    alert.value = { 
      message: 'Error al cargar los items: ' + (itemsStore.error || 'Error desconocido'), 
      type: 'error' 
    };
  }
});

const handleSaveItem = async (itemData) => {
  try {
    if (itemsStore.currentItem) {
      await itemsStore.updateItem(itemsStore.currentItem.id, itemData);
      alert.value = { message: 'Item actualizado exitosamente', type: 'success' };
    } else {
      await itemsStore.createItem(itemData);
      alert.value = { message: 'Item creado exitosamente', type: 'success' };
    }
    showForm.value = false;
  } catch (error) {
    alert.value = { 
      message: itemsStore.error || 'Error al guardar el item', 
      type: 'error' 
    };
  }
};

const handleEditItem = (item) => {
  itemsStore.setCurrentItem(item);
  showForm.value = true;
};

const handleDeleteItem = async (item) => {
  const result = await Swal.fire({
    title: '¿Eliminar item?',
    html: `¿Estás seguro de eliminar el item "<strong>${item.codigo} - ${item.descripcion}</strong>"?<br><br>Esta acción no se puede deshacer.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar',
    reverseButtons: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6b7280'
  });

  if (!result.isConfirmed) return;

  try {
    await itemsStore.deleteItem(item.id);
    await Swal.fire({
      title: '¡Item eliminado!',
      text: 'El item ha sido eliminado exitosamente.',
      icon: 'success',
      timer: 2000,
      showConfirmButton: false
    });
  } catch (error) {
    Swal.fire({
      title: 'Error',
      text: error.response?.data?.message || 'Error al eliminar el item',
      icon: 'error'
    });
  }
};
</script>
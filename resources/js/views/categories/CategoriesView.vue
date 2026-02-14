<template>
  <div class="max-w-6xl mx-auto">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Gestión de Categorías</h1>
        <p class="text-gray-600">Crea y gestiona categorías compuestas por módulos</p>
      </div>
      
      <button
        @click="showForm = true; categoriesStore.clearCurrentCategory()"
        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center space-x-2"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        <span>Nueva Categoría</span>
      </button>
    </div>

    <div v-if="showForm" class="mb-6">
      <CategoryForm
        :category="categoriesStore.currentCategory"
        :loading="categoriesStore.loading"
        @submit="handleSaveCategory"
        @cancel="showForm = false"
      />
    </div>

    <CategoryList
      :loading="categoriesStore.loading"
      @edit="handleEditCategory"
      @delete="handleDeleteCategory"
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
import { useCategoriesStore } from '../../stores/categories';
import CategoryForm from '../../components/categories/CategoryForm.vue';
import CategoryList from '../../components/categories/CategoryList.vue';
import Alert from '../../components/ui/Alert.vue';
import Swal from 'sweetalert2';

const categoriesStore = useCategoriesStore();
const showForm = ref(false);
const alert = ref({ message: '', type: 'success' });

onMounted(async () => {
  try {
    await categoriesStore.fetchCategories();
  } catch (error) {
    alert.value = { 
      message: 'Error al cargar las categorías: ' + (categoriesStore.error || 'Error desconocido'), 
      type: 'error' 
    };
  }
});

const handleSaveCategory = async (categoryData) => {
  try {
    if (categoriesStore.currentCategory) {
      await categoriesStore.updateCategory(categoriesStore.currentCategory.id, categoryData);
      alert.value = { message: 'Categoría actualizada exitosamente', type: 'success' };
    } else {
      await categoriesStore.createCategory(categoryData);
      alert.value = { message: 'Categoría creada exitosamente', type: 'success' };
    }
    showForm.value = false;
  } catch (error) {
    alert.value = { 
      message: categoriesStore.error || 'Error al guardar la categoría', 
      type: 'error' 
    };
  }
};

const handleEditCategory = (category) => {
  categoriesStore.setCurrentCategory(category);
  showForm.value = true;
};

const handleDeleteCategory = async (category) => {
  const result = await Swal.fire({
    title: '¿Eliminar categoría?',
    html: `¿Estás seguro de eliminar la categoría "<strong>${category.codigo} - ${category.nombre}</strong>"?<br><br>Esta acción no se puede deshacer.`,
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
    await categoriesStore.deleteCategory(category.id);
    await Swal.fire({
      title: '¡Categoría eliminada!',
      text: 'La categoría ha sido eliminada exitosamente.',
      icon: 'success',
      timer: 2000,
      showConfirmButton: false
    });
  } catch (error) {
    Swal.fire({
      title: 'Error',
      text: error.response?.data?.message || 'Error al eliminar la categoría',
      icon: 'error'
    });
  }
};
</script>
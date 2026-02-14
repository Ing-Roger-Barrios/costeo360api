import Swal from 'sweetalert2';

export function useSweetAlert() {
  const confirmDelete = async (title, name, type) => {
    const result = await Swal.fire({
      title: `¿${title}?`,
      html: `¿Estás seguro de ${title.toLowerCase()} "${name}"?<br><br>Esta acción no se puede deshacer.`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
      reverseButtons: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6b7280'
    });
    return result.isConfirmed;
  };

  const showSuccess = async (message) => {
    await Swal.fire({
      title: '¡Éxito!',
      text: message,
      icon: 'success',
      timer: 2000,
      showConfirmButton: false
    });
  };

  const showError = async (message) => {
    await Swal.fire({
      title: 'Error',
      text: message,
      icon: 'error'
    });
  };

  return {
    confirmDelete,
    showSuccess,
    showError
  };
}
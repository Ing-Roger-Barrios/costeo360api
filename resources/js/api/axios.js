import axios from 'axios';

// Configuración base

axios.defaults.headers.common['Accept'] = 'application/json';

// Obtener token del localStorage
const token = localStorage.getItem('auth_token');
if (token) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

// Interceptor de respuesta
axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            // Eliminar token y redirigir al login
            localStorage.removeItem('auth_token');
            window.location.href = '/login';
        }
        return Promise.reject(error);
    }
);

export default axios;
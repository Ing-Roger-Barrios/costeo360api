// router/middleware.js
import { useAuthStore } from '../stores/auth';


// router/middleware.js
export const authMiddleware = async (to, from, next) => {
  const authStore = useAuthStore();
  
  const isAuthenticated = await authStore.checkAuth();
  
  if (to.meta.requiresAuth) {
    if (!isAuthenticated) {
      next('/login');
      return;
    }
    
    // Protección por roles
    if (to.meta.requiresAdmin && !authStore.isAdmin) {
      next(authStore.isUser ? '/my-licenses' : '/login');
      return;
    }
    
    if (to.meta.requiresUser && !authStore.isUser) {
      next(authStore.isAdmin ? '/' : '/login');
      return;
    }
  }
  
  if (to.meta.guest && isAuthenticated) {
    // Redirigir según rol
    if (authStore.isAdmin) {
      next('/dashboard');
    } else if (authStore.isUser) {
      next('/my-licenses');
    } else {
      next('/');
    }
    return;
  }
  
  next();
};
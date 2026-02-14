// stores/auth.js
import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: JSON.parse(localStorage.getItem('user')) || null,
        token: localStorage.getItem('token') || null,
        isAuthenticated: !!localStorage.getItem('token')
    }),
    getters: {
        // Verificar si es admin
        isAdmin: (state) => {
            if (!state.user || !state.user.roles) return false;
            return state.user.roles.some(role => 
                role.name === 'admin' || role.name === 'super_admin'
            );
        },
        
        // Verificar si es usuario normal
        isUser: (state) => {
            if (!state.user || !state.user.roles) return false;
            return state.user.roles.some(role => role.name === 'user');
        }
    },

    actions: {
        async login(credentials) {
            try {
                const response = await axios.post('/api/v1/auth/login', credentials);
                
                this.token = response.data.access_token;
                this.user = response.data.user;
                this.isAuthenticated = true;
                
                localStorage.setItem('token', this.token);
                localStorage.setItem('user', JSON.stringify(this.user));
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
                
                return response.data;
            } catch (error) {
                throw error;
            }
        },

        async logout() {
            try {
                if (this.token) {
                    await axios.post('/api/v1/auth/logout');
                }
            } catch (error) {
                console.error('Logout error:', error);
            } finally {
                this.user = null;
                this.token = null;
                this.isAuthenticated = false;
                
                localStorage.removeItem('token');
                localStorage.removeItem('user');
                delete axios.defaults.headers.common['Authorization'];
            }
        },

        async checkAuth() {
            if (this.token) {
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
                try {
                    const response = await axios.get('/api/v1/auth/me');
                    this.user = response.data.user;
                    this.isAuthenticated = true;
                    localStorage.setItem('user', JSON.stringify(this.user));
                    return true;
                } catch (error) {
                    this.logout();
                    return false;
                }
            }
            return false;
        }
    }
});
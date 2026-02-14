import { defineStore } from 'pinia';
import axios from 'axios';

export const useModulesStore = defineStore('modules', {
    state: () => ({
        modules: [],
        loading: false,
        error: null,
        currentModule: null,
        filters: {
            search: '',
            page: 1,
            per_page: 25
        },
        pagination: {
            current_page: 1,
            last_page: 1,
            per_page: 25,
            total: 0
        }
    }),

    getters: {
        activeModules: (state) => state.modules.filter(module => module.activo)
    },

    actions: {
        async fetchModules(filters = {}) {
            this.loading = true;
            this.error = null;
            
            try {
                const params = {
                    ...this.filters,
                    ...filters
                };

                const response = await axios.get('/api/v1/modules', { params });
                
                if (response.data.data) {
                    this.modules = response.data.data;
                    this.pagination = {
                        current_page: response.data.meta.current_page,
                        last_page: response.data.meta.last_page,
                        per_page: response.data.meta.per_page,
                        total: response.data.meta.total
                    };
                } else {
                    this.modules = response.data;
                    this.pagination = {
                        current_page: 1,
                        last_page: 1,
                        per_page: this.modules.length,
                        total: this.modules.length
                    };
                }
                
                this.filters = { ...this.filters, ...filters };
                
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al cargar los módulos';
                console.error('Error fetching modules:', error);
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async createModule(moduleData) {
            this.loading = true;
            this.error = null;
            
            try {
                const response = await axios.post('/api/v1/modules', moduleData);
                this.modules.unshift(response.data.data || response.data);
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al crear el módulo';
                console.error('Error creating module:', error);
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async updateModule(id, moduleData) {
            this.loading = true;
            this.error = null;
            
            try {
                const response = await axios.put(`/api/v1/modules/${id}`, moduleData);
                const index = this.modules.findIndex(m => m.id === id);
                if (index !== -1) {
                    this.modules[index] = response.data.data || response.data;
                }
                return response.data;
            } catch (error) {
                console.error('ERROR 422:', error.response?.data);
                this.error = error.response?.data?.message || 'Error al actualizar el módulo';
                throw error;
            }
            finally {
                this.loading = false;
            }
        },

        async deleteModule(id) {
            this.loading = true;
            this.error = null;
            
            try {
                await axios.delete(`/api/v1/modules/${id}`);
                this.modules = this.modules.filter(m => m.id !== id);
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al eliminar el módulo';
                console.error('Error deleting module:', error);
                throw error;
            } finally {
                this.loading = false;
            }
        },

        setCurrentModule(module) {
            this.currentModule = module;
        },

        clearCurrentModule() {
            this.currentModule = null;
        },

        resetFilters() {
            this.filters = {
                search: '',
                page: 1,
                per_page: 25
            };
            return this.fetchModules();
        },

        updateFilter(key, value) {
            this.filters[key] = value;
            return this.fetchModules();
        }
    }
});
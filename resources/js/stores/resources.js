import { defineStore } from 'pinia';
import axios from 'axios';

export const useResourcesStore = defineStore('resources', {
    state: () => ({
        resources: [],
        loading: false,
        error: null,
        currentResource: null,
        filters: {
            search: '',
            tipo: '',
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
        activeResources: (state) => state.resources.filter(resource => resource.activo),
        
        tiposDisponibles: () => [
            { value: 'Material', label: 'Material' },
            { value: 'ManoObra', label: 'ManoObra' },
            { value: 'Equipo', label: 'Equipo' }
        ]
    },

    actions: {
        async fetchResources(filters = {}) {
            this.loading = true;
            this.error = null;
            
            try {
                // Merge filters with defaults
                const params = {
                    ...this.filters,
                    ...filters
                };

                const response = await axios.get('/api/v1/recursos', { params });
                
                // Handle both paginated and non-paginated responses
                if (response.data.data) {
                    // Paginated response
                    this.resources = response.data.data;
                    this.pagination = {
                        current_page: response.data.meta.current_page,
                        last_page: response.data.meta.last_page,
                        per_page: response.data.meta.per_page,
                        total: response.data.meta.total
                    };
                } else {
                    // Non-paginated response
                    this.resources = response.data;
                    this.pagination = {
                        current_page: 1,
                        last_page: 1,
                        per_page: this.resources.length,
                        total: this.resources.length
                    };
                }
                
                // Update filters
                this.filters = { ...this.filters, ...filters };
                
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al cargar los recursos';
                console.error('Error fetching resources:', error);
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async createResource(resourceData) {
            this.loading = true;
            this.error = null;
            
            try {
                const response = await axios.post('/api/v1/recursos', resourceData);
                // Add to beginning of list
                this.resources.unshift(response.data.data || response.data);
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al crear el recurso';
                console.error('Error creating resource:', error);
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async updateResource(id, resourceData) {
            this.loading = true;
            this.error = null;
            
            try {
                const response = await axios.put(`/api/v1/recursos/${id}`, resourceData);
                const index = this.resources.findIndex(r => r.id === id);
                if (index !== -1) {
                    this.resources[index] = response.data.data || response.data;
                }
                return response.data;
            } catch (error) {
                console.error('ERROR 422 FULL:', error.response?.data);
                console.error('VALIDATION ERRORS:', error.response?.data?.errors);
                throw error;
                }
            finally {
                            this.loading = false;
                        }
                    },

        async deleteResource(id) {
            this.loading = true;
            this.error = null;
            
            try {
                await axios.delete(`/api/v1/recursos/${id}`);
                this.resources = this.resources.filter(r => r.id !== id);
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al eliminar el recurso';
                console.error('Error deleting resource:', error);
                throw error;
            } finally {
                this.loading = false;
            }
        },

        setCurrentResource(resource) {
            this.currentResource = resource;
        },

        clearCurrentResource() {
            this.currentResource = null;
        },

        async updatePricesBulk(prices) {
            this.loading = true;
            this.error = null;
            
            try {
                const response = await axios.post('/api/v1/recursos/bulk-update-prices', {
                    precios: prices
                });
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al actualizar precios';
                console.error('Error updating prices:', error);
                throw error;
            } finally {
                this.loading = false;
            }
        },

        // Reset filters and fetch
        resetFilters() {
            this.filters = {
                search: '',
                tipo: '',
                page: 1,
                per_page: 25
            };
            return this.fetchResources();
        },

        // Update single filter and fetch
        updateFilter(key, value) {
            this.filters[key] = value;
            return this.fetchResources();
        }
    }
});
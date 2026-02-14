import { defineStore } from 'pinia';
import axios from 'axios';

export const useVersionsStore = defineStore('versions', {
    state: () => ({
        versions: [],
        loading: false,
        error: null,
        currentVersion: null,
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
        activeVersions: (state) => state.versions.filter(version => version.activo)
    },

    actions: {
        async fetchVersions(filters = {}) {
        this.loading = true;
        this.error = null;
        
        try {
            const params = {
                ...this.filters,
                ...filters
            };

            const response = await axios.get('/api/v1/versions', { params });
            
            let versions = [];
            if (response.data.data) {
                versions = response.data.data;
            } else {
                versions = response.data;
            }
            
            // 👇 ORDENAR: activas primero, luego por fecha descendente
            /*versions.sort((a, b) => {
                // Si uno está activo y el otro no
                if (a.activo && !b.activo) return -1;
                if (!a.activo && b.activo) return 1;
                
                // Ambos activos o ambos inactivos: ordenar por fecha
                return new Date(a.fecha_publicacion) - new Date(b.fecha_publicacion);
            });*/
            
            this.versions = versions;
            this.pagination = {
                current_page: 1,
                last_page: 1,
                per_page: versions.length,
                total: versions.length
            };
            
            this.filters = { ...this.filters, ...filters };
            
        } catch (error) {
            this.error = error.response?.data?.message || 'Error al cargar las versiones';
            console.error('Error fetching versions:', error);
            throw error;
        } finally {
            this.loading = false;
        }
    },

        async createVersion(versionData) {
            this.loading = true;
            this.error = null;
            
            try {
                const response = await axios.post('/api/v1/versions', versionData);
                this.versions.unshift(response.data.data || response.data);
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al crear la versión';
                console.error('Error creating version:', error);
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async updateVersion(id, versionData) {
            this.loading = true;
            this.error = null;
            
            try {
                const response = await axios.put(`/api/v1/versions/${id}`, versionData);
                const index = this.versions.findIndex(v => v.id === id);
                if (index !== -1) {
                    this.versions[index] = response.data.data || response.data;
                }
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al actualizar la versión';
                console.error('Error updating version:', error);
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async deleteVersion(id) {
            this.loading = true;
            this.error = null;
            
            try {
                await axios.delete(`/api/v1/versions/${id}`);
                this.versions = this.versions.filter(v => v.id !== id);
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al eliminar la versión';
                console.error('Error deleting version:', error);
                throw error;
            } finally {
                this.loading = false;
            }
        },

        setCurrentVersion(version) {
            this.currentVersion = version;
        },

        clearCurrentVersion() {
            this.currentVersion = null;
        },

        resetFilters() {
            this.filters = {
                search: '',
                page: 1,
                per_page: 25
            };
            return this.fetchVersions();
        },

        updateFilter(key, value) {
            this.filters[key] = value;
            return this.fetchVersions();
        }
    }
});
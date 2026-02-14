import { defineStore } from 'pinia';
import axios from 'axios';

export const useRegionsStore = defineStore('regions', {
    state: () => ({
        regions: [],
        loading: false,
        error: null,
        currentRegion: null
    }),

    getters: {
        activeRegions: (state) => state.regions.filter(region => region.activo)
    },

    actions: {
        async fetchRegions() {
            this.loading = true;
            this.error = null;
            
            try {
                const response = await axios.get('/api/v1/regions');
                this.regions = response.data.data || response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al cargar las regiones';
                console.error('Error fetching regions:', error);
            } finally {
                this.loading = false;
            }
        },

        async createRegion(regionData) {
            this.loading = true;
            this.error = null;
            
            try {
                const response = await axios.post('/api/v1/regions', regionData);
                this.regions.push(response.data.data || response.data);
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al crear la región';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async updateRegion(id, regionData) {
            this.loading = true;
            this.error = null;
            
            try {
                const response = await axios.put(`/api/v1/regions/${id}`, regionData);
                const index = this.regions.findIndex(r => r.id === id);
                if (index !== -1) {
                    this.regions[index] = response.data.data || response.data;
                }
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al actualizar la región';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async deleteRegion(id) {
            this.loading = true;
            this.error = null;
            
            try {
                await axios.delete(`/api/v1/regions/${id}`);
                this.regions = this.regions.filter(r => r.id !== id);
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al eliminar la región';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        setCurrentRegion(region) {
            this.currentRegion = region;
        },

        clearCurrentRegion() {
            this.currentRegion = null;
        }
    }
});
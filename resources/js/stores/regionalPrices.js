import { defineStore } from 'pinia';
import axios from 'axios';

export const useRegionalPricesStore = defineStore('regionalPrices', {
    state: () => ({
        regionalPrices: [],
        loading: false,
        error: null,
        currentResource: null,
        regions: []
    }),

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

        async fetchRegionalPrices(resourceId) {
            this.loading = true;
            this.error = null;
            
            try {
                // Obtener precios regionales del recurso
                const response = await axios.get(`/api/v1/recursos/${resourceId}/precios-regionales`);
                this.regionalPrices = response.data.data || response.data;
                this.currentResource = resourceId;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al cargar precios regionales';
                console.error('Error fetching regional prices:', error);
            } finally {
                this.loading = false;
            }
        },

        async updateRegionalPrice(resourceId, regionId, priceData) {
            this.loading = true;
            this.error = null;
            
            try {
                const response = await axios.put(`/api/v1/recursos/${resourceId}/precios-regionales/${regionId}`, priceData);
                
                // Actualizar en el store
                const index = this.regionalPrices.findIndex(p => p.region_id === regionId);
                if (index !== -1) {
                    this.regionalPrices[index] = response.data.data || response.data;
                } else {
                    this.regionalPrices.push(response.data.data || response.data);
                }
                
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al actualizar precio regional';
                console.error('Error updating regional price:', error);
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async deleteRegionalPrice(resourceId, regionId) {
            this.loading = true;
            this.error = null;
            
            try {
                await axios.delete(`/api/v1/recursos/${resourceId}/precios-regionales/${regionId}`);
                this.regionalPrices = this.regionalPrices.filter(p => p.region_id !== regionId);
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al eliminar precio regional';
                console.error('Error deleting regional price:', error);
                throw error;
            } finally {
                this.loading = false;
            }
        },

        clearRegionalPrices() {
            this.regionalPrices = [];
            this.currentResource = null;
        }
    }
});
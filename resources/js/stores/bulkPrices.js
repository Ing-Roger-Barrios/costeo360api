import { defineStore } from 'pinia';
import axios from 'axios';

export const useBulkPricesStore = defineStore('bulkPrices', {
    state: () => ({
        resources: [],
        regions: [],
        loading: false,
        saving: false,
        error: null,
        versionActivaId: null,
        versionPublicadaId: null
    }),

    actions: {
        async fetchResourcesWithPrices() {
            this.loading = true;
            this.error = null;
            
            try {
                const response = await axios.get('/api/v1/resources/bulk-prices');
                this.resources = response.data.data;
                this.regions = response.data.regions || [];
                this.versionActivaId = response.data.version_activa_id;
                this.versionPublicadaId = response.data.version_publicada_id;
                
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al cargar los recursos';
                console.error('Error fetching resources with prices:', error);
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async savePrices(updates) {
            this.saving = true;
            this.error = null;
            
            try {
                const response = await axios.post('/api/v1/resources/bulk-update', {
                    updates: updates,
                    version_id: this.versionActivaId
                });
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al guardar los precios';
                console.error('Error saving prices:', error);
                throw error;
            } finally {
                this.saving = false;
            }
        }
    }
});
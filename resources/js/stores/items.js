import { defineStore } from 'pinia';
import axios from 'axios';

export const useItemsStore = defineStore('items', {
    state: () => ({
        items: [],
        loading: false,
        error: null,
        currentItem: null,
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
        activeItems: (state) => state.items.filter(item => item.activo)
    },

    actions: {
        async fetchItems(filters = {}) {
            this.loading = true;
            this.error = null;
            
            try {
                const params = {
                    ...this.filters,
                    ...filters
                };

                const response = await axios.get('/api/v1/items', { params });
                
                if (response.data.data) {
                    this.items = response.data.data;
                    this.pagination = {
                        current_page: response.data.meta.current_page,
                        last_page: response.data.meta.last_page,
                        per_page: response.data.meta.per_page,
                        total: response.data.meta.total
                    };
                } else {
                    this.items = response.data;
                    this.pagination = {
                        current_page: 1,
                        last_page: 1,
                        per_page: this.items.length,
                        total: this.items.length
                    };
                }
                
                this.filters = { ...this.filters, ...filters };
                
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al cargar los items';
                console.error('Error fetching items:', error);
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async createItem(itemData) {
            this.loading = true;
            this.error = null;
            
            try {
                const response = await axios.post('/api/v1/items', itemData);
                this.items.unshift(response.data.data || response.data);
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al crear el item';
                console.error('Error creating item:', error);
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async updateItem(id, itemData) {
            this.loading = true;
            this.error = null;
            
            try {
                const response = await axios.put(`/api/v1/items/${id}`, itemData);
                const index = this.items.findIndex(i => i.id === id);
                if (index !== -1) {
                    this.items[index] = response.data.data || response.data;
                }
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al actualizar el item';
                console.error('Error updating item:', error);
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async deleteItem(id) {
            this.loading = true;
            this.error = null;
            
            try {
                await axios.delete(`/api/v1/items/${id}`);
                this.items = this.items.filter(i => i.id !== id);
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al eliminar el item';
                console.error('Error deleting item:', error);
                throw error;
            } finally {
                this.loading = false;
            }
        },

        setCurrentItem(item) {
            this.currentItem = item;
        },

        clearCurrentItem() {
            this.currentItem = null;
        },

        resetFilters() {
            this.filters = {
                search: '',
                page: 1,
                per_page: 25
            };
            return this.fetchItems();
        },

        updateFilter(key, value) {
            this.filters[key] = value;
            return this.fetchItems();
        }
    }
});
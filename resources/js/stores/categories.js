import { defineStore } from 'pinia';
import axios from 'axios';

export const useCategoriesStore = defineStore('categories', {
    state: () => ({
        categories: [],
        loading: false,
        error: null,
        currentCategory: null,
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
        activeCategories: (state) => state.categories.filter(category => category.activo)
    },

    actions: {
        async fetchCategories(filters = {}) {
            this.loading = true;
            this.error = null;
            
            try {
                const params = {
                    ...this.filters,
                    ...filters
                };

                const response = await axios.get('/api/v1/categories', { params });
                
                if (response.data.data) {
                    this.categories = response.data.data;
                    this.pagination = {
                        current_page: response.data.meta.current_page,
                        last_page: response.data.meta.last_page,
                        per_page: response.data.meta.per_page,
                        total: response.data.meta.total
                    };
                } else {
                    this.categories = response.data;
                    this.pagination = {
                        current_page: 1,
                        last_page: 1,
                        per_page: this.categories.length,
                        total: this.categories.length
                    };
                }
                
                this.filters = { ...this.filters, ...filters };
                
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al cargar las categorías';
                console.error('Error fetching categories:', error);
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async createCategory(categoryData) {
            this.loading = true;
            this.error = null;
            
            try {
                const response = await axios.post('/api/v1/categories', categoryData);
                this.categories.unshift(response.data.data || response.data);
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al crear la categoría';
                console.error('Error creating category:', error);
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async updateCategory(id, categoryData) {
            this.loading = true;
            this.error = null;
            
            try {
                const response = await axios.put(`/api/v1/categories/${id}`, categoryData);
                const index = this.categories.findIndex(c => c.id === id);
                if (index !== -1) {
                    this.categories[index] = response.data.data || response.data;
                }
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al actualizar la categoría';
                console.error('Error updating category:', error);
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async deleteCategory(id) {
            this.loading = true;
            this.error = null;
            
            try {
                await axios.delete(`/api/v1/categories/${id}`);
                this.categories = this.categories.filter(c => c.id !== id);
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al eliminar la categoría';
                console.error('Error deleting category:', error);
                throw error;
            } finally {
                this.loading = false;
            }
        },

        setCurrentCategory(category) {
            this.currentCategory = category;
        },

        clearCurrentCategory() {
            this.currentCategory = null;
        },

        resetFilters() {
            this.filters = {
                search: '',
                page: 1,
                per_page: 25
            };
            return this.fetchCategories();
        },

        updateFilter(key, value) {
            this.filters[key] = value;
            return this.fetchCategories();
        }
    }
});
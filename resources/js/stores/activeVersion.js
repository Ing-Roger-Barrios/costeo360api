import { defineStore } from 'pinia';
import axios from 'axios';

export const useActiveVersionStore = defineStore('activeVersion', {
    state: () => ({
        currentVersion: null,      // Versión activa (trabajo)
        publishedVersion: null,   // 👈 AÑADIR ESTA LÍNEA
        loading: false,
        error: null
    }),

    getters: {
        isActiveVersionSet: (state) => !!state.currentVersion,
        isPublishedVersionSet: (state) => !!state.publishedVersion, // 👈 AÑADIR ESTA LÍNEA
        currentVersionId: (state) => state.currentVersion?.id || null,
        currentVersionName: (state) => state.currentVersion 
            ? `${state.currentVersion.version} - ${state.currentVersion.nombre}`
            : 'Sin versión activa',
        publishedVersionName: (state) => state.publishedVersion // 👈 CORREGIR NOMBRE
            ? `${state.publishedVersion.version} - ${state.publishedVersion.nombre}`
            : 'Sin versión publicada'
    },

    actions: {
        async fetchActiveVersion() {
            this.loading = true;
            try {
                const response = await axios.get('/api/v1/versions/active');
                this.currentVersion = response.data.data;
            } catch (error) {
                console.error('Error fetching active version:', error);
                this.currentVersion = null;
            } finally {
                this.loading = false;
            }
        },

        async fetchPublishedVersion() { // 👈 AÑADIR ESTE MÉTODO
            this.loading = true;
            try {
                const response = await axios.get('/api/v1/versions/published');
                this.publishedVersion = response.data.data;
            } catch (error) {
                console.error('Error fetching published version:', error);
                this.publishedVersion = null;
            } finally {
                this.loading = false;
            }
        },

        // Obtener ambas versiones
        async fetchAllVersionsInfo() {
            await Promise.all([
                this.fetchActiveVersion(),
                this.fetchPublishedVersion() // 👈 USAR EL NUEVO MÉTODO
            ]);
        },

        async setActiveVersion(version) {
            this.currentVersion = version;
        },

        clearActiveVersion() {
            this.currentVersion = null;
        },

        requireActiveVersion() {
            if (!this.currentVersion) {
                throw new Error('No hay una versión activa seleccionada');
            }
            return this.currentVersion;
        }
    }
});
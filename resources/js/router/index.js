import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { authMiddleware } from './middleware';

const routes = [
    {
        path: '/',
        name: 'landing',
        component: () => import('../views/LandingPage.vue')
    },
    {
        path: '/login',
        name: 'login',
        component: () => import('../views/auth/Login.vue'),
        meta: { guest: true }
    },
    {
        path: '/register',
        name: 'register',
        component: () => import('../views/auth/Register.vue'),
        meta: { guest: true }
    },
    {
        // 👇 Layout principal con Sidebar y Header
        path: '/dashboard',
        component: () => import('../views/dashboard/DashboardLayout.vue'), // Cambiado a Layout
        meta: { requiresAuth: true, requiresAdmin: true  },
        children: [
            {
                path: '',
                name: 'dashboard',
                component: () => import('../views/dashboard/Dashboard.vue')
            },
            {
                path: 'regions',
                name: 'regions',
                component: () => import('../views/regions/RegionsView.vue')
            },
            {
                path: 'resources',
                name: 'resources',
                component: () => import('../views/resources/ResourcesView.vue')
            },
            {
                path: 'items',
                name: 'items',
                component: () => import('../views/items/ItemsView.vue')
            },
            {
                path: 'modules',
                name: 'modules',
                component: () => import('../views/modules/ModulesView.vue')
            },
            {
                path: 'categories',
                name: 'categories',
                component: () => import('../views/categories/CategoriesView.vue')
            },
            {
                path: 'versions',
                name: 'versions',
                component: () => import('../views/versions/VersionsView.vue')
            },
            {
                path: 'resources/:resourceId/regional-prices',
                name: 'resource-regional-prices',
                component: () => import('../views/resources/ResourceRegionalPrices.vue'),
                props: true
            },
            {
                path: '/resources/bulk-prices',
                name: 'bulk-prices',
                component: () => import('../views/resources/BulkPriceManager.vue'),
                meta: { requiresAuth: true }
            },
            {
                path: '/import/ddp',
                name: 'import-ddp',
                component: () => import('../views/import/DdpImport.vue'),
                meta: { requiresAuth: true }
            },
            {
                path: '/categories/:id/import-modules',
                name: 'import-modules',
                component: () => import('../views/import/ImportModules.vue'),
                props: true
            }
        ]
    },
    // Ruta para usuarios normales (licencias)
    {
        path: '/licenses',
        component: () => import('../components/layout/LicensesLayout.vue'),
        meta: { requiresAuth: true, requiresUser: true },
        children: [
        {
            path: '',
            name: 'my-licenses',
            component: () => import('../views/licenses/MyLicenses.vue')
        },
        {
            path: 'purchase',
            name: 'purchase-license',
            component: () => import('../views/licenses/PurchaseLicense.vue')
        },
        {
            path: ':type/payment',
            name: 'license-payment',
            component: () => import('../views/licenses/LicensePayment.vue'),
            props: true
        },
        {
            path: 'success',
            name: 'license-success',
            component: () => import('../views/licenses/LicenseSuccess.vue')
        }
        ]
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

// Aplicar middleware
router.beforeEach(authMiddleware);

// Guardias de navegación
router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore();
    
    // Verificar autenticación
    if (to.meta.requiresAuth) {
        const isAuthenticated = await authStore.checkAuth();
        if (!isAuthenticated) {
            next('/login');
            return;
        }
    }
    
    // Verificar si está autenticado y va a login
    if (to.meta.guest && authStore.isAuthenticated) {
        next('/');
        return;
    }
    
    next();
});

export default router;
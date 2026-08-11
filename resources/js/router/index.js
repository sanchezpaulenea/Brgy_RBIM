import { createRouter, createWebHistory } from 'vue-router';
import { useAuth } from '@/composables/useAuth';

const routes = [
    {
        path: '/',
        redirect: '/login',
    },
    {
        path: '/login',
        name: 'login',
        component: () => import('@/pages/auth/LoginPage.vue'),
        meta: { guestOnly: true },
    },
    {
        path: '/change-password',
        name: 'change-password',
        component: () => import('@/pages/auth/ChangePasswordPage.vue'),
        meta: { requiresAuth: true, allowWhileMustChangePassword: true },
    },
    {
        path: '/dashboard',
        name: 'dashboard',
        component: () => import('@/pages/DashboardPage.vue'),
        meta: { requiresAuth: true },
    },
    {
        path: '/:pathMatch(.*)*',
        redirect: '/login',
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to) => {
    const auth = useAuth();

    if (!auth.initialized.value) {
        await auth.initialize();
    }

    if (to.meta.requiresAuth && !auth.isAuthenticated.value) {
        return {
            name: 'login',
            query: { redirect: to.fullPath },
        };
    }

    if (to.meta.guestOnly && auth.isAuthenticated.value) {
        if (auth.mustChangePassword.value) {
            return { name: 'change-password' };
        }

        return { name: 'dashboard' };
    }

    if (
        auth.isAuthenticated.value
        && auth.mustChangePassword.value
        && !to.meta.allowWhileMustChangePassword
    ) {
        return { name: 'change-password' };
    }

    return true;
});

export default router;

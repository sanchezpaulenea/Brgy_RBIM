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
        path: '/personnel',
        name: 'personnel',
        component: () => import('@/pages/personnel/PersonnelPage.vue'),
        meta: {
            requiresAuth: true,
            requiresPermissions: ['personnel.view', 'personnel.create', 'personnel.update'],
        },
    },
    {
        path: '/users',
        name: 'users',
        component: () => import('@/pages/users/UsersPage.vue'),
        meta: {
            requiresAuth: true,
            requiresPermissions: ['user.view'],
        },
    },
    {
        path: '/settings',
        name: 'settings',
        component: () => import('@/pages/settings/SettingsPage.vue'),
        meta: {
            requiresAuth: true,
            requiresPermissions: ['setting.view', 'setting.update'],
        },
    },
    {
        path: '/settings/lookups/personnel-positions',
        name: 'lookup-personnel-positions',
        component: () => import('@/pages/settings/LookupsPage.vue'),
        meta: {
            requiresAuth: true,
            requiresSuperAdmin: true,
            requiresPermissions: ['pposition.view'],
            lookup: 'personnel-positions',
        },
    },
    {
        path: '/settings/lookups/user-roles',
        name: 'lookup-user-roles',
        component: () => import('@/pages/settings/LookupsPage.vue'),
        meta: {
            requiresAuth: true,
            requiresSuperAdmin: true,
            requiresPermissions: ['userrole.view'],
            lookup: 'user-roles',
        },
    },
    {
        path: '/settings/lookups/role-permissions',
        name: 'lookup-role-permissions',
        component: () => import('@/pages/settings/LookupsPage.vue'),
        meta: {
            requiresAuth: true,
            requiresSuperAdmin: true,
            requiresPermissions: ['userrole.view'],
            lookup: 'role-permissions',
        },
    },
    {
        path: '/settings/audit-logs',
        name: 'audit-logs',
        component: () => import('@/pages/logs/LogsPage.vue'),
        meta: {
            requiresAuth: true,
            requiresSystemAdministrator: true,
            logType: 'audit',
        },
    },
    {
        path: '/settings/user-logs',
        name: 'user-logs',
        component: () => import('@/pages/logs/LogsPage.vue'),
        meta: {
            requiresAuth: true,
            requiresPermissions: ['userlog.view'],
            logType: 'login',
        },
    },
    {
        path: '/settings/logs',
        redirect: { name: 'audit-logs' },
    },
    {
        path: '/lookups',
        redirect: { name: 'lookup-personnel-positions' },
    },
    {
        path: '/forbidden',
        name: 'forbidden',
        component: () => import('@/pages/ForbiddenPage.vue'),
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

    if (!auth.canAccessRoute(to.meta)) {
        return { name: 'dashboard' };
    }

    return true;
});

export default router;

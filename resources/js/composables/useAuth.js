import { computed, ref } from 'vue';
import { ROLES } from '@/constants/roles';
import * as authService from '@/services/authService';

const user = ref(null);
const roles = ref([]);
const permissions = ref([]);
const initialized = ref(false);
const loading = ref(false);

function asStringList(value) {
    if (!value) {
        return [];
    }

    if (typeof value === 'string') {
        return [value];
    }

    if (Array.isArray(value)) {
        return value.flatMap((item) => asStringList(item)).filter(Boolean);
    }

    if (typeof value === 'object') {
        if (typeof value.permission === 'string') {
            return [value.permission];
        }

        if (typeof value.role_name === 'string') {
            return [value.role_name];
        }

        return Object.values(value).flatMap((item) => asStringList(item)).filter(Boolean);
    }

    return [];
}

function setSession(data) {
    user.value = data.user;
    roles.value = asStringList(data.roles);
    permissions.value = asStringList(data.permissions);
}

function clearSession() {
    user.value = null;
    roles.value = [];
    permissions.value = [];
}

export function useAuth() {
    const isAuthenticated = computed(() => user.value !== null);
    const mustChangePassword = computed(() => user.value?.must_change_password === true);

    async function initialize() {
        if (initialized.value) {
            return;
        }

        try {
            const data = await authService.fetchMe();
            setSession(data);
        } catch {
            clearSession();
        } finally {
            initialized.value = true;
        }
    }

    async function login(credentials) {
        loading.value = true;

        try {
            const data = await authService.login(credentials);
            setSession(data);

            return data;
        } finally {
            loading.value = false;
        }
    }

    async function logout() {
        loading.value = true;

        try {
            await authService.logout();
        } catch {
            // Session may already be invalid; still clear local state.
        } finally {
            clearSession();
            loading.value = false;
        }
    }

    async function changePassword(payload) {
        loading.value = true;

        try {
            await authService.changePassword(payload);
            clearSession();
        } finally {
            loading.value = false;
        }
    }

    async function updateAvatar(payload) {
        const data = await authService.updateAvatar(payload);
        setSession(data);

        return data;
    }

    function hasPermission(permission) {
        if (roles.value.includes(ROLES.SUPER_ADMIN)) {
            return true;
        }

        return permissions.value.includes(permission);
    }

    function hasRole(roleName) {
        return roles.value.includes(roleName);
    }

    const isSuperAdmin = computed(() => roles.value.includes(ROLES.SUPER_ADMIN));

    const isSystemAdministrator = computed(() => (
        isSuperAdmin.value || roles.value.includes(ROLES.ADMIN)
    ));

    const isGuest = computed(() => (
        roles.value.includes(ROLES.GUEST) && !isSystemAdministrator.value
    ));

    const isEncoder = computed(() => roles.value.includes(ROLES.ENCODER));

    function hasAnyPermission(requiredPermissions) {
        return requiredPermissions.some((permission) => hasPermission(permission));
    }

    function canAccessRoute(meta = {}) {
        if (isSuperAdmin.value) {
            return true;
        }

        if (meta.requiresSuperAdmin) {
            return false;
        }

        if (meta.requiresSystemAdministrator && !isSystemAdministrator.value) {
            return false;
        }

        if (Array.isArray(meta.requiresPermissions) && meta.requiresPermissions.length > 0) {
            return hasAnyPermission(meta.requiresPermissions);
        }

        return true;
    }

    return {
        user,
        roles,
        permissions,
        initialized,
        loading,
        isAuthenticated,
        mustChangePassword,
        isGuest,
        isEncoder,
        isSuperAdmin,
        isSystemAdministrator,
        initialize,
        login,
        logout,
        changePassword,
        updateAvatar,
        hasPermission,
        hasAnyPermission,
        hasRole,
        canAccessRoute,
    };
}

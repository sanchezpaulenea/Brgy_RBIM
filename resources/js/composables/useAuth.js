import { computed, ref } from 'vue';
import { ROLES } from '@/constants/roles';
import * as authService from '@/services/authService';

const user = ref(null);
const roles = ref([]);
const permissions = ref([]);
const initialized = ref(false);
const loading = ref(false);

function setSession(data) {
    user.value = data.user;
    roles.value = data.roles ?? [];
    permissions.value = data.permissions ?? [];
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

            if (user.value) {
                user.value = {
                    ...user.value,
                    must_change_password: false,
                };
            }
        } finally {
            loading.value = false;
        }
    }

    function hasPermission(permission) {
        return permissions.value.includes(permission);
    }

    function hasRole(roleName) {
        return roles.value.includes(roleName);
    }

    const isSystemAdministrator = computed(() => (
        roles.value.includes(ROLES.SUPER_ADMIN)
        || roles.value.includes(ROLES.ADMIN)
    ));

    const isGuest = computed(() => (
        roles.value.includes(ROLES.GUEST) && !isSystemAdministrator.value
    ));

    return {
        user,
        roles,
        permissions,
        initialized,
        loading,
        isAuthenticated,
        mustChangePassword,
        isGuest,
        isSystemAdministrator,
        initialize,
        login,
        logout,
        changePassword,
        hasPermission,
        hasRole,
    };
}

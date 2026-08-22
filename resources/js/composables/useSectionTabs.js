import { computed } from 'vue';
import { useAuth } from '@/composables/useAuth';

/**
 * Tab sets for the two management sections. Personnel Position, User Role, and
 * Role Permission are reached from the module they belong to instead of the
 * Settings menu.
 */
export function useSectionTabs() {
    const { hasPermission, hasAnyPermission, isSuperAdmin } = useAuth();

    const personnelTabs = computed(() => [
        hasAnyPermission(['personnel.view', 'personnel.create', 'personnel.update'])
            ? { name: 'personnel', label: 'Barangay Personnel' }
            : null,
        isSuperAdmin.value && hasPermission('pposition.view')
            ? { name: 'personnel-positions', label: 'Personnel Position' }
            : null,
    ].filter(Boolean));

    const userTabs = computed(() => [
        hasPermission('user.view')
            ? { name: 'users', label: 'User Account' }
            : null,
        isSuperAdmin.value && hasPermission('userrole.view')
            ? { name: 'user-roles', label: 'User Role' }
            : null,
        isSuperAdmin.value && hasPermission('userrole.view')
            ? { name: 'role-permissions', label: 'Role Permission' }
            : null,
    ].filter(Boolean));

    return { personnelTabs, userTabs };
}

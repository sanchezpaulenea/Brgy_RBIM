import { computed } from 'vue';
import { useAuth } from '@/composables/useAuth';

/**
 * Tab sets for management sections. Lookup tables (Personnel Position, Street,
 * Nationality, Ethnicity, Religion, User Role, and Role Permission) are reached
 * from the module they belong to instead of the Settings menu.
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

    // Super Admin is not an operational role for these modules; keep the pages hidden.
    const householdTabs = computed(() => {
        if (isSuperAdmin.value) {
            return [];
        }

        return [
            hasPermission('household.view')
                ? { name: 'households', label: 'View Households' }
                : null,
            hasPermission('householdassessment.view')
                ? { name: 'household-assessments', label: 'Assessment' }
                : null,
            hasPermission('household.create')
                ? { name: 'household-register', label: 'Register Household' }
                : null,
            hasPermission('street.view')
                ? { name: 'streets', label: 'Street' }
                : null,
        ].filter(Boolean);
    });

    const residentTabs = computed(() => {
        if (isSuperAdmin.value) {
            return [];
        }

        return [
            hasPermission('resident.view')
                ? { name: 'residents', label: 'View Residents' }
                : null,
            hasPermission('resident.create')
                ? { name: 'resident-register', label: 'Register Resident' }
                : null,
            hasPermission('nationality.view')
                ? { name: 'nationalities', label: 'Nationality' }
                : null,
            hasPermission('ethnicity.view')
                ? { name: 'ethnicities', label: 'Ethnicity' }
                : null,
            hasPermission('religion.view')
                ? { name: 'religions', label: 'Religion' }
                : null,
        ].filter(Boolean);
    });

    return { personnelTabs, userTabs, householdTabs, residentTabs };
}

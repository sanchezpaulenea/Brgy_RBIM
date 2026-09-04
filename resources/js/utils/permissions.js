/**
 * Readable wording for each permission slug stored in the database, grouped so
 * the Role Permission view reads as a list of capabilities rather than raw keys.
 */
const PERMISSION_LABELS = {
    'auditlog.view': { group: 'System logs', label: 'View the audit log' },
    'user.create': { group: 'User accounts', label: 'Create user accounts' },
    'user.view': { group: 'User accounts', label: 'View user accounts' },
    'user.delete': { group: 'User accounts', label: 'Delete user accounts' },
    'user.resetpassword': { group: 'User accounts', label: 'Reset account passwords' },
    'user.updatestatus': { group: 'User accounts', label: 'Enable or disable accounts' },
    'user.changepassword': { group: 'Own account', label: 'Change own password' },
    'userrole.create': { group: 'User roles', label: 'Assign roles to accounts' },
    'userrole.view': { group: 'User roles', label: 'View role assignments and permissions' },
    'userrole.updatestatus': { group: 'User roles', label: 'Enable or disable role assignments' },
    'personnel.create': { group: 'Barangay personnel', label: 'Create personnel records' },
    'personnel.view': { group: 'Barangay personnel', label: 'View personnel records' },
    'personnel.update': { group: 'Barangay personnel', label: 'Update personnel records' },
    'pposition.create': { group: 'Personnel positions', label: 'Create personnel positions' },
    'pposition.view': { group: 'Personnel positions', label: 'View personnel positions' },
    'pposition.delete': { group: 'Personnel positions', label: 'Delete personnel positions' },
    'setting.view': { group: 'System settings', label: 'View system settings' },
    'setting.update': { group: 'System settings', label: 'Update system settings' },
    'userlog.view': { group: 'System logs', label: 'View the user log' },
    'street.view': { group: 'Streets', label: 'View streets' },
    'street.create': { group: 'Streets', label: 'Create streets' },
    'street.delete': { group: 'Streets', label: 'Delete streets' },
    'household.view': { group: 'Households', label: 'View households' },
    'household.create': { group: 'Households', label: 'Register households' },
    'household.update': { group: 'Households', label: 'Update households' },
    'householdassessment.view': { group: 'Household assessments', label: 'View household assessments' },
    'householdassessment.create': { group: 'Household assessments', label: 'Create household assessments' },
    'householdassessment.updatestatus': { group: 'Household assessments', label: 'Update household assessment status' },
    'nationality.view': { group: 'Nationalities', label: 'View nationalities' },
    'nationality.create': { group: 'Nationalities', label: 'Create nationalities' },
    'nationality.delete': { group: 'Nationalities', label: 'Delete nationalities' },
    'ethnicity.view': { group: 'Ethnicities', label: 'View ethnicities' },
    'ethnicity.create': { group: 'Ethnicities', label: 'Create ethnicities' },
    'ethnicity.delete': { group: 'Ethnicities', label: 'Delete ethnicities' },
    'resident.view': { group: 'Residents', label: 'View resident records' },
    'resident.create': { group: 'Residents', label: 'Create resident records' },
    'resident.update': { group: 'Residents', label: 'Update resident records' },
    'religion.view': { group: 'Religions', label: 'View religions' },
    'religion.create': { group: 'Religions', label: 'Create religions' },
    'religion.delete': { group: 'Religions', label: 'Delete religions' },
};

const OTHER_GROUP = 'Other';

/**
 * Shared group order for every role so missing groups are skipped without
 * reshuffling the ones that remain.
 */
const PERMISSION_GROUP_ORDER = [
    'Own account',
    'System logs',
    'System settings',
    'User accounts',
    'User roles',
    'Barangay personnel',
    'Personnel positions',
    'Households',
    'Household assessments',
    'Residents',
    'Streets',
    'Nationalities',
    'Ethnicities',
    'Religions',
];

export function permissionLabel(permission) {
    const known = PERMISSION_LABELS[permission];

    if (known) {
        return known.label;
    }

    return String(permission)
        .split('.')
        .join(' ')
        .replace(/[_-]+/g, ' ')
        .replace(/\b\w/g, (char) => char.toUpperCase());
}

export function permissionGroup(permission) {
    return PERMISSION_LABELS[permission]?.group ?? OTHER_GROUP;
}

/**
 * @param {string[]} permissions
 * @returns {{ group: string, items: string[] }[]}
 */
export function groupPermissions(permissions) {
    const groups = new Map();

    permissions.forEach((permission) => {
        const group = permissionGroup(permission);
        const items = groups.get(group) ?? [];
        items.push(permissionLabel(permission));
        groups.set(group, items);
    });

    return [...groups.entries()]
        .map(([group, items]) => ({ group, items: items.sort((a, b) => a.localeCompare(b)) }))
        .sort((a, b) => groupOrderIndex(a.group) - groupOrderIndex(b.group));
}

function groupOrderIndex(group) {
    if (group === OTHER_GROUP) {
        return Number.MAX_SAFE_INTEGER;
    }

    const index = PERMISSION_GROUP_ORDER.indexOf(group);

    return index === -1 ? PERMISSION_GROUP_ORDER.length : index;
}

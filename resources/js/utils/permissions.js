/**
 * Readable wording for each permission slug stored in the database, grouped so
 * the Role Permission view reads as a list of capabilities rather than raw keys.
 */
const PERMISSION_LABELS = {
    'user.create': { group: 'User accounts', label: 'Create user accounts' },
    'user.view': { group: 'User accounts', label: 'View user accounts' },
    'user.delete': { group: 'User accounts', label: 'Delete user accounts' },
    'user.resetpassword': { group: 'User accounts', label: 'Reset account passwords' },
    'user.updatestatus': { group: 'User accounts', label: 'Enable or disable accounts' },
    'user.changepassword': { group: 'Own account', label: 'Change own password' },
    'userrole.create': { group: 'Roles', label: 'Assign roles to accounts' },
    'userrole.view': { group: 'Roles', label: 'View role assignments and permissions' },
    'userrole.updatestatus': { group: 'Roles', label: 'Enable or disable role assignments' },
    'personnel.create': { group: 'Barangay personnel', label: 'Create personnel records' },
    'personnel.view': { group: 'Barangay personnel', label: 'View personnel records' },
    'personnel.update': { group: 'Barangay personnel', label: 'Update personnel records' },
    'pposition.create': { group: 'Personnel positions', label: 'Create personnel positions' },
    'pposition.view': { group: 'Personnel positions', label: 'View personnel positions' },
    'pposition.delete': { group: 'Personnel positions', label: 'Delete personnel positions' },
    'setting.view': { group: 'System settings', label: 'View system settings' },
    'setting.update': { group: 'System settings', label: 'Update system settings' },
    'userlog.view': { group: 'System logs', label: 'View the user log' },
};

const OTHER_GROUP = 'Other';

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
        .sort((a, b) => {
            if (a.group === OTHER_GROUP) {
                return 1;
            }

            if (b.group === OTHER_GROUP) {
                return -1;
            }

            return a.group.localeCompare(b.group);
        });
}

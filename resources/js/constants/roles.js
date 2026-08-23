export const ROLES = {
    ENCODER: 'Encoder',
    ADMIN: 'Admin',
    SUPER_ADMIN: 'Super Admin',
    GUEST: 'Guest',
};

export const SYSTEM_ADMINISTRATOR_ROLES = [
    ROLES.ADMIN,
    ROLES.SUPER_ADMIN,
];

export const STAFF_ROLES = [
    ROLES.SUPER_ADMIN,
    ROLES.ADMIN,
    ROLES.ENCODER,
];

export const PERSONNEL_STATUSES = [
    { id: 1, label: 'Active' },
    { id: 2, label: 'Inactive' },
    { id: 3, label: 'Resigned' },
    { id: 4, label: 'Retired' },
    { id: 5, label: 'On Leave' },
];

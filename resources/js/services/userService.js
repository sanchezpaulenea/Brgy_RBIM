import http from '@/services/http';

export async function fetchUsers() {
    const { data } = await http.get('/users');

    return data.users;
}

export async function fetchUser(userId) {
    const { data } = await http.get(`/users/${userId}`);

    return data.user;
}

export async function fetchCreateOptions() {
    const { data } = await http.get('/users/create-options');

    return data;
}

export async function createUser(payload) {
    const { data } = await http.post('/users', payload);

    return data.user;
}

export async function updateUserStatus(userId, userStatusId) {
    const { data } = await http.patch(`/users/${userId}/status`, {
        user_status_id: userStatusId,
    });

    return data.user;
}

export async function resetUserPassword(userId) {
    const { data } = await http.post(`/users/${userId}/reset-password`);

    return data.user;
}

export async function fetchUserRoles(userId) {
    const { data } = await http.get(`/users/${userId}/roles`);

    return data.roles;
}

export async function assignUserRole(userId, roleId) {
    const { data } = await http.post(`/users/${userId}/roles`, {
        role_id: roleId,
    });

    return data.user_role;
}

export async function updateUserRoleStatus(userRoleId, enable) {
    const { data } = await http.patch(`/user-roles/${userRoleId}/status`, {
        enable,
    });

    return data.user_role;
}

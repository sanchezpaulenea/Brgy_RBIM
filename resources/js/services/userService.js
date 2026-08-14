import http from '@/services/http';

export async function fetchUsers() {
    const { data } = await http.get('/users');

    return data.users;
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

export async function deleteUser(userId) {
    await http.delete(`/users/${userId}`);
}

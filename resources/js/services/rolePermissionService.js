import http from '@/services/http';

export async function fetchRolePermissions() {
    const { data } = await http.get('/role-permissions');

    return data.roles;
}

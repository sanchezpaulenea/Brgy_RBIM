import http from '@/services/http';

export const LOOKUP_TYPES = [
    { value: 'personnel-position', label: 'Personnel Position', writable: true },
    { value: 'personnel-status', label: 'Personnel Status', writable: false },
    { value: 'user-status', label: 'User Status', writable: false },
    { value: 'login-status', label: 'Login Status', writable: false },
    { value: 'action', label: 'Action', writable: false },
    { value: 'permission', label: 'Permission', writable: false },
];

export async function fetchLookups(type) {
    const { data } = await http.get(`/lookups/${type}`);

    return data.items;
}

export async function createLookup(type, payload) {
    const { data } = await http.post(`/lookups/${type}`, payload);

    return data.item;
}

export async function deleteLookup(type, id) {
    await http.delete(`/lookups/${type}/${id}`);
}

import http from '@/services/http';

export const LOOKUP_TYPES = [
    { value: 'personnel-position', label: 'Personnel Position', writable: true },
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

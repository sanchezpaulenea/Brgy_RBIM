import http from '@/services/http';

export async function fetchResidents(filters = {}) {
    const params = {};

    if (filters.household_id) {
        params.household_id = filters.household_id;
    }

    if (filters.resident_status_id) {
        params.resident_status_id = filters.resident_status_id;
    }

    const { data } = await http.get('/residents', { params });

    return data.items;
}

export async function fetchResident(id) {
    const { data } = await http.get(`/residents/${id}`);

    return data.item;
}

export async function createResident(payload) {
    const { data } = await http.post('/residents', payload);

    return data.item;
}

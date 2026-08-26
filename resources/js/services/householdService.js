import http from '@/services/http';

export async function fetchLocationProfile() {
    const { data } = await http.get('/location-profile');

    return data.location;
}

export async function createHousehold(payload) {
    const { data } = await http.post('/households', payload);

    return data.item;
}

export async function createResident(payload) {
    const { data } = await http.post('/residents', payload);

    return data.item;
}

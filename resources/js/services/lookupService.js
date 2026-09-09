import http from '@/services/http';

export async function fetchPersonnelPositions() {
    const { data } = await http.get('/personnel-positions');

    return data.items;
}

export async function createPersonnelPosition(payload) {
    const { data } = await http.post('/personnel-positions', payload);

    return data.item;
}

export async function deletePersonnelPosition(id) {
    await http.delete(`/personnel-positions/${id}`);
}

export async function createDisability(payload) {
    const { data } = await http.post('/disabilities', payload);

    return data.item;
}

export async function fetchLookup(slug) {
    const { data } = await http.get(`/lookups/${slug}`);

    return data.items;
}

export async function fetchStreets() {
    const { data } = await http.get('/streets');

    return data.items;
}

export async function createStreet(payload) {
    const { data } = await http.post('/streets', payload);

    return data.item;
}

export async function deleteStreet(id) {
    await http.delete(`/streets/${id}`);
}

export async function fetchNationalities() {
    const { data } = await http.get('/nationalities');

    return data.items;
}

export async function createNationality(payload) {
    const { data } = await http.post('/nationalities', payload);

    return data.item;
}

export async function deleteNationality(id) {
    await http.delete(`/nationalities/${id}`);
}

export async function fetchEthnicities() {
    const { data } = await http.get('/ethnicities');

    return data.items;
}

export async function createEthnicity(payload) {
    const { data } = await http.post('/ethnicities', payload);

    return data.item;
}

export async function deleteEthnicity(id) {
    await http.delete(`/ethnicities/${id}`);
}

export async function fetchReligions() {
    const { data } = await http.get('/religions');

    return data.items;
}

export async function createReligion(payload) {
    const { data } = await http.post('/religions', payload);

    return data.item;
}

export async function deleteReligion(id) {
    await http.delete(`/religions/${id}`);
}

export async function fetchUserStatuses() {
    const { data } = await http.get('/user-statuses');

    return data.items;
}

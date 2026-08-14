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

export async function fetchUserStatuses() {
    const { data } = await http.get('/user-statuses');

    return data.items;
}

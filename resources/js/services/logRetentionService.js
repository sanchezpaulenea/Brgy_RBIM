import http from '@/services/http';

export async function fetchLogRetention() {
    const { data } = await http.get('/log-retention');

    return data;
}

export async function updateLogRetention(payload) {
    const { data } = await http.put('/log-retention', payload);

    return data;
}

export async function previewArchive(log) {
    const { data } = await http.get('/log-retention/preview', { params: { log } });

    return data;
}

export async function archiveLogs(log) {
    const { data } = await http.post('/log-retention/archive', { log });

    return data;
}

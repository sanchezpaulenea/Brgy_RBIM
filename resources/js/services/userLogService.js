import http from '@/services/http';

export async function fetchUserLogs(params = {}) {
    const { data } = await http.get('/user-logs', { params });

    return data.logs;
}

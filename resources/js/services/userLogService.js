import http from '@/services/http';

export async function fetchUserLogs() {
    const { data } = await http.get('/user-logs');

    return data.logs;
}

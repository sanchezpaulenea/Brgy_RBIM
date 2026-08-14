import http from '@/services/http';

export async function fetchAuditLogs(params = {}) {
    const { data } = await http.get('/audit-logs', { params });

    return data.logs;
}

export async function fetchLoginLogs() {
    const { data } = await http.get('/auth/logs');

    return data.logs;
}

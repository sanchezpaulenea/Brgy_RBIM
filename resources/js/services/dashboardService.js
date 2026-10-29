import http from '@/services/http';

export async function fetchDashboardStatistics(params = {}) {
    const { data } = await http.get('/dashboard/statistics', { params });

    return data;
}

export async function fetchDashboardOverview(params = {}) {
    const { data } = await http.get('/dashboard/statistics/overview', { params });

    return data;
}

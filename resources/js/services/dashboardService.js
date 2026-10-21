import http from '@/services/http';

export async function fetchDashboardStatistics() {
    const { data } = await http.get('/dashboard/statistics');

    return data;
}

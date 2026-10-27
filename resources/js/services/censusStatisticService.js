import http from '@/services/http';

export async function fetchCensusTiles() {
    const { data } = await http.get('/dashboard/tiles');

    return data;
}

export async function fetchCensusStatistic(statId, params) {
    const { data } = await http.get(`/dashboard/stats/${statId}`, { params });

    return data;
}

export async function exportCensusStatistic(statId, params) {
    const response = await http.get(`/dashboard/stats/${statId}/export`, {
        params,
        responseType: 'blob',
    });

    const disposition = response.headers?.['content-disposition'] ?? '';
    const match = /filename="?([^"]+)"?/i.exec(disposition);
    const filename = match?.[1] ?? `${statId}.csv`;
    const url = URL.createObjectURL(response.data);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(url);
}

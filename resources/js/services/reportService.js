import http, { extractErrorMessage } from '@/services/http';

export async function fetchReportCategories() {
    const { data } = await http.get('/reports/categories');

    return data.categories ?? [];
}

export async function fetchReportOptions(category, filter, search = '', all = false) {
    const { data } = await http.get('/reports/options', {
        params: {
            category,
            filter,
            search: search || undefined,
            all: all ? 1 : undefined,
        },
    });

    return data.items ?? [];
}

export async function previewReport(payload) {
    const { data } = await http.post('/reports/preview', payload);

    return data;
}

export async function exportReport(payload) {
    const response = await http.post('/reports/export', payload, {
        responseType: 'blob',
    });

    const disposition = response.headers?.['content-disposition'] ?? '';
    const match = /filename="?([^"]+)"?/i.exec(disposition);
    const filename = match?.[1] ?? `report.${payload.format === 'pdf' ? 'pdf' : 'xlsx'}`;
    const url = URL.createObjectURL(response.data);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(url);
}

export async function reportErrorMessage(error, fallback) {
    const data = error?.response?.data;

    if (data instanceof Blob) {
        try {
            const parsed = JSON.parse(await data.text());
            const firstError = parsed?.errors
                ? Object.values(parsed.errors).flat().find((message) => typeof message === 'string' && message !== '')
                : null;

            return firstError || parsed?.message || fallback;
        } catch {
            return fallback;
        }
    }

    return extractErrorMessage(error, fallback);
}

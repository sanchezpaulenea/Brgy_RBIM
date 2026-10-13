import http, { extractErrorMessage } from '@/services/http';

export async function fetchCertificateTypes() {
    const { data } = await http.get('/certificates/types');

    return data.types ?? [];
}

export async function searchCertificateResidents(search) {
    const { data } = await http.get('/certificates/residents', {
        params: { search },
    });

    return data.residents ?? [];
}

export async function fetchCertificateHistory(residentId, page = 1) {
    const { data } = await http.get(`/certificates/residents/${residentId}/history`, {
        params: { page },
    });

    return data;
}

export async function previewCertificate(payload) {
    const { data } = await http.post('/certificates/preview', payload);

    return data;
}

export async function issueCertificate(payload) {
    const response = await http.post('/certificates', payload, {
        responseType: 'blob',
    });

    return {
        blob: response.data,
        certificateId: response.headers?.['x-certificate-id'] ?? null,
        filename: filenameFromDisposition(response.headers?.['content-disposition']),
    };
}

export async function fetchSignatureBlob() {
    const response = await http.get('/certificates/signature', {
        responseType: 'blob',
    });

    return response.data;
}

export async function certificateErrorMessage(error, fallback) {
    const data = error?.response?.data;

    if (data instanceof Blob) {
        try {
            const raw = await data.text();

            if (!raw.trim().startsWith('{') && !raw.trim().startsWith('[')) {
                return raw.includes('Allowed memory size')
                    ? 'The certificate could not be rendered. A logo image is too large.'
                    : fallback;
            }

            const parsed = JSON.parse(raw);
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

function filenameFromDisposition(disposition) {
    const match = /filename="?([^"]+)"?/i.exec(disposition ?? '');

    return match?.[1] ?? 'certificate.pdf';
}

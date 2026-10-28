import http, { extractErrorMessage } from '@/services/http';

export async function exportDatabase(format) {
    const response = await http.post('/database-backups/export', { format }, {
        responseType: 'blob',
    });

    const disposition = response.headers?.['content-disposition'] ?? '';
    const match = /filename="?([^"]+)"?/i.exec(disposition);
    const filename = match?.[1] ?? fallbackName(format);
    const url = URL.createObjectURL(response.data);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(url);

    return filename;
}

export async function restoreDatabase(file, confirmation, adminPassword) {
    const formData = new FormData();
    formData.append('backup', file);
    formData.append('confirmation', confirmation);
    formData.append('admin_password', adminPassword ?? '');

    const { data } = await http.post('/database-backups/restore', formData, {
        transformRequest: [
            (value, headers) => {
                if (value instanceof FormData && headers) {
                    if (typeof headers.delete === 'function') {
                        headers.delete('Content-Type');
                    } else {
                        delete headers['Content-Type'];
                    }
                }

                return value;
            },
        ],
    });

    return data;
}

export async function backupErrorMessage(error, fallback) {
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

    const validationErrors = error?.response?.data?.errors ?? {};
    const firstError = Object.values(validationErrors).flat().find((message) => typeof message === 'string' && message !== '');

    return firstError || extractErrorMessage(error, fallback);
}

function fallbackName(format) {
    if (format === 'pdf') {
        return 'rbim-backup.pdf';
    }

    if (format === 'excel') {
        return 'rbim-backup.xlsx';
    }

    return 'rbim-backup.sql';
}

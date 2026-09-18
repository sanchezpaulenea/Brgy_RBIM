import axios from 'axios';
import { notifySuccess } from '@/composables/useAppNotifications';

const http = axios.create({
    baseURL: '/api/v1',
    withCredentials: true,
    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

let csrfInitialized = false;

export async function ensureCsrfCookie() {
    if (csrfInitialized) {
        return;
    }

    await axios.get('/sanctum/csrf-cookie', {
        withCredentials: true,
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    });

    csrfInitialized = true;
}

http.interceptors.request.use(async (config) => {
    if (config.method !== 'get') {
        await ensureCsrfCookie();
    }

    return config;
});

let handlingUnauthorized = false;

const WRITE_METHODS = ['post', 'patch', 'put', 'delete'];

/**
 * Sign-in and sign-out already navigate the user somewhere new, so their
 * messages would announce something the user can plainly see.
 */
const SILENT_PATHS = ['/auth/login', '/auth/logout'];

/**
 * Every write endpoint answers with a human-readable `message`. Surfacing it
 * here means a save, update, or delete anywhere in the app is confirmed the
 * same way, without each page having to remember to do it.
 */
function announceWrite(response) {
    const method = String(response.config?.method ?? '').toLowerCase();

    if (!WRITE_METHODS.includes(method)) {
        return;
    }

    const url = String(response.config?.url ?? '');

    if (SILENT_PATHS.some((path) => url.includes(path))) {
        return;
    }

    notifySuccess(response.data?.message);
}

http.interceptors.response.use(
    (response) => {
        handlingUnauthorized = false;
        announceWrite(response);

        return response;
    },
    async (error) => {
        if (error.response?.status === 419) {
            csrfInitialized = false;
            await ensureCsrfCookie();

            return http.request(error.config);
        }

        if (error.response?.status === 401 && !handlingUnauthorized) {
            const requestUrl = String(error.config?.url ?? '');
            const message = String(error.response?.data?.message ?? '');
            const reason = typeof error.response?.data?.reason === 'string'
                ? error.response.data.reason
                : null;
            const isLoginOrLogout = requestUrl.includes('/auth/login')
                || requestUrl.includes('/auth/logout');
            const isAnonymousSessionCheck = requestUrl.includes('/auth/me')
                && (message === '' || message === 'Unauthenticated.');

            if (!isLoginOrLogout && !isAnonymousSessionCheck) {
                handlingUnauthorized = true;
                window.dispatchEvent(new CustomEvent('rbim:unauthorized', {
                    detail: { message, reason },
                }));
            }
        }

        return Promise.reject(error);
    },
);

export function extractValidationErrors(error) {
    const errors = error.response?.data?.errors;

    if (!errors) {
        return {};
    }

    return Object.fromEntries(
        Object.entries(errors).map(([field, messages]) => [field, messages[0]]),
    );
}

export function extractErrorMessage(error, fallback = 'Something went wrong. Please try again.') {
    return error.response?.data?.message ?? fallback;
}

export default http;

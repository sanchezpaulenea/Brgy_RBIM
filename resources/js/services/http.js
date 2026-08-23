import axios from 'axios';

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

http.interceptors.response.use(
    (response) => {
        handlingUnauthorized = false;

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
            const isLoginOrLogout = requestUrl.includes('/auth/login')
                || requestUrl.includes('/auth/logout');
            const isAnonymousSessionCheck = requestUrl.includes('/auth/me')
                && (message === '' || message === 'Unauthenticated.');

            if (!isLoginOrLogout && !isAnonymousSessionCheck) {
                handlingUnauthorized = true;
                window.dispatchEvent(new CustomEvent('rbim:unauthorized', {
                    detail: { message },
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

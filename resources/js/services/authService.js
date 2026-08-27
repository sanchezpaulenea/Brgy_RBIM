import http, { ensureCsrfCookie } from './http';

export async function login(credentials) {
    await ensureCsrfCookie();

    const { data } = await http.post('/auth/login', credentials);

    return data;
}

export async function logout() {
    const { data } = await http.post('/auth/logout');

    return data;
}

export async function fetchMe() {
    const { data } = await http.get('/auth/me');

    return data;
}

export async function fetchPasswordPolicy() {
    const { data } = await http.get('/auth/password/policy');

    return data;
}

export async function changePassword(payload) {
    const { data } = await http.post('/auth/password/change', payload);

    return data;
}

export async function updateAvatar({ photo, avatarPreset } = {}) {
    const formData = new FormData();

    if (photo) {
        formData.append('photo', photo);
    }

    if (avatarPreset) {
        formData.append('avatar_preset', avatarPreset);
    }

    const { data } = await http.post('/auth/profile/avatar', formData, {
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

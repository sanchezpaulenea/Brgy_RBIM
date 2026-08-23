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

import http from '@/services/http';

function compactParams(filters = {}) {
    const params = {};

    Object.entries(filters).forEach(([key, value]) => {
        if (value === undefined || value === null || value === '') {
            return;
        }

        params[key] = value;
    });

    return params;
}

export async function fetchResidents(filters = {}) {
    const { data } = await http.get('/residents', { params: compactParams(filters) });

    return data.items;
}

export async function fetchResident(id) {
    const { data } = await http.get(`/residents/${id}`);

    return data.item;
}

export async function loadProfilingResident(residentOrId) {
    const id = typeof residentOrId === 'object'
        ? residentOrId?.resident_id
        : residentOrId;

    if (!id) {
        return residentOrId && typeof residentOrId === 'object' ? residentOrId : null;
    }

    try {
        return await fetchResident(id);
    } catch {
        return typeof residentOrId === 'object' ? residentOrId : { resident_id: id };
    }
}

export async function createResident(payload) {
    const { data } = await http.post('/residents', payload);

    return data.item;
}

export async function updateResident(id, payload) {
    const { data } = await http.patch(`/residents/${id}`, payload);

    return data.item;
}

export async function createEducation(residentId, payload) {
    const { data } = await http.post(`/residents/${residentId}/education`, payload);

    return data.item;
}

export async function updateEducation(educationId, payload) {
    const { data } = await http.patch(`/educations/${educationId}`, payload);

    return data.item;
}

export async function createEconomic(residentId, payload) {
    const { data } = await http.post(`/residents/${residentId}/economic`, payload);

    return data.item;
}

export async function updateEconomic(economicId, payload) {
    const { data } = await http.patch(`/economics/${economicId}`, payload);

    return data.item;
}

export async function createInfantHealth(residentId, payload) {
    const { data } = await http.post(`/residents/${residentId}/infant-health`, payload);

    return data.item;
}

export async function updateInfantHealth(infantHealthId, payload) {
    const { data } = await http.patch(`/infant-health/${infantHealthId}`, payload);

    return data.item;
}

export async function createHealth(residentId, payload) {
    const { data } = await http.post(`/residents/${residentId}/health`, payload);

    return data.item;
}

export async function updateHealth(healthId, payload) {
    const { data } = await http.patch(`/health-records/${healthId}`, payload);

    return data.item;
}

export async function createWomenHealth(residentId, payload) {
    const { data } = await http.post(`/residents/${residentId}/women-health`, payload);

    return data.item;
}

export async function updateWomenHealth(womenHealthId, payload) {
    const { data } = await http.patch(`/women-health/${womenHealthId}`, payload);

    return data.item;
}

export async function createSociocivic(residentId, payload) {
    const { data } = await http.post(`/residents/${residentId}/sociocivic`, payload);

    return data.item;
}

export async function updateSociocivic(sociocivicId, payload) {
    const { data } = await http.patch(`/sociocivics/${sociocivicId}`, payload);

    return data.item;
}

export async function createMigration(residentId, payload) {
    const { data } = await http.post(`/residents/${residentId}/migration`, payload);

    return data.item;
}

export async function updateMigration(migrationId, payload) {
    const { data } = await http.patch(`/migrations/${migrationId}`, payload);

    return data.item;
}

export async function createCtc(residentId, payload) {
    const { data } = await http.post(`/residents/${residentId}/ctc`, payload);

    return data.item;
}

export async function updateCtc(ctcId, payload) {
    const { data } = await http.patch(`/ctcs/${ctcId}`, payload);

    return data.item;
}

export async function createSkills(residentId, payload) {
    const { data } = await http.post(`/residents/${residentId}/skills`, payload);

    return data.item;
}

export async function updateSkills(skillsId, payload) {
    const { data } = await http.patch(`/skills/${skillsId}`, payload);

    return data.item;
}

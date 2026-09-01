import http from '@/services/http';

export async function fetchLocationProfile() {
    const { data } = await http.get('/location-profile');

    return data.location;
}

export async function fetchHouseholds(filters = {}) {
    const params = {};

    if (filters.clan_id) {
        params.clan_id = filters.clan_id;
    }

    if (filters.street_id) {
        params.street_id = filters.street_id;
    }

    if (filters.household_status_id) {
        params.household_status_id = filters.household_status_id;
    }

    const { data } = await http.get('/households', { params });

    return data.items;
}

export async function fetchHousehold(id) {
    const { data } = await http.get(`/households/${id}`);

    return data.item;
}

export async function createHousehold(payload) {
    const { data } = await http.post('/households', payload);

    return data.item;
}

export async function updateHousehold(id, payload) {
    const { data } = await http.patch(`/households/${id}`, payload);

    return data.item;
}

export async function fetchAllHouseholdAssessments() {
    const { data } = await http.get('/household-assessments');

    return data.items;
}

export async function fetchHouseholdAssessments(householdId) {
    const { data } = await http.get(`/households/${householdId}/assessments`);

    return data.items;
}

export async function fetchHouseholdAssessmentOptions() {
    const { data } = await http.get('/household-assessment-options');

    return data.personnel;
}

export async function createHouseholdAssessment(householdId, payload) {
    const { data } = await http.post(`/households/${householdId}/assessments`, payload);

    return data.item;
}

export async function createResident(payload) {
    const { data } = await http.post('/residents', payload);

    return data.item;
}

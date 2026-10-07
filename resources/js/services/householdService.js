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

export async function createHouseholdQuestions(householdId, payload) {
    const { data } = await http.post(`/households/${householdId}/questions`, payload);

    return data.item;
}

export async function createHouseholdPets(householdId, pets) {
    const { data } = await http.post('/pet-census', {
        household_id: Number(householdId),
        pets,
    });

    return data.items;
}

export async function fetchPetCensus(filters = {}) {
    const params = {};

    ['search', 'pet_status_id', 'specie_id', 'breed_id'].forEach((key) => {
        if (filters[key]) {
            params[key] = filters[key];
        }
    });

    const { data } = await http.get('/pet-census', { params });

    return data;
}

export async function updateHouseholdPet(petCensusId, payload) {
    const { data } = await http.patch(`/pet-census/${petCensusId}`, payload);

    return data.item;
}

export async function updateHouseholdQuestions(questionsId, payload) {
    const { data } = await http.patch(`/household-questions/${questionsId}`, payload);

    return data.item;
}

export async function createResident(payload) {
    const { data } = await http.post('/residents', payload);

    return data.item;
}

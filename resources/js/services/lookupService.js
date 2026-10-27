import http from '@/services/http';

const inFlightGets = new Map();

function selectable(items) {
    return (items ?? []).filter((item) => Number(item.id) > 0);
}

function copyItems(items) {
    if (!Array.isArray(items)) {
        return items;
    }

    return items.map((item) => (item && typeof item === 'object' ? { ...item } : item));
}

function getOnce(url, readItems) {
    let pending = inFlightGets.get(url);

    if (!pending) {
        pending = http.get(url).finally(() => {
            inFlightGets.delete(url);
        });
        inFlightGets.set(url, pending);
    }

    return pending.then(({ data }) => copyItems(readItems(data)));
}

export function fetchPersonnelPositions() {
    return getOnce('/personnel-positions', (data) => data.items);
}

export async function createPersonnelPosition(payload) {
    const { data } = await http.post('/personnel-positions', payload);

    return data.item;
}

export async function deletePersonnelPosition(id) {
    await http.delete(`/personnel-positions/${id}`);
}

export async function createDisability(payload) {
    const { data } = await http.post('/disabilities', payload);

    return data.item;
}

export async function createPlaceOfDelivery(payload) {
    const { data } = await http.post('/places-of-delivery', payload);

    return data.item;
}

export async function createBirthAttendant(payload) {
    const { data } = await http.post('/birth-attendants', payload);

    return data.item;
}

export async function createImmunization(payload) {
    const { data } = await http.post('/immunizations', payload);

    return data.item;
}

export async function createHealthInsurance(payload) {
    const { data } = await http.post('/health-insurances', payload);

    return data.item;
}

export async function createFacilityVisited(payload) {
    const { data } = await http.post('/facilities-visited-past-12mos', payload);

    return data.item;
}

export async function createFacilityVisitReason(payload) {
    const { data } = await http.post('/facility-visit-reasons', payload);

    return data.item;
}

export function fetchLookup(slug) {
    return getOnce(`/lookups/${slug}`, (data) => selectable(data.items));
}

export function fetchStreets() {
    return getOnce('/streets', (data) => data.items);
}

export async function createStreet(payload) {
    const { data } = await http.post('/streets', payload);

    return data.item;
}

export async function deleteStreet(id) {
    await http.delete(`/streets/${id}`);
}

export function fetchNationalities() {
    return getOnce('/nationalities', (data) => selectable(data.items));
}

export async function createNationality(payload) {
    const { data } = await http.post('/nationalities', payload);

    return data.item;
}

export async function deleteNationality(id) {
    await http.delete(`/nationalities/${id}`);
}

export function fetchEthnicities() {
    return getOnce('/ethnicities', (data) => selectable(data.items));
}

export async function createEthnicity(payload) {
    const { data } = await http.post('/ethnicities', payload);

    return data.item;
}

export async function deleteEthnicity(id) {
    await http.delete(`/ethnicities/${id}`);
}

export function fetchReligions() {
    return getOnce('/religions', (data) => selectable(data.items));
}

export async function createReligion(payload) {
    const { data } = await http.post('/religions', payload);

    return data.item;
}

export async function deleteReligion(id) {
    await http.delete(`/religions/${id}`);
}

export async function createSpecie(payload) {
    const { data } = await http.post('/species', payload);

    return data.item;
}

export async function createBreed(payload) {
    const { data } = await http.post('/breeds', payload);

    return data.item;
}

export async function createSkillType(payload) {
    const { data } = await http.post('/skill-types', payload);

    return data.item;
}

export async function createSex(payload) {
    const { data } = await http.post('/sexes', payload);

    return data.item;
}

export function fetchUserStatuses() {
    return getOnce('/user-statuses', (data) => data.items);
}

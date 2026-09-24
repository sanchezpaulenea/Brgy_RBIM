import http from '@/services/http';
import { LOOKUP_UNSPECIFIED_ID } from '@/utils/residentForm';

/**
 * Lookup tables carry a "Not Applicable" row at id 0 so that optional answers
 * can satisfy their NOT NULL foreign keys. It is a storage placeholder, not a
 * choice, so it never reaches a dropdown.
 */
function selectable(items) {
    return (items ?? []).filter((item) => Number(item.id) !== LOOKUP_UNSPECIFIED_ID);
}

export async function fetchPersonnelPositions() {
    const { data } = await http.get('/personnel-positions');

    return data.items;
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

export async function fetchLookup(slug) {
    const { data } = await http.get(`/lookups/${slug}`);

    return selectable(data.items);
}

export async function fetchStreets() {
    const { data } = await http.get('/streets');

    return data.items;
}

export async function createStreet(payload) {
    const { data } = await http.post('/streets', payload);

    return data.item;
}

export async function deleteStreet(id) {
    await http.delete(`/streets/${id}`);
}

export async function fetchNationalities() {
    const { data } = await http.get('/nationalities');

    return selectable(data.items);
}

export async function createNationality(payload) {
    const { data } = await http.post('/nationalities', payload);

    return data.item;
}

export async function deleteNationality(id) {
    await http.delete(`/nationalities/${id}`);
}

export async function fetchEthnicities() {
    const { data } = await http.get('/ethnicities');

    return selectable(data.items);
}

export async function createEthnicity(payload) {
    const { data } = await http.post('/ethnicities', payload);

    return data.item;
}

export async function deleteEthnicity(id) {
    await http.delete(`/ethnicities/${id}`);
}

export async function fetchReligions() {
    const { data } = await http.get('/religions');

    return selectable(data.items);
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

export async function fetchUserStatuses() {
    const { data } = await http.get('/user-statuses');

    return data.items;
}

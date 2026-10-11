import * as lookupService from '@/services/lookupService';

export const PET_LOOKUP_HINT = 'Choose from the list, or type a new name and press Enter to add it.';

export const PET_RABIES_HINT = 'Date the pet last received anti rabies vaccine';

export const ALLOWED_SPECIES = ['Dog', 'Cat'];

const PET_NAME_PATTERN = /^[\p{L} '\-]+$/u;

export function isAllowedPetSpecies(name) {
    const normalized = String(name ?? '').trim().toLowerCase();

    return ALLOWED_SPECIES.some((label) => label.toLowerCase() === normalized);
}

export function canonicalPetSpecies(name) {
    const normalized = String(name ?? '').trim().toLowerCase();

    return ALLOWED_SPECIES.find((label) => label.toLowerCase() === normalized) ?? String(name ?? '').trim();
}

export function allowedSpeciesOptions(species = []) {
    return ALLOWED_SPECIES.map((label) => {
        const existing = (species ?? []).find((option) => (
            String(option.label ?? '').trim().toLowerCase() === label.toLowerCase()
        ));

        return existing ? { id: existing.id, label } : { id: null, label };
    });
}

export function emptyPetForm(record = null) {
    return {
        pet_census_id: record?.pet_census_id ?? null,
        specie_id: record?.specie_id ?? null,
        specie_name: canonicalPetSpecies(record?.specie ?? ''),
        breed_id: record?.breed_id ?? null,
        breed_name: record?.breed ?? '',
        sex_id: record?.sex_id ?? '',
        pet_date_of_birth: record?.pet_date_of_birth ?? '',
        is_spay_neuter: record == null ? '' : (record.is_spay_neuter ? 'true' : 'false'),
        rabies_vaccination_date: record?.rabies_vaccination_date ?? '',
        pet_status_id: record?.pet_status_id ?? '',
    };
}

export function emptyPetLookups() {
    return {
        species: [],
        breeds: [],
        sexes: [],
        petStatuses: [],
    };
}

export async function fetchPetLookups() {
    const [species, breeds, sexes, petStatuses] = await Promise.all([
        lookupService.fetchLookup('specie'),
        lookupService.fetchLookup('breed'),
        lookupService.fetchLookup('sex'),
        lookupService.fetchLookup('pet-status'),
    ]);

    return { species, breeds, sexes, petStatuses };
}

export function petPayload(form) {
    const payload = {
        specie: canonicalPetSpecies(form.specie_name),
        breed: String(form.breed_name ?? '').trim(),
        sex_id: Number(form.sex_id),
        pet_date_of_birth: form.pet_date_of_birth || null,
        is_spay_neuter: form.is_spay_neuter === 'true' || form.is_spay_neuter === true,
        rabies_vaccination_date: form.rabies_vaccination_date || null,
    };

    if (form.pet_status_id) {
        payload.pet_status_id = Number(form.pet_status_id);
    }

    return payload;
}

function lookupNameError(value, label) {
    const name = String(value ?? '').trim();

    if (!name) {
        return `${label} is required.`;
    }

    if (name.length < 2 || name.length > 45 || !PET_NAME_PATTERN.test(name) || !/\p{L}/u.test(name)) {
        return `${label} may only contain letters, spaces, hyphens, and apostrophes.`;
    }

    return '';
}

export function validatePetForm(form, errors, { requireStatus = false } = {}) {
    Object.keys(errors).forEach((key) => {
        delete errors[key];
    });

    if (!isAllowedPetSpecies(form.specie_name)) {
        errors.specie = String(form.specie_name ?? '').trim()
            ? 'Specie must be Dog or Cat.'
            : 'Specie is required.';
    }

    const breedError = lookupNameError(form.breed_name, 'Breed');

    if (breedError) {
        errors.breed = breedError;
    }

    if (!Number(form.sex_id)) {
        errors.sex_id = 'Sex is required.';
    }

    if (form.pet_date_of_birth && form.pet_date_of_birth > new Date().toISOString().slice(0, 10)) {
        errors.pet_date_of_birth = 'Pet date of birth cannot be in the future.';
    }

    if (form.is_spay_neuter !== 'true' && form.is_spay_neuter !== 'false') {
        errors.is_spay_neuter = 'Spay/neuter is required.';
    }

    if (form.rabies_vaccination_date && form.pet_date_of_birth && form.rabies_vaccination_date < form.pet_date_of_birth) {
        errors.rabies_vaccination_date = 'Rabies vaccination date cannot be before the pet date of birth.';
    }

    if (requireStatus && !Number(form.pet_status_id)) {
        errors.pet_status_id = 'Pet status is required.';
    }

    return Object.keys(errors).length === 0;
}

export function yesNoLabel(value) {
    if (value === true || value === 'true') {
        return 'Yes';
    }

    if (value === false || value === 'false') {
        return 'No';
    }

    return '—';
}

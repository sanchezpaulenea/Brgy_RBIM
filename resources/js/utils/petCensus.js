import * as lookupService from '@/services/lookupService';

export const PET_LOOKUP_HINT = 'Choose from the list, or type a new name and press Enter to add it.';

export function emptyPetForm(record = null) {
    return {
        pet_census_id: record?.pet_census_id ?? null,
        specie_id: record?.specie_id ?? null,
        specie_name: record?.specie ?? '',
        breed_id: record?.breed_id ?? null,
        breed_name: record?.breed ?? '',
        sex_id: record?.sex_id ?? null,
        sex_name: record?.sex ?? '',
        pet_date_of_birth: record?.pet_date_of_birth ?? '',
        is_spay_neuter: record == null ? '' : (record.is_spay_neuter ? 'true' : 'false'),
        rabies_vaccination_date: record?.rabies_vaccination_date ?? '',
    };
}

export function emptyPetLookups() {
    return {
        species: [],
        breeds: [],
        sexes: [],
    };
}

export async function fetchPetLookups() {
    const [species, breeds, sexes] = await Promise.all([
        lookupService.fetchLookup('specie'),
        lookupService.fetchLookup('breed'),
        lookupService.fetchLookup('sex'),
    ]);

    return { species, breeds, sexes };
}

export function petPayload(form) {
    return {
        specie_id: Number(form.specie_id),
        breed_id: Number(form.breed_id),
        sex_id: Number(form.sex_id),
        pet_date_of_birth: form.pet_date_of_birth,
        is_spay_neuter: form.is_spay_neuter === 'true' || form.is_spay_neuter === true,
        rabies_vaccination_date: form.rabies_vaccination_date || null,
    };
}

export function validatePetForm(form, errors) {
    Object.keys(errors).forEach((key) => {
        delete errors[key];
    });

    if (!Number(form.specie_id)) {
        errors.specie_id = 'Specie is required.';
    }

    if (!Number(form.breed_id)) {
        errors.breed_id = 'Breed is required.';
    }

    if (!Number(form.sex_id)) {
        errors.sex_id = 'Sex is required.';
    }

    if (!form.pet_date_of_birth) {
        errors.pet_date_of_birth = 'Pet date of birth is required.';
    }

    if (form.is_spay_neuter !== 'true' && form.is_spay_neuter !== 'false') {
        errors.is_spay_neuter = 'Spay/neuter is required.';
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

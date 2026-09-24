import { ageFromDateOfBirth, todayDate } from '@/utils/format';
import { personnelNameValidationError, placeNameValidationError } from '@/utils/validation';

export const HEAD_RELATIONSHIP_ID = 1;

export const HOUSEHOLD_HEAD_MIN_AGE = 15;

export const HOUSEHOLD_STATUS_ACTIVE = 1;

export const RESIDENT_STATUS = {
    ACTIVE: 1,
    MOVED_OUT: 2,
    DECEASED: 3,
    ARCHIVE: 4,
};

export const DEFAULT_BIRTH_COUNTRY = 'Philippines';

export function emptyResidentForm(overrides = {}) {
    return {
        last_name: '',
        first_name: '',
        middle_name: '',
        suffix: '',
        relationship_to_hh_id: '',
        sex_id: '',
        date_of_birth: '',
        birth_city_municipality: '',
        birth_province: '',
        birth_country: DEFAULT_BIRTH_COUNTRY,
        nationality_id: '',
        nationality_name: '',
        religion_id: '',
        religion_name: '',
        ethnicity_id: '',
        ethnicity_name: '',
        marital_status_id: '',
        ...overrides,
    };
}

export function optionalText(value) {
    const text = String(value ?? '').trim();

    return text === '' ? null : text;
}

const NOT_APPLICABLE_ADDRESS = /^(n\/?a|n\.a\.?|not applicable)$/i;

export function optionalAddressText(value) {
    const text = optionalText(value);

    if (text === null || NOT_APPLICABLE_ADDRESS.test(text)) {
        return null;
    }

    return text;
}

export function emptyHouseholdStructure() {
    return {
        number_of_house_story: 1,
        has_basement: '',
        number_of_basement_level: '',
    };
}

export function householdStructureFromRecord(household) {
    const levels = Number(household?.number_of_basement_level ?? 0);

    return {
        number_of_house_story: household?.number_of_house_story ?? 1,
        has_basement: levels > 0 ? 'true' : 'false',
        number_of_basement_level: levels > 0 ? String(levels) : '',
    };
}

export function validateHouseholdStructure(form, errors) {
    const stories = Number(form.number_of_house_story);

    if (!Number.isInteger(stories) || stories < 1) {
        errors.number_of_house_story = 'Number of house stories is required.';
    } else {
        delete errors.number_of_house_story;
    }

    if (form.has_basement !== 'true' && form.has_basement !== 'false') {
        errors.has_basement = 'Please indicate whether the house has a basement.';
    } else {
        delete errors.has_basement;
    }

    if (form.has_basement === 'true') {
        const levels = Number(form.number_of_basement_level);

        if (!Number.isInteger(levels) || levels < 1) {
            errors.number_of_basement_level = 'Number of basement levels is required.';
        } else {
            delete errors.number_of_basement_level;
        }
    } else {
        delete errors.number_of_basement_level;
    }

    return !errors.number_of_house_story && !errors.has_basement && !errors.number_of_basement_level;
}

export function householdStructurePayload(form) {
    const hasBasement = form.has_basement === 'true';

    return {
        number_of_house_story: Number(form.number_of_house_story),
        has_basement: hasBasement,
        number_of_basement_level: hasBasement ? Number(form.number_of_basement_level) : 0,
    };
}

export function residentStatusRequiresHeadReplacement(statusId) {
    return [
        RESIDENT_STATUS.MOVED_OUT,
        RESIDENT_STATUS.DECEASED,
        RESIDENT_STATUS.ARCHIVE,
    ].includes(Number(statusId));
}

export function residentStatusLabel(statusId) {
    const labels = {
        [RESIDENT_STATUS.ACTIVE]: 'Active',
        [RESIDENT_STATUS.MOVED_OUT]: 'Moved Out',
        [RESIDENT_STATUS.DECEASED]: 'Deceased',
        [RESIDENT_STATUS.ARCHIVE]: 'Archive',
    };

    return labels[Number(statusId)] ?? 'this status';
}

export function eligibleHouseholdHeadCandidates(members, currentHeadId) {
    return (members || []).filter((member) => {
        if (Number(member.resident_id) === Number(currentHeadId)) {
            return false;
        }

        if (Number(member.resident_status_id) !== RESIDENT_STATUS.ACTIVE) {
            return false;
        }

        const age = ageFromDateOfBirth(member.date_of_birth);

        return age !== null && age >= HOUSEHOLD_HEAD_MIN_AGE;
    });
}

export function toId(value) {
    const number = Number(value);

    return Number.isInteger(number) && number > 0 ? number : null;
}

/**
 * Nationality, religion, and ethnicity columns are NOT NULL behind a foreign
 * key, so a blank answer is saved as the "Not Applicable" lookup row at id 0.
 */
export const LOOKUP_UNSPECIFIED_ID = 0;

export function toOptionalLookupId(value) {
    return toId(value) ?? LOOKUP_UNSPECIFIED_ID;
}

export function assignResidentNameError(form, errors, field, label, required = false) {
    const message = personnelNameValidationError(form[field], label, required);

    if (message) {
        errors[field] = message;

        return;
    }

    delete errors[field];
}

export function validateResidentForm(form, errors, { requireRelationship = true, minAge = 0 } = {}) {
    assignResidentNameError(form, errors, 'last_name', 'Last Name', true);
    assignResidentNameError(form, errors, 'first_name', 'First Name', true);
    assignResidentNameError(form, errors, 'middle_name', 'Middle Name');
    assignResidentNameError(form, errors, 'suffix', 'Suffix');

    if (requireRelationship && !toId(form.relationship_to_hh_id)) {
        errors.relationship_to_hh_id = 'Relationship to household head is required.';
    } else {
        delete errors.relationship_to_hh_id;
    }

    if (!toId(form.sex_id)) {
        errors.sex_id = 'Sex is required.';
    } else {
        delete errors.sex_id;
    }

    if (!form.date_of_birth) {
        errors.date_of_birth = 'Date of birth is required.';
    } else if (form.date_of_birth > todayDate()) {
        errors.date_of_birth = 'Date of birth cannot be in the future.';
    } else if (minAge > 0) {
        const age = ageFromDateOfBirth(form.date_of_birth);

        if (age === null || age < minAge) {
            errors.date_of_birth = minAge === HOUSEHOLD_HEAD_MIN_AGE
                ? `The household head must be at least ${minAge} years old.`
                : `Age must be at least ${minAge} years.`;
        } else {
            delete errors.date_of_birth;
        }
    } else {
        delete errors.date_of_birth;
    }

    const cityError = placeNameValidationError(form.birth_city_municipality, 'Birth City/Municipality');
    const provinceError = placeNameValidationError(form.birth_province, 'Birth Province');
    const countryError = placeNameValidationError(form.birth_country, 'Birth Country');

    if (cityError) {
        errors.birth_city_municipality = cityError;
    } else {
        delete errors.birth_city_municipality;
    }

    if (provinceError) {
        errors.birth_province = provinceError;
    } else {
        delete errors.birth_province;
    }

    if (countryError) {
        errors.birth_country = countryError;
    } else {
        delete errors.birth_country;
    }

    delete errors.nationality_id;
    delete errors.religion_id;
    delete errors.ethnicity_id;

    if (!toId(form.marital_status_id)) {
        errors.marital_status_id = 'Marital status is required.';
    } else {
        delete errors.marital_status_id;
    }

    return Object.keys(errors).length === 0;
}

export function residentPayload(form) {
    return {
        last_name: String(form.last_name ?? '').trim(),
        first_name: String(form.first_name ?? '').trim(),
        middle_name: optionalText(form.middle_name),
        suffix: optionalText(form.suffix),
        relationship_to_hh_id: toId(form.relationship_to_hh_id),
        sex_id: toId(form.sex_id),
        date_of_birth: form.date_of_birth,
        birth_city_municipality: String(form.birth_city_municipality ?? '').trim(),
        birth_province: String(form.birth_province ?? '').trim(),
        birth_country: String(form.birth_country ?? '').trim(),
        nationality_id: toOptionalLookupId(form.nationality_id),
        religion_id: toOptionalLookupId(form.religion_id),
        ethnicity_id: toOptionalLookupId(form.ethnicity_id),
        marital_status_id: toId(form.marital_status_id),
    };
}

export function duplicateResidentMatch(residents, form, excludeId = null) {
    const last = String(form.last_name ?? '').trim().toLowerCase();
    const first = String(form.first_name ?? '').trim().toLowerCase();

    if (!last || !first) {
        return null;
    }

    return residents.find((resident) => (
        Number(resident.resident_id) !== Number(excludeId)
        && String(resident.last_name ?? '').trim().toLowerCase() === last
        && String(resident.first_name ?? '').trim().toLowerCase() === first
    )) ?? null;
}

export function applyValidationErrors(target, validationErrors, prefix = '') {
    Object.keys(target).forEach((key) => {
        delete target[key];
    });

    Object.entries(validationErrors).forEach(([field, message]) => {
        if (prefix && field.startsWith(`${prefix}.`)) {
            target[field.slice(prefix.length + 1)] = message;

            return;
        }

        if (!prefix) {
            target[field] = message;
        }
    });
}

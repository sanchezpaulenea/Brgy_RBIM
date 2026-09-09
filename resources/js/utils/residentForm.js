import { ageFromDateOfBirth, todayDate } from '@/utils/format';
import { personnelNameValidationError, placeNameValidationError } from '@/utils/validation';

export const HEAD_RELATIONSHIP_ID = 1;

export const HOUSEHOLD_HEAD_MIN_AGE = 15;

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

export function toId(value) {
    const number = Number(value);

    return Number.isInteger(number) && number > 0 ? number : null;
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

    if (!toId(form.nationality_id)) {
        errors.nationality_id = 'Nationality is required.';
    } else {
        delete errors.nationality_id;
    }

    if (!toId(form.religion_id)) {
        errors.religion_id = 'Religion is required.';
    } else {
        delete errors.religion_id;
    }

    if (!toId(form.ethnicity_id)) {
        errors.ethnicity_id = 'Ethnicity is required.';
    } else {
        delete errors.ethnicity_id;
    }

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
        nationality_id: toId(form.nationality_id),
        religion_id: toId(form.religion_id),
        ethnicity_id: toId(form.ethnicity_id),
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

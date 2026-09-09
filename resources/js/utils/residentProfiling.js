import { ROLES } from '@/constants/roles';
import { ageFromDateOfBirth } from '@/utils/format';

export const SEX_FEMALE_ID = 2;
export const ENROLLMENT_NOT_ENROLLED_ID = 3;
export const NON_SOLO_PARENT_STATUS_ID = 2;

/**
 * Mirrors Resident::ageInMonths() — whole months since date of birth.
 */
export function ageInMonthsFromDateOfBirth(value, now = new Date()) {
    const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(String(value ?? '').trim());

    if (!match) {
        return null;
    }

    const birth = new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3]));

    if (Number.isNaN(birth.getTime())) {
        return null;
    }

    let months = ((now.getFullYear() - birth.getFullYear()) * 12)
        + (now.getMonth() - birth.getMonth());

    if (now.getDate() < birth.getDate()) {
        months -= 1;
    }

    return months < 0 ? null : months;
}

export function residentAgeYears(resident) {
    if (resident?.age !== undefined && resident?.age !== null && resident?.age !== '') {
        return Number(resident.age);
    }

    return ageFromDateOfBirth(resident?.date_of_birth);
}

export function residentAgeMonths(resident) {
    if (resident?.age_in_months !== undefined && resident?.age_in_months !== null && resident?.age_in_months !== '') {
        return Number(resident.age_in_months);
    }

    return ageInMonthsFromDateOfBirth(resident?.date_of_birth);
}

export function isFemaleResident(resident) {
    if (Number(resident?.sex_id) === SEX_FEMALE_ID) {
        return true;
    }

    return String(resident?.sex ?? '').trim().toLowerCase() === 'female';
}

/**
 * Mirrors Resident::applicableSections() exactly.
 */
export function applicableSectionsFor(resident) {
    const age = residentAgeYears(resident);
    const months = residentAgeMonths(resident);

    return {
        education: true,
        economic: true,
        infant_health: months !== null && months >= 0 && months <= 11,
        health: true,
        women_health: isFemaleResident(resident) && age !== null && age >= 10 && age <= 54,
        sociocivic: true,
        migration: true,
        ctc: age !== null && age >= 18,
        skills: age !== null && age >= 15,
    };
}

export function resolveApplicableSections(resident) {
    const fromApi = resident?.applicable_sections;

    if (fromApi && typeof fromApi === 'object') {
        return {
            ...applicableSectionsFor(resident),
            ...fromApi,
        };
    }

    return applicableSectionsFor(resident);
}

/**
 * Mirrors Resident::sociocivicFieldRelevance().
 */
export function sociocivicFieldRelevance(resident) {
    const fromRecord = resident?.sociocivic?.field_relevance;

    if (fromRecord && typeof fromRecord === 'object') {
        return {
            solo_parent: Boolean(fromRecord.solo_parent),
            senior_citizen: Boolean(fromRecord.senior_citizen),
            barangay_voter: Boolean(fromRecord.barangay_voter),
        };
    }

    const age = residentAgeYears(resident);

    return {
        solo_parent: age !== null && age >= 10,
        senior_citizen: age !== null && age >= 60,
        barangay_voter: age !== null && age >= 15,
    };
}

export function completenessRatio(resident) {
    const completeness = resident?.profiling_completeness;
    const applicable = Number(completeness?.applicable_count ?? 0);

    if (!completeness || applicable === 0) {
        return 1;
    }

    return Number(completeness.completed_count ?? 0) / applicable;
}

export function isProfileIncomplete(resident) {
    const completeness = resident?.profiling_completeness;

    if (!completeness) {
        return false;
    }

    return completeness.is_complete !== true;
}

export function canPerformCreate(auth, permission) {
    return auth.hasPermission(permission);
}

export function canPerformUpdate(auth, permissions) {
    if (auth.hasRole(ROLES.ADMIN)) {
        return true;
    }

    const list = Array.isArray(permissions) ? permissions : [permissions];

    return list.some((permission) => auth.hasPermission(permission));
}

export function isEnrollmentStatusEnrolled(option) {
    if (!option) {
        return false;
    }

    if (Number(option.id) === ENROLLMENT_NOT_ENROLLED_ID) {
        return false;
    }

    return String(option.label ?? '').trim().toLowerCase() !== 'no';
}

export function isFamilyPlanningNone(option) {
    if (!option) {
        return false;
    }

    return /\bnone\b/i.test(String(option.label ?? ''));
}

export function isActiveWorkStatus(option) {
    if (!option) {
        return false;
    }

    return !/\b(unemployed|none|not working|inactive|no work)\b/i.test(String(option.label ?? ''));
}

export function lookupById(options, id) {
    return options.find((option) => Number(option.id) === Number(id)) ?? null;
}

export function firstLookupId(options) {
    return options?.[0]?.id ?? null;
}

export function titleCaseWords(value) {
    return String(value ?? '')
        .trim()
        .replace(/\w\S*/g, (word) => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase());
}

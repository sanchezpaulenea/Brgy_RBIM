export const NON_MIGRANT_TYPE_ID = 1;

export const MIGRANT_TYPE_ID = 2;

export const TRANSIENT_TYPE_ID = 3;

export const MIGRANT_THRESHOLD_MONTHS = 6;

export function normalizePlace(value) {
    const text = String(value ?? '').trim().replace(/\s+/g, ' ');

    if (text === '') {
        return '';
    }

    return text
        .replace(/^(brgy\.?|barangay)\s+/i, '')
        .replace(/\s+(city|municipality)$/i, '')
        .trim()
        .toLowerCase();
}

export function placesMatch(left, right) {
    const a = normalizePlace(left);
    const b = normalizePlace(right);

    return a !== '' && a === b;
}

export function sameAsCurrentResidence(previousBrgy, previousCity, currentBrgy, currentCity) {
    if (!placesMatch(previousCity, currentCity)) {
        return false;
    }

    const previousBarangay = normalizePlace(previousBrgy);
    const currentBarangay = normalizePlace(currentBrgy);

    if (previousBarangay === '' || currentBarangay === '') {
        return true;
    }

    return previousBarangay === currentBarangay;
}

export function lengthOfStayMonths(isoDate, asOf = new Date()) {
    if (!isoDate) {
        return null;
    }

    const match = /^(\d{4})-(\d{2})/.exec(String(isoDate));

    if (!match) {
        return null;
    }

    const months = (asOf.getFullYear() - Number(match[1])) * 12
        + (asOf.getMonth() + 1 - Number(match[2]));

    return months < 0 ? 0 : months;
}

export function lengthOfStayLabel(months) {
    if (months === null || months === undefined) {
        return '';
    }

    const years = Math.floor(months / 12);
    const remaining = months % 12;

    return `${years} years / ${remaining} months`;
}

export function classifyResidentType({
    previousBrgy,
    previousCity,
    previousFiveYearBrgy,
    previousFiveYearCity,
    currentBrgy,
    currentCity,
    transferDate,
}) {
    const sameSixMonths = sameAsCurrentResidence(previousBrgy, previousCity, currentBrgy, currentCity);
    const sameFiveYears = sameAsCurrentResidence(
        previousFiveYearBrgy ?? previousBrgy,
        previousFiveYearCity ?? previousCity,
        currentBrgy,
        currentCity,
    );

    if (sameSixMonths && sameFiveYears) {
        return NON_MIGRANT_TYPE_ID;
    }

    const months = lengthOfStayMonths(transferDate);

    if (months !== null && months >= MIGRANT_THRESHOLD_MONTHS) {
        return MIGRANT_TYPE_ID;
    }

    return TRANSIENT_TYPE_ID;
}

export function monthsAgoIso(months, asOf = new Date()) {
    const date = new Date(asOf.getFullYear(), asOf.getMonth() - months, 1);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');

    return `${year}-${month}-01`;
}

export function residentTypeLabel(typeId) {
    if (Number(typeId) === NON_MIGRANT_TYPE_ID) {
        return 'Non-Migrant';
    }

    if (Number(typeId) === MIGRANT_TYPE_ID) {
        return 'Migrant';
    }

    if (Number(typeId) === TRANSIENT_TYPE_ID) {
        return 'Transient';
    }

    return '';
}

export function formatMonthYear(isoDate) {
    const match = /^(\d{4})-(\d{2})/.exec(String(isoDate ?? ''));

    if (!match) {
        return '';
    }

    return `${match[2]}/${match[1]}`;
}

export function classifyMigrationForm(form, location = {}) {
    const sameSixMonths = sameAsCurrentResidence(
        form?.previous_residence_6mos_brgy,
        form?.previous_residence_6mos_city_municipality,
        location?.barangay,
        location?.city,
    );
    const sameFiveYears = sameAsCurrentResidence(
        form?.previous_residence_5yrs_brgy,
        form?.previous_residence_5yrs_city_municipality,
        location?.barangay,
        location?.city,
    );
    const typeId = classifyResidentType({
        previousBrgy: form?.previous_residence_6mos_brgy,
        previousCity: form?.previous_residence_6mos_city_municipality,
        previousFiveYearBrgy: form?.previous_residence_5yrs_brgy,
        previousFiveYearCity: form?.previous_residence_5yrs_city_municipality,
        currentBrgy: location?.barangay,
        currentCity: location?.city,
        transferDate: form?.date_of_transfer_in_brgy,
    });
    const nonMigrant = typeId === NON_MIGRANT_TYPE_ID;
    const months = nonMigrant ? null : lengthOfStayMonths(form?.date_of_transfer_in_brgy);

    return {
        typeId,
        typeLabel: residentTypeLabel(typeId),
        nonMigrant,
        sixMonthsDiffers: !sameSixMonths,
        fiveYearsDiffers: !sameFiveYears,
        stayMonths: months,
        stayLabel: lengthOfStayLabel(months),
        transferDateMin: !sameSixMonths ? monthsAgoIso(MIGRANT_THRESHOLD_MONTHS) : '',
    };
}

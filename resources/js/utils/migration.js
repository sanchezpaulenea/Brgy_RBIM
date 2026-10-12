export const NON_MIGRANT_TYPE_ID = 1;

export const MIGRANT_TYPE_ID = 2;

export const TRANSIENT_TYPE_ID = 3;

export const MIGRANT_THRESHOLD_MONTHS = 6;

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

/**
 * Month precision used by the transfer-date field (`YYYY-MM-01`).
 */
export function transferMonthFromDate(isoDate) {
    const match = /^(\d{4})-(\d{2})/.exec(String(isoDate ?? ''));

    if (!match || match[1] === '0000') {
        return '';
    }

    return `${match[1]}-${match[2]}-01`;
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

    if (!match || match[1] === '0000') {
        return '';
    }

    return `${match[2]}/${match[1]}`;
}

/**
 * Since Birth is non-migrant. Otherwise 6 months and above is migrant, below that is transient.
 */
export function classifyMigrationForm(form, { dateOfBirth } = {}) {
    const sinceBirth = form?.since_birth === true;
    const transferDate = sinceBirth
        ? (transferMonthFromDate(dateOfBirth) || transferMonthFromDate(form?.date_of_transfer_in_brgy))
        : transferMonthFromDate(form?.date_of_transfer_in_brgy);
    const months = lengthOfStayMonths(transferDate);

    let typeId = null;

    if (sinceBirth && transferDate) {
        typeId = NON_MIGRANT_TYPE_ID;
    } else if (!sinceBirth && months !== null) {
        typeId = months >= MIGRANT_THRESHOLD_MONTHS ? MIGRANT_TYPE_ID : TRANSIENT_TYPE_ID;
    }

    return {
        typeId,
        typeLabel: residentTypeLabel(typeId),
        nonMigrant: typeId === NON_MIGRANT_TYPE_ID,
        migrantOrTransient: typeId === MIGRANT_TYPE_ID || typeId === TRANSIENT_TYPE_ID,
        sinceBirth,
        stayMonths: months,
        stayLabel: lengthOfStayLabel(months),
        transferDate,
        plansToReturn: form?.will_return_to_previous_residence === true,
    };
}

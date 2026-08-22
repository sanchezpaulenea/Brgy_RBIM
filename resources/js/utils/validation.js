export const POSITION_NAME_PATTERN = /^\p{L}+(?: \p{L}+)*$/u;

export const POSITION_NAME_ERROR = 'Position name may only contain letters and spaces.';

export const PERSONNEL_NAME_PATTERN = /^[\p{L} .'\-]+$/u;

export function isValidPositionName(value) {
    return positionNameValidationError(value) === '';
}

export function positionNameValidationError(value) {
    const name = typeof value === 'string' ? value.trim() : '';

    if (!name) {
        return 'Position name is required.';
    }

    if (!POSITION_NAME_PATTERN.test(name)) {
        return POSITION_NAME_ERROR;
    }

    return '';
}

export function personnelNameValidationError(value, label, required = false) {
    const name = typeof value === 'string' ? value.trim() : '';

    if (!name) {
        return required ? `${label} is required.` : '';
    }

    if (!PERSONNEL_NAME_PATTERN.test(name)) {
        return `${label} may only contain letters, spaces, hyphens, apostrophes, and periods.`;
    }

    if (!/\p{L}/u.test(name)) {
        return `${label} must contain at least one letter.`;
    }

    return '';
}

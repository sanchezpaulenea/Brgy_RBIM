export const POSITION_NAME_PATTERN = /^[\p{L}\p{N}]+(?:[ \-][\p{L}\p{N}]+)*$/u;

export const POSITION_NAME_SPECIAL_CHAR_ERROR = 'Position name must not contain special characters.';

export const POSITION_NAME_NUMBERS_ONLY_ERROR = 'Position name must not contain numbers only.';

export function isValidPositionName(value) {
    return positionNameValidationError(value) === '';
}

export function positionNameValidationError(value) {
    const name = typeof value === 'string' ? value.trim() : '';

    if (!name) {
        return 'Position name is required.';
    }

    if (!POSITION_NAME_PATTERN.test(name)) {
        return POSITION_NAME_SPECIAL_CHAR_ERROR;
    }

    if (!/\p{L}/u.test(name)) {
        return POSITION_NAME_NUMBERS_ONLY_ERROR;
    }

    return '';
}

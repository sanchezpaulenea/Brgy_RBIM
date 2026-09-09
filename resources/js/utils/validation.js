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

export const USERNAME_PATTERN = /^[A-Za-z0-9](?:[A-Za-z0-9._-]{2,44})$/;

export const LOGIN_PASSWORD_MIN_LENGTH = 8;

export const INVALID_CREDENTIALS_MESSAGE = 'Invalid credentials.';

export const INVALID_USERNAME_MESSAGE = 'Invalid username.';

export const INVALID_PASSWORD_MESSAGE = 'Invalid password, please try again.';

export const INVALID_USERNAME_OR_PASSWORD_MESSAGE = 'Invalid username or password.';

export const EMPTY_USERNAME_MESSAGE = 'Please enter your username.';

export const EMPTY_PASSWORD_MESSAGE = 'Please enter your password.';

export const EMPTY_CREDENTIALS_MESSAGE = 'Please enter your username and password.';

export function isWellFormedUsername(value) {
    return USERNAME_PATTERN.test(typeof value === 'string' ? value.trim() : '');
}

export function isWellFormedPassword(value) {
    const password = typeof value === 'string' ? value : '';

    return password.length >= LOGIN_PASSWORD_MIN_LENGTH && !/\s/.test(password);
}

export const BARANGAY_ADDRESS_MIN_LENGTH = 10;

export const BARANGAY_CODE_PATTERN = /^\d{10}$/;

export const EMAIL_PATTERN = /^[^\s@]+@[^\s@.]+(?:\.[^\s@.]+)+$/;

export const PLACE_NAME_PATTERN = /^\p{L}[\p{L}\d .,'\-]*$/u;

export const PLACE_NAME_MIN_LENGTH = 3;

export const NCSC_RRN_PATTERN = /^\d{6}$/;

export function ncscRrnValidationError(value, required = false) {
    const number = typeof value === 'string' || typeof value === 'number'
        ? String(value).trim()
        : '';

    if (!number) {
        return required ? 'NCSC-RRN is required.' : '';
    }

    return NCSC_RRN_PATTERN.test(number)
        ? ''
        : 'NCSC-RRN must be a 6-digit Registration Reference Number.';
}

/**
 * Each numeric setting drives real behaviour (lockouts, sessions, retention),
 * so the accepted range is bounded per key instead of "any number above zero".
 */
export const SETTING_INT_RANGES = {
    password_min_length: { min: 8, max: 32 },
    max_login_attempts: { min: 3, max: 10 },
    account_lockout_minutes: { min: 1, max: 1440 },
    session_timeout_minutes: { min: 5, max: 480 },
    audit_log_retention_days: { min: 30, max: 3650 },
};

export function placeNameValidationError(value, label, required = true) {
    const name = typeof value === 'string' ? value.trim() : '';

    if (!name) {
        return required ? `${label} is required.` : '';
    }

    if (name.length < PLACE_NAME_MIN_LENGTH) {
        return `${label} must be at least ${PLACE_NAME_MIN_LENGTH} characters long.`;
    }

    if (!PLACE_NAME_PATTERN.test(name)) {
        return `${label} must start with a letter and may only contain letters, numbers, spaces, periods, commas, hyphens, and apostrophes.`;
    }

    if ((name.match(/\p{L}/gu) ?? []).length < PLACE_NAME_MIN_LENGTH) {
        return `${label} must contain at least ${PLACE_NAME_MIN_LENGTH} letters.`;
    }

    return '';
}

/**
 * Mirrors the server rules in App\Rules so an invalid barangay profile value is
 * rejected before it is saved, with the same wording the API would return.
 */
const SETTING_VALIDATORS = {
    barangay_name(value) {
        return placeNameValidationError(value, 'Barangay name');
    },
    city_name(value) {
        return placeNameValidationError(value, 'City name');
    },
    default_password(value) {
        if (/\s/.test(value)) {
            return 'Default password must not contain spaces.';
        }

        if (value.length < LOGIN_PASSWORD_MIN_LENGTH) {
            return `Default password must be at least ${LOGIN_PASSWORD_MIN_LENGTH} characters long.`;
        }

        if (!/[A-Z]/.test(value) || !/[a-z]/.test(value) || !/\d/.test(value)) {
            return 'Default password must include at least one uppercase letter, one lowercase letter, and one number.';
        }

        return '';
    },
    barangay_address(value) {
        const address = value.trim();

        if (address.length < BARANGAY_ADDRESS_MIN_LENGTH) {
            return `Please enter a valid barangay address (at least ${BARANGAY_ADDRESS_MIN_LENGTH} characters).`;
        }

        if (!/\p{L}/u.test(address)) {
            return 'Please enter a valid barangay address. It must include the barangay, city, and province names.';
        }

        const compact = address.replace(/\s+/gu, '');

        if (compact && /^(.)\1+$/u.test(compact)) {
            return 'Please enter a valid barangay address.';
        }

        return '';
    },
    barangay_code(value) {
        return BARANGAY_CODE_PATTERN.test(value.trim())
            ? ''
            : 'Barangay code must follow the Philippine Standard Geographic Code (PSGC): exactly 10 digits, numbers only.';
    },
    barangay_contact_no(value) {
        const normalized = value.replace(/[\s\-()]+/g, '');
        const isMobile = /^09\d{9}$/.test(normalized) || /^\+639\d{9}$/.test(normalized);
        const isLandline = /^02\d{7,8}$/.test(normalized) || /^0[3-8]\d{8,9}$/.test(normalized);

        return isMobile || isLandline
            ? ''
            : 'Please enter a valid Philippine contact number. Use an 11-digit mobile number starting with 09 (e.g. 09123456789) or +639XXXXXXXXX, or a landline with area code plus 7–8 local digits (e.g. 074-123-4567).';
    },
    barangay_email(value) {
        return EMAIL_PATTERN.test(value.trim())
            ? ''
            : 'Please enter a valid email address that includes an @ symbol and a domain (e.g. barangay@example.com).';
    },
};

export function settingValueValidationError(setting, value) {
    const text = value === null || value === undefined ? '' : String(value);

    if (!text.trim()) {
        return 'This value is required.';
    }

    if (text.length > 45) {
        return 'This value may not be longer than 45 characters.';
    }

    const validate = SETTING_VALIDATORS[setting?.setting_key];

    if (validate) {
        return validate(text);
    }

    if (setting?.data_type === 'int') {
        const number = text.trim();

        if (!/^\d+$/.test(number)) {
            return 'Please enter a whole number, digits only.';
        }

        const range = SETTING_INT_RANGES[setting?.setting_key];

        if (range && (Number(number) < range.min || Number(number) > range.max)) {
            return `Please enter a whole number between ${range.min} and ${range.max}.`;
        }

        return range || Number(number) >= 1 ? '' : 'Please enter a whole number of 1 or greater.';
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

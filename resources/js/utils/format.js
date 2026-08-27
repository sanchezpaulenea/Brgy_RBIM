export function personnelDisplayName(person) {
    if (!person) {
        return '—';
    }

    if (person.full_name) {
        return person.full_name;
    }

    const last = person.personnel_last_name?.trim();
    const first = [person.personnel_first_name, person.personnel_middle_name, person.personnel_suffix]
        .filter(Boolean)
        .join(' ')
        .trim();

    if (last && first) {
        return `${last}, ${first}`;
    }

    if (last || first) {
        return last || first;
    }

    return person.label || person.position_name || (person.personnel_id ? `Personnel #${person.personnel_id}` : '—');
}

export function formatDateTime(value) {
    if (!value) {
        return '—';
    }

    const text = String(value).trim();

    if (/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/.test(text)) {
        return text.slice(0, 19);
    }

    const parsed = new Date(text);

    if (Number.isNaN(parsed.getTime())) {
        return text.replace('T', ' ').slice(0, 19);
    }

    const pad = (part) => String(part).padStart(2, '0');

    return [
        parsed.getFullYear(),
        pad(parsed.getMonth() + 1),
        pad(parsed.getDate()),
    ].join('-')
    + ' '
    + [pad(parsed.getHours()), pad(parsed.getMinutes()), pad(parsed.getSeconds())].join(':');
}

/**
 * Usernames are stored lowercase, so greetings capitalize the first letter of
 * each word without touching the rest of the spelling.
 */
export function formatDisplayName(value) {
    const name = String(value ?? '').trim();

    if (!name) {
        return '';
    }

    return name.replace(/(^|[\s.'-])(\p{L})/gu, (match, separator, letter) => separator + letter.toUpperCase());
}

/**
 * Stored keys use shorthand such as `no`, `max`, and `min`; the interface always
 * spells the words out in full.
 */
const SETTING_LABELS = {
    barangay_name: 'Barangay Name',
    barangay_address: 'Barangay Address',
    city_name: 'City Name',
    barangay_code: 'Barangay Code',
    barangay_contact_no: 'Barangay Contact Number',
    barangay_email: 'Barangay Email',
    password_min_length: 'Password Minimum Length',
    max_login_attempts: 'Maximum Login Attempts',
    account_lockout_minutes: 'Account Lockout Minutes',
    default_password: 'Default Password',
    session_timeout_minutes: 'Session Timeout Minutes',
    audit_log_retention_days: 'Audit Log Retention Days',
};

export function formatSettingLabel(key) {
    return SETTING_LABELS[key]
        ?? String(key).replaceAll('_', ' ').replace(/\b\w/g, (char) => char.toUpperCase());
}

/**
 * Presents audit log entity and target values the way they read in the app
 * rather than the way they are stored, e.g. `barangay_personnel` becomes
 * "Barangay Personnel".
 */
export function formatRecordLabel(value) {
    const text = String(value ?? '').trim();

    if (!text) {
        return '—';
    }

    return text
        .replaceAll('_', ' ')
        .replace(/\s+/g, ' ')
        .replace(/\b\w/g, (char) => char.toUpperCase());
}

export function todayDate() {
    const now = new Date();
    const offset = now.getTimezoneOffset();

    return new Date(now.getTime() - (offset * 60_000)).toISOString().slice(0, 10);
}

/**
 * Completed years of age from an ISO date of birth (`YYYY-MM-DD`).
 */
export function ageFromDateOfBirth(value) {
    const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(String(value ?? '').trim());

    if (!match) {
        return null;
    }

    const birth = new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3]));

    if (Number.isNaN(birth.getTime())) {
        return null;
    }

    const today = new Date();
    let age = today.getFullYear() - birth.getFullYear();
    const beforeBirthday = today.getMonth() < birth.getMonth()
        || (today.getMonth() === birth.getMonth() && today.getDate() < birth.getDate());

    if (beforeBirthday) {
        age -= 1;
    }

    return age < 0 ? null : age;
}

export function formatDate(value) {
    if (!value) {
        return '—';
    }

    return String(value).slice(0, 10);
}

export function householdDisplayLabel(household) {
    if (!household) {
        return '—';
    }

    const address = [household.street_name, household.house_lot].filter(Boolean).join(', ');
    const head = household.head_name || household.head?.full_name || personDisplayName(household.head);
    const id = household.household_id;

    if (address && head) {
        return `${id} — ${address} (${head})`;
    }

    if (address) {
        return id ? `${id} — ${address}` : address;
    }

    if (head) {
        return id ? `${id} — ${head}` : head;
    }

    return id ? `Household ${id}` : '—';
}

export function personDisplayName(person) {
    if (!person) {
        return '';
    }

    const last = String(person.last_name ?? '').trim();
    const given = [person.first_name, person.middle_name, person.suffix]
        .filter(Boolean)
        .join(' ')
        .trim();

    if (last && given) {
        return `${last}, ${given}`;
    }

    return last || given;
}

export function normalizeSearch(value) {
    return String(value ?? '').trim().toLocaleLowerCase();
}

export function matchesSearch(haystack, needle) {
    const term = normalizeSearch(needle);

    if (!term) {
        return true;
    }

    return normalizeSearch(haystack).includes(term);
}

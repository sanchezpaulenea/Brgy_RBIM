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

export function formatSettingLabel(key) {
    return String(key).replaceAll('_', ' ').replace(/\b\w/g, (char) => char.toUpperCase());
}

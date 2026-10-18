/**
 * Guided date entry for MM/DD/YYYY and MM/YYYY.
 * A slash stays on screen after each finished part. Month stops at 12.
 * Day stops at 31, 30, or 29 when February is already chosen.
 */

export function emptyDateParts() {
    return { month: '', day: '', year: '' };
}

export function maxDayForMonth(month) {
    const monthNumber = Number(month);

    if (monthNumber === 2) {
        return 29;
    }

    if (monthNumber >= 1 && monthNumber <= 12) {
        return new Date(2001, monthNumber, 0).getDate();
    }

    return 31;
}

export function isCompleteDate(parts, precision = 'day') {
    if (parts.month.length !== 2 || parts.year.length !== 4) {
        return false;
    }

    return precision === 'month' || parts.day.length === 2;
}

export function textFromParts(parts, precision = 'day') {
    const month = parts.month ?? '';
    const day = precision === 'month' ? '' : (parts.day ?? '');
    const year = parts.year ?? '';

    if (precision === 'month') {
        if (!month && !year) {
            return '';
        }

        if (!year) {
            return month.length >= 2 ? `${month}/` : month;
        }

        return `${month}/${year}`;
    }

    if (!month && !day && !year) {
        return '';
    }

    if (!day && !year) {
        return month.length >= 2 ? `${month}/` : month;
    }

    if (!year) {
        return day.length >= 2 ? `${month}/${day}/` : `${month}/${day}`;
    }

    return `${month}/${day}/${year}`;
}

export function maskDateTyping(raw, precision = 'day') {
    const text = String(raw ?? '');

    if (looksLikeCompleteDate(text, precision)) {
        const loose = parseLooseDate(text, precision);

        if (!loose) {
            return { parts: emptyDateParts(), caret: 0, text: '', rejected: true };
        }

        const display = textFromParts(loose, precision);

        return { parts: loose, caret: display.length, text: display, rejected: false };
    }

    let parts = emptyDateParts();
    let caret = 0;

    for (const character of text) {
        const next = applyDateCharacter(parts, caret, caret, character, precision);
        parts = next.parts;
        caret = next.caret;
    }

    return { parts, caret, text: textFromParts(parts, precision), rejected: false };
}

export function applyDateCharacter(parts, selectionStart, selectionEnd, character, precision = 'day') {
    if (/^\d$/.test(character)) {
        return applyDateDigit(parts, selectionStart, selectionEnd, character, precision);
    }

    if (character === '/' || character === '-' || character === '.') {
        const cleared = selectionEnd > selectionStart
            ? applyDateDeleteRange(parts, selectionStart, selectionEnd, precision)
            : pack(parts, selectionStart, precision);

        return applyDateSeparator(cleared.parts, cleared.caret, precision);
    }

    return pack(parts, selectionStart, precision);
}

export function applyDateDigit(parts, selectionStart, selectionEnd, digit, precision = 'day') {
    const cleared = selectionEnd > selectionStart
        ? applyDateDeleteRange(parts, selectionStart, selectionEnd, precision)
        : pack(parts, selectionStart, precision);

    return insertDateDigit(cleared.parts, cleared.caret, digit, precision);
}

export function applyDatePaste(parts, selectionStart, selectionEnd, raw, precision = 'day') {
    if (looksLikeCompleteDate(raw, precision)) {
        const masked = maskDateTyping(raw, precision);

        if (masked.rejected) {
            return pack(parts, selectionStart, precision);
        }

        return masked;
    }

    let current = normalize(parts, precision);
    let caret = selectionStart;

    if (selectionEnd > selectionStart) {
        const cleared = applyDateDeleteRange(current, selectionStart, selectionEnd, precision);
        current = cleared.parts;
        caret = cleared.caret;
    }

    for (const character of String(raw ?? '')) {
        const next = applyDateCharacter(current, caret, caret, character, precision);
        current = next.parts;
        caret = next.caret;
    }

    return pack(current, caret, precision);
}

export function applyDateSeparator(parts, caret, precision = 'day') {
    const current = normalize(parts, precision);
    const { seg } = locate(textFromParts(current, precision), caret, precision);

    if (seg === 'month') {
        if (current.month.length === 1 && current.month !== '0') {
            const next = normalize({ ...current, month: `0${current.month}` }, precision);

            return pack(next, placeCaret(next, 'month', 2, precision), precision);
        }

        if (current.month.length === 2) {
            const destination = precision === 'month' ? 'year' : 'day';

            return pack(current, placeCaret(current, destination, 0, precision), precision);
        }
    }

    if (seg === 'day') {
        if (current.day.length === 1 && current.day !== '0') {
            const padded = `0${current.day}`;

            if (Number(padded) <= maxDayForMonth(current.month)) {
                const next = normalize({ ...current, day: padded }, precision);

                return pack(next, placeCaret(next, 'day', 2, precision), precision);
            }
        }

        if (current.day.length === 2) {
            return pack(current, placeCaret(current, 'year', 0, precision), precision);
        }
    }

    return pack(current, caret, precision);
}

export function applyDateBackspace(parts, selectionStart, selectionEnd = selectionStart, precision = 'day') {
    if (selectionEnd > selectionStart) {
        return applyDateDeleteRange(parts, selectionStart, selectionEnd, precision);
    }

    const current = normalize(parts, precision);
    const text = textFromParts(current, precision);
    const caret = Math.max(0, Math.min(selectionStart, text.length));

    if (caret === 0) {
        return pack(current, 0, precision);
    }

    const { seg, offset } = locate(text, caret, precision);

    if (offset === 0 && seg === 'day') {
        const next = normalize({ ...current, month: current.month.slice(0, -1) }, precision);

        return pack(next, placeCaret(next, 'month', next.month.length, precision), precision);
    }

    if (offset === 0 && seg === 'year') {
        if (precision === 'month') {
            const next = normalize({ ...current, month: current.month.slice(0, -1) }, precision);

            return pack(next, placeCaret(next, 'month', next.month.length, precision), precision);
        }

        const next = normalize({ ...current, day: current.day.slice(0, -1) }, precision);

        return pack(next, placeCaret(next, 'day', next.day.length, precision), precision);
    }

    if (offset === 0) {
        return pack(current, 0, precision);
    }

    const segment = current[seg];
    const next = normalize({
        ...current,
        [seg]: segment.slice(0, offset - 1) + segment.slice(offset),
    }, precision);

    return pack(next, placeCaret(next, seg, offset - 1, precision), precision);
}

export function applyDateDeleteForward(parts, selectionStart, selectionEnd = selectionStart, precision = 'day') {
    if (selectionEnd > selectionStart) {
        return applyDateDeleteRange(parts, selectionStart, selectionEnd, precision);
    }

    const current = normalize(parts, precision);
    const text = textFromParts(current, precision);
    const caret = Math.max(0, Math.min(selectionStart, text.length));
    let index = caret;

    if (text[index] === '/') {
        index += 1;
    }

    if (index >= text.length || text[index] === '/') {
        return pack(current, caret, precision);
    }

    const cleared = applyDateDeleteRange(current, index, index + 1, precision);

    return { ...cleared, caret: Math.min(caret, cleared.text.length) };
}

export function applyDateDeleteRange(parts, selectionStart, selectionEnd, precision = 'day') {
    const current = normalize(parts, precision);
    const text = textFromParts(current, precision);
    const start = Math.max(0, Math.min(selectionStart, text.length));
    const end = Math.max(start, Math.min(selectionEnd, text.length));
    const slashes = slashIndexes(text);
    const yearSlash = precision === 'month' ? slashes[0] : slashes[1];
    const { seg, offset } = locate(text, start, precision);
    const next = normalize({
        month: removeDigits(current.month, 0, start, end),
        day: precision === 'day' && slashes[0] !== undefined
            ? removeDigits(current.day, slashes[0] + 1, start, end)
            : '',
        year: yearSlash !== undefined
            ? removeDigits(current.year, yearSlash + 1, start, end)
            : current.year,
    }, precision);

    return pack(next, placeCaret(next, seg, Math.min(offset, next[seg].length), precision), precision);
}

/**
 * A full pasted date in MM/DD/YYYY, YYYY-MM-DD, or MMDDYYYY shape.
 * February is allowed through the 29th; the caller still rejects non-leap years.
 */
export function parseLooseDate(raw, precision = 'day') {
    const text = String(raw ?? '').trim();

    if (!text) {
        return null;
    }

    const pieces = text.split(/[^\d]+/).filter(Boolean);
    const digits = text.replace(/\D/g, '');
    let month = '';
    let day = '';
    let year = '';

    if (precision === 'month') {
        if (pieces.length === 2 && pieces[0].length <= 2 && pieces[1].length === 4) {
            month = pieces[0];
            year = pieces[1];
        } else if (pieces.length === 2 && pieces[0].length === 4 && pieces[1].length <= 2) {
            year = pieces[0];
            month = pieces[1];
        } else if (pieces.length === 1 && digits.length === 6) {
            month = digits.slice(0, 2);
            year = digits.slice(2);
        } else {
            return null;
        }
    } else if (pieces.length === 3 && pieces[2].length === 4 && pieces[0].length <= 2 && pieces[1].length <= 2) {
        month = pieces[0];
        day = pieces[1];
        year = pieces[2];
    } else if (pieces.length === 3 && pieces[0].length === 4 && pieces[1].length <= 2 && pieces[2].length <= 2) {
        year = pieces[0];
        month = pieces[1];
        day = pieces[2];
    } else if (pieces.length === 1 && digits.length === 8) {
        month = digits.slice(0, 2);
        day = digits.slice(2, 4);
        year = digits.slice(4);
    } else {
        return null;
    }

    month = month.padStart(2, '0');

    if (Number(month) < 1 || Number(month) > 12 || !/^\d{4}$/.test(year)) {
        return null;
    }

    if (precision === 'day') {
        day = day.padStart(2, '0');
        const max = maxDayForMonth(month);

        if (Number(day) < 1) {
            return null;
        }

        if (Number(day) > max) {
            day = String(max).padStart(2, '0');
        }
    }

    return {
        month,
        day: precision === 'day' ? day : '',
        year,
    };
}

function looksLikeCompleteDate(raw, precision) {
    const text = String(raw ?? '').trim();
    const pieces = text.split(/[^\d]+/).filter(Boolean);
    const digits = text.replace(/\D/g, '');

    if (precision === 'month') {
        return (pieces.length === 2 && pieces[0].length <= 2 && pieces[1].length === 4)
            || (pieces.length === 2 && pieces[0].length === 4 && pieces[1].length <= 2)
            || (pieces.length === 1 && digits.length === 6);
    }

    return (pieces.length === 3 && pieces[2].length === 4 && pieces[0].length <= 2 && pieces[1].length <= 2)
        || (pieces.length === 3 && pieces[0].length === 4 && pieces[1].length <= 2 && pieces[2].length <= 2)
        || (pieces.length === 1 && digits.length === 8);
}

function insertDateDigit(parts, caret, digit, precision) {
    const current = normalize(parts, precision);

    if (!/^\d$/.test(digit)) {
        return pack(current, caret, precision);
    }

    const { seg, offset } = resolveTarget(current, caret, precision);

    if (seg === 'day' && current.month.length < 2) {
        return pack(current, caret, precision);
    }

    if (seg === 'year') {
        const ready = precision === 'month'
            ? current.month.length === 2
            : current.month.length === 2 && current.day.length === 2;

        if (!ready) {
            return pack(current, caret, precision);
        }

        const written = writeYearDigit(current.year, offset, digit);

        if (!written.accepted) {
            return pack(current, caret, precision);
        }

        const next = normalize({ ...current, year: written.year }, precision);

        return pack(next, placeCaret(next, 'year', written.placed, precision), precision);
    }

    const max = seg === 'month' ? 12 : maxDayForMonth(current.month);
    const written = writeBoundedDigit(current[seg], offset, digit, max);

    if (!written.accepted) {
        return pack(current, caret, precision);
    }

    const next = normalize({ ...current, [seg]: written.value }, precision);
    const placed = written.value.length >= 2 && written.placed >= 2 ? 2 : written.placed;

    return pack(next, placeCaret(next, seg, placed, precision), precision);
}

function writeBoundedDigit(segment, offset, digit, max) {
    let index = offset;

    if (index > segment.length) {
        index = segment.length;
    }

    if (segment.length >= 2 && index >= 2) {
        return { value: segment, placed: index, accepted: false };
    }

    if (index >= segment.length) {
        const next = pushDigit(segment, digit, max);

        return { value: next, placed: next.length, accepted: next !== segment };
    }

    if (index === 0) {
        const rest = segment.slice(1);
        const first = pushDigit('', digit, max);

        if (!first) {
            return { value: segment, placed: index, accepted: false };
        }

        if (first.length === 2) {
            return { value: first, placed: 2, accepted: true };
        }

        if (rest && Number(first + rest) >= 1 && Number(first + rest) <= max) {
            return { value: first + rest, placed: 1, accepted: true };
        }

        return { value: first, placed: first.length, accepted: true };
    }

    const combined = segment[0] + digit;

    if (Number(combined) >= 1 && Number(combined) <= max) {
        return { value: combined, placed: 2, accepted: true };
    }

    return { value: segment, placed: index, accepted: false };
}

function pushDigit(segment, digit, max) {
    const number = Number(digit);

    if (segment.length >= 2) {
        return segment;
    }

    if (segment.length === 0) {
        if (number === 0) {
            return '0';
        }

        if (number * 10 > max) {
            return number <= max ? String(number).padStart(2, '0') : segment;
        }

        return digit;
    }

    const value = Number(segment + digit);

    return value >= 1 && value <= max ? segment + digit : segment;
}

function writeYearDigit(year, offset, digit) {
    if (offset < year.length) {
        return {
            year: year.slice(0, offset) + digit + year.slice(offset + 1),
            placed: offset + 1,
            accepted: true,
        };
    }

    if (year.length >= 4) {
        return { year, placed: offset, accepted: false };
    }

    return { year: year + digit, placed: year.length + 1, accepted: true };
}

function resolveTarget(parts, caret, precision) {
    const text = textFromParts(parts, precision);
    let { seg, offset } = locate(text, caret, precision);

    if (seg === 'month' && parts.month.length === 2 && offset >= parts.month.length) {
        seg = precision === 'month' ? 'year' : 'day';
        offset = 0;
    } else if (seg === 'day' && parts.day.length === 2 && offset >= parts.day.length) {
        seg = 'year';
        offset = 0;
    }

    return { seg, offset };
}

function locate(text, caret, precision) {
    const position = Math.max(0, Math.min(caret, text.length));
    const slashes = slashIndexes(text);
    const monthSlash = slashes[0];
    const daySlash = precision === 'day' ? slashes[1] : undefined;

    if (monthSlash === undefined || position <= monthSlash) {
        return { seg: 'month', offset: Math.min(position, monthSlash ?? text.length) };
    }

    if (precision === 'month') {
        return { seg: 'year', offset: position - monthSlash - 1 };
    }

    if (daySlash === undefined || position <= daySlash) {
        return { seg: 'day', offset: position - monthSlash - 1 };
    }

    return { seg: 'year', offset: position - daySlash - 1 };
}

function placeCaret(parts, seg, offset, precision) {
    const text = textFromParts(parts, precision);

    if (!text) {
        return 0;
    }

    const slashes = slashIndexes(text);
    const safeOffset = Math.max(0, offset);

    if (seg === 'month') {
        if (parts.month.length === 2 && safeOffset >= 2 && slashes[0] !== undefined) {
            return slashes[0] + 1;
        }

        return Math.min(safeOffset, parts.month.length);
    }

    if (seg === 'day') {
        if (slashes[0] === undefined) {
            return text.length;
        }

        if (parts.day.length === 2 && safeOffset >= 2 && slashes[1] !== undefined) {
            return slashes[1] + 1;
        }

        const start = slashes[0] + 1;
        const end = slashes[1] ?? text.length;

        return Math.min(start + safeOffset, end);
    }

    const slash = precision === 'month' ? slashes[0] : slashes[1];

    if (slash === undefined) {
        return text.length;
    }

    return Math.min(slash + 1 + safeOffset, text.length);
}

function normalize(parts, precision) {
    const month = String(parts.month ?? '').replace(/\D/g, '').slice(0, 2);
    let day = precision === 'day' ? String(parts.day ?? '').replace(/\D/g, '').slice(0, 2) : '';
    const year = String(parts.year ?? '').replace(/\D/g, '').slice(0, 4);

    if (precision === 'day' && month.length === 2 && day) {
        day = clampDay(day, month);
    }

    return { month, day, year };
}

function clampDay(day, month) {
    const max = maxDayForMonth(month);

    if (day.length === 1) {
        const number = Number(day);

        if (number === 0) {
            return day;
        }

        if (number * 10 > max) {
            return number <= max ? String(number).padStart(2, '0') : '';
        }

        return day;
    }

    const value = Number(day);

    if (value < 1) {
        return '';
    }

    if (value > max) {
        return String(max).padStart(2, '0');
    }

    return day;
}

function removeDigits(segment, startIndex, rangeStart, rangeEnd) {
    let next = '';

    for (let index = 0; index < segment.length; index += 1) {
        const position = startIndex + index;

        if (position < rangeStart || position >= rangeEnd) {
            next += segment[index];
        }
    }

    return next;
}

function slashIndexes(text) {
    const indexes = [];

    for (let index = 0; index < text.length; index += 1) {
        if (text[index] === '/') {
            indexes.push(index);
        }
    }

    return indexes;
}

function pack(parts, caret, precision) {
    const next = normalize(parts, precision);
    const text = textFromParts(next, precision);

    return {
        parts: next,
        text,
        caret: Math.max(0, Math.min(caret, text.length)),
    };
}

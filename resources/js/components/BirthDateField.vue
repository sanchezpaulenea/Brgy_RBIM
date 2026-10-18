<template>
    <div ref="rootEl">
        <label :for="inputId" class="rbim-label">
            {{ label }}<span v-if="required" class="rbim-required" aria-hidden="true">*</span>
        </label>
        <div class="relative">
            <input
                :id="inputId"
                ref="inputEl"
                :value="typed"
                type="text"
                inputmode="numeric"
                autocomplete="off"
                spellcheck="false"
                :placeholder="inputPlaceholder"
                :disabled="disabled"
                class="rbim-input pr-10"
                :class="{ 'rbim-input-error': error }"
                :aria-expanded="calendarOpen"
                aria-haspopup="dialog"
                @focus="onFocus"
                @blur="onBlur"
                @beforeinput="onBeforeInput"
                @paste="onPaste"
                @input="onFallbackInput"
                @keydown.escape.prevent="calendarOpen = false"
            >
            <button
                type="button"
                class="absolute inset-y-0 right-0 flex w-10 items-center justify-center transition hover:text-brand disabled:cursor-not-allowed"
                :class="calendarOpen ? 'text-brand' : 'text-slate-500'"
                :disabled="disabled"
                aria-label="Open calendar"
                :aria-expanded="calendarOpen"
                @mousedown.prevent="markCalendarPointer"
                @click="toggleCalendar"
            >
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path
                        fill-rule="evenodd"
                        d="M5.75 2a.75.75 0 01.75.75V4h7V2.75a.75.75 0 011.5 0V4h.25A2.75 2.75 0 0118 6.75v8.5A2.75 2.75 0 0115.25 18H4.75A2.75 2.75 0 012 15.25v-8.5A2.75 2.75 0 014.75 4H5V2.75A.75.75 0 015.75 2zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75z"
                        clip-rule="evenodd"
                    />
                </svg>
            </button>
        </div>
        <p v-if="showAge && summary" class="rbim-hint">{{ summary }}</p>
        <p v-if="error" class="rbim-error">{{ error }}</p>
        <Teleport to="body">
            <div
                v-if="calendarOpen"
                ref="panelEl"
                class="fixed z-[80] w-[17.5rem] rounded-lg border border-slate-200 bg-white p-3 shadow-lg"
                :style="panelStyle"
                role="dialog"
                aria-label="Calendar"
                @mousedown.prevent="markCalendarPointer"
            >
                <div class="mb-2 flex items-center justify-between gap-2">
                    <button
                        type="button"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-600 hover:bg-brand-muted hover:text-brand"
                        :aria-label="precision === 'month' ? 'Previous year' : 'Previous month'"
                        @click="shiftCalendar(-1)"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <p class="text-sm font-semibold text-slate-800">{{ calendarTitle }}</p>
                    <button
                        type="button"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-600 hover:bg-brand-muted hover:text-brand"
                        :aria-label="precision === 'month' ? 'Next year' : 'Next month'"
                        @click="shiftCalendar(1)"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
                <div v-if="precision === 'month'" class="grid grid-cols-3 gap-2">
                    <button
                        v-for="cell in monthCells"
                        :key="cell.iso"
                        type="button"
                        class="rounded-md px-2 py-2 text-sm font-medium"
                        :class="calendarDayClass(cell)"
                        :disabled="cell.disabled"
                        :aria-pressed="cell.selected"
                        @click="selectCalendarDate(cell.iso)"
                    >
                        {{ cell.label }}
                    </button>
                </div>
                <template v-else>
                    <div class="mb-1 grid grid-cols-7 text-center text-xs font-medium text-slate-500">
                        <span v-for="label in WEEKDAY_LABELS" :key="label">{{ label }}</span>
                    </div>
                    <div class="grid grid-cols-7 gap-y-1 text-center">
                        <span v-for="(cell, index) in calendarCells" :key="index" class="flex h-8 justify-center">
                            <button
                                v-if="cell"
                                type="button"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-md text-sm"
                                :class="calendarDayClass(cell)"
                                :disabled="cell.disabled"
                                :aria-pressed="cell.selected"
                                :aria-label="cell.label"
                                @click="selectCalendarDate(cell.iso)"
                            >
                                {{ cell.day }}
                            </button>
                        </span>
                    </div>
                </template>
            </div>
        </Teleport>
    </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import {
    applyDateBackspace,
    applyDateDeleteForward,
    applyDateDeleteRange,
    applyDateDigit,
    applyDatePaste,
    applyDateSeparator,
    emptyDateParts,
    isCompleteDate,
    maskDateTyping,
    textFromParts,
} from '@/utils/dateMask';
import { ageFromDateOfBirth, dateYearsAgo, todayDate } from '@/utils/format';

const OLDEST_SUPPORTED_AGE = 120;
const DEFAULT_FUTURE_YEARS = 10;
const WEEKDAY_LABELS = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];
const MONTH_LABELS = [
    'January',
    'February',
    'March',
    'April',
    'May',
    'June',
    'July',
    'August',
    'September',
    'October',
    'November',
    'December',
];

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    label: {
        type: String,
        default: 'Date of birth',
    },
    inputId: {
        type: String,
        default: 'birth-date',
    },
    placeholder: {
        type: String,
        default: '',
    },
    min: {
        type: String,
        default: '',
    },
    max: {
        type: String,
        default: '',
    },
    showAge: {
        type: Boolean,
        default: true,
    },
    error: {
        type: String,
        default: '',
    },
    required: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    precision: {
        type: String,
        default: 'day',
        validator: (value) => ['day', 'month'].includes(value),
    },
});

const emit = defineEmits(['update:modelValue']);

const rootEl = ref(null);
const inputEl = ref(null);
const panelEl = ref(null);
const typed = ref('');
const parts = ref(emptyDateParts());
const focused = ref(false);
const calendarOpen = ref(false);
const panelStyle = ref({ top: '0px', left: '0px' });
const viewYear = ref(2000);
const viewMonth = ref(1);
let maskHandled = false;
let caretToken = 0;
let revealedIso = '';
let pointerInCalendar = false;
let blurToken = 0;

const maxIso = computed(() => {
    if (props.max) {
        return props.max;
    }

    if (props.min) {
        return dateYearsAgo(-DEFAULT_FUTURE_YEARS, props.min);
    }

    return todayDate();
});

const minIso = computed(() => props.min || dateYearsAgo(OLDEST_SUPPORTED_AGE, maxIso.value));

const inputPlaceholder = computed(() => {
    if (props.placeholder) {
        return props.placeholder;
    }

    return props.precision === 'month' ? 'MM/YYYY' : 'MM/DD/YYYY';
});

const summary = computed(() => {
    if (!props.showAge || props.precision === 'month') {
        return '';
    }

    const age = ageFromDateOfBirth(props.modelValue);

    return age === null ? '' : `${age} years old`;
});

function pad(value) {
    return String(value).padStart(2, '0');
}

function toIso({ year, month, day }) {
    return `${String(year).padStart(4, '0')}-${pad(month)}-${pad(day)}`;
}

function parseIso(value) {
    const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(String(value ?? '').trim());

    if (!match) {
        return null;
    }

    return {
        year: Number(match[1]),
        month: Number(match[2]),
        day: Number(match[3]),
    };
}

function formatDisplay(iso) {
    const parsed = parseIso(iso);

    if (!parsed) {
        return '';
    }

    if (props.precision === 'month') {
        return `${pad(parsed.month)}/${parsed.year}`;
    }

    return `${pad(parsed.month)}/${pad(parsed.day)}/${parsed.year}`;
}

function daysInMonth(year, month) {
    return new Date(year, month, 0).getDate();
}

/**
 * Encoders type dates in whatever shape is quickest, so `/`, `-`, `.`, and
 * spaces are all accepted as separators, as is a run of bare digits.
 */
function parseTyped(value) {
    const text = String(value ?? '').trim();

    if (!text) {
        return { empty: true };
    }

    const digits = text.replace(/\D/g, '');
    const parts = text.split(/[^\d]+/).filter(Boolean);

    if (props.precision === 'month') {
        // MM/YYYY
        if (parts.length === 2 && parts[1].length === 4) {
            return fromParts(Number(parts[1]), Number(parts[0]), 1);
        }

        // YYYY-MM
        if (parts.length === 2 && parts[0].length === 4) {
            return fromParts(Number(parts[0]), Number(parts[1]), 1);
        }

        // MMYYYY
        if (parts.length === 1 && digits.length === 6) {
            return fromParts(Number(digits.slice(2)), Number(digits.slice(0, 2)), 1);
        }

        return { invalid: true };
    }

    // MM/DD/YYYY
    if (parts.length === 3 && parts[2].length === 4) {
        return fromParts(Number(parts[2]), Number(parts[0]), Number(parts[1]));
    }

    // YYYY-MM-DD
    if (parts.length === 3 && parts[0].length === 4) {
        return fromParts(Number(parts[0]), Number(parts[1]), Number(parts[2]));
    }

    // MMDDYYYY
    if (parts.length === 1 && digits.length === 8) {
        return fromParts(Number(digits.slice(4)), Number(digits.slice(0, 2)), Number(digits.slice(2, 4)));
    }

    return { invalid: true };
}

function fromParts(year, month, day) {
    if (month < 1 || month > 12 || day < 1 || day > daysInMonth(year, month)) {
        return { invalid: true };
    }

    return { iso: toIso({ year, month, day }) };
}

function isSelectable(iso) {
    if (!iso) {
        return true;
    }

    return iso >= minIso.value && iso <= maxIso.value;
}

function commit(iso) {
    if (iso !== props.modelValue) {
        emit('update:modelValue', iso);
    }

    if (!focused.value) {
        typed.value = formatDisplay(iso);
    }
}

function showParts(nextParts, caret = null) {
    parts.value = {
        month: nextParts.month,
        day: nextParts.day,
        year: nextParts.year,
    };
    typed.value = textFromParts(parts.value, props.precision);

    if (caret !== null) {
        queueCaret(caret);
    }

    publish(parts.value);
}

function queueCaret(caret) {
    const token = ++caretToken;

    nextTick(() => {
        if (token !== caretToken) {
            return;
        }

        const element = inputEl.value;

        if (!element || document.activeElement !== element) {
            return;
        }

        const position = Math.max(0, Math.min(caret, element.value.length));
        element.setSelectionRange(position, position);
    });
}

function publish(current) {
    const text = textFromParts(current, props.precision);

    if (!text) {
        commit('');
        calendarOpen.value = false;

        return;
    }

    if (!isCompleteDate(current, props.precision)) {
        return;
    }

    const parsed = parseTyped(text);

    if (parsed.iso && isSelectable(parsed.iso)) {
        commit(parsed.iso);

        if (focused.value && !props.disabled) {
            revealCalendar(parsed.iso);
        }

        return;
    }

    calendarOpen.value = false;
}

function selectionOf(event) {
    const element = event.target;

    return {
        start: element.selectionStart ?? 0,
        end: element.selectionEnd ?? element.selectionStart ?? 0,
    };
}

function markHandled() {
    maskHandled = true;
    nextTick(() => {
        maskHandled = false;
    });
}

function onBeforeInput(event) {
    const type = event.inputType;

    if (type === 'insertFromPaste' || type === 'insertFromDrop') {
        event.preventDefault();

        if (maskHandled) {
            return;
        }

        markHandled();
        const { start, end } = selectionOf(event);
        showParts(...spreadResult(applyDatePaste(parts.value, start, end, event.data ?? '', props.precision)));

        return;
    }

    if (type === 'insertText' || type === 'insertReplacementText') {
        event.preventDefault();
        markHandled();
        const { start, end } = selectionOf(event);
        const data = event.data ?? '';

        if (data.length > 1) {
            showParts(...spreadResult(applyDatePaste(parts.value, start, end, data, props.precision)));

            return;
        }

        if (/^\d$/.test(data)) {
            showParts(...spreadResult(applyDateDigit(parts.value, start, end, data, props.precision)));

            return;
        }

        if (data === '/' || data === '-' || data === '.') {
            const cleared = end > start
                ? applyDateDeleteRange(parts.value, start, end, props.precision)
                : { parts: parts.value, caret: start };
            showParts(...spreadResult(applyDateSeparator(cleared.parts, cleared.caret, props.precision)));
        }

        return;
    }

    if (type === 'deleteContentBackward' || type === 'deleteWordBackward' || type === 'deleteSoftLineBackward') {
        event.preventDefault();
        markHandled();
        const { start, end } = selectionOf(event);
        showParts(...spreadResult(applyDateBackspace(parts.value, start, end, props.precision)));

        return;
    }

    if (type === 'deleteContentForward' || type === 'deleteWordForward') {
        event.preventDefault();
        markHandled();
        const { start, end } = selectionOf(event);
        showParts(...spreadResult(applyDateDeleteForward(parts.value, start, end, props.precision)));

        return;
    }

    if (type === 'deleteByCut') {
        event.preventDefault();
        markHandled();
        const { start, end } = selectionOf(event);
        showParts(...spreadResult(applyDateDeleteRange(parts.value, start, end, props.precision)));
    }
}

function onPaste(event) {
    event.preventDefault();

    if (maskHandled) {
        return;
    }

    markHandled();
    const { start, end } = selectionOf(event);
    const raw = event.clipboardData?.getData('text') ?? '';
    showParts(...spreadResult(applyDatePaste(parts.value, start, end, raw, props.precision)));
}

function onFallbackInput(event) {
    if (maskHandled) {
        maskHandled = false;

        if (event.target.value !== typed.value) {
            event.target.value = typed.value;
        }

        return;
    }

    const raw = event.target.value;
    const masked = maskDateTyping(raw, props.precision);

    if (masked.rejected) {
        event.target.value = typed.value;

        return;
    }

    showParts(masked.parts, masked.caret);
}

function spreadResult(result) {
    return [result.parts, result.caret];
}

function onFocus() {
    blurToken += 1;
    focused.value = true;
}

function onBlur() {
    const token = ++blurToken;

    window.setTimeout(() => {
        if (token !== blurToken) {
            return;
        }

        if (pointerInCalendar || panelEl.value?.contains(document.activeElement)) {
            pointerInCalendar = false;
            focused.value = true;
            inputEl.value?.focus();

            return;
        }

        focused.value = false;
        calendarOpen.value = false;
        settleTypedValue();
    }, 0);
}

function settleTypedValue() {
    const parsed = parseTyped(typed.value);

    if (parsed.empty) {
        commit('');
        showFromIso('');

        return;
    }

    if (parsed.iso && isSelectable(parsed.iso)) {
        commit(parsed.iso);
        showFromIso(parsed.iso);

        return;
    }

    showFromIso(props.modelValue);
}

function showFromIso(iso) {
    const parsed = parseIso(iso) ?? parseIso(`${iso}-01`);

    if (!parsed) {
        parts.value = emptyDateParts();
        typed.value = '';

        return;
    }

    parts.value = {
        month: pad(parsed.month),
        day: props.precision === 'month' ? '' : pad(parsed.day),
        year: String(parsed.year).padStart(4, '0'),
    };
    typed.value = textFromParts(parts.value, props.precision);
}

const selectedIso = computed(() => {
    if (!isCompleteDate(parts.value, props.precision)) {
        return '';
    }

    const parsed = parseTyped(typed.value);

    return parsed.iso && isSelectable(parsed.iso) ? parsed.iso : '';
});

const calendarTitle = computed(() => (
    props.precision === 'month'
        ? String(viewYear.value)
        : `${MONTH_LABELS[viewMonth.value - 1]} ${viewYear.value}`
));

const calendarCells = computed(() => {
    const year = viewYear.value;
    const month = viewMonth.value;
    const leading = new Date(year, month - 1, 1).getDay();
    const total = daysInMonth(year, month);
    const cells = Array.from({ length: leading }, () => null);

    for (let day = 1; day <= total; day += 1) {
        const iso = toIso({ year, month, day });

        cells.push({
            day,
            iso,
            label: `${MONTH_LABELS[month - 1]} ${day}, ${year}`,
            disabled: !isSelectable(iso),
            selected: iso === selectedIso.value,
            today: iso === todayDate(),
        });
    }

    return cells;
});

const monthCells = computed(() => MONTH_LABELS.map((label, index) => {
    const month = index + 1;
    const iso = toIso({ year: viewYear.value, month, day: 1 });

    return {
        label: label.slice(0, 3),
        iso,
        disabled: !isSelectable(iso),
        selected: selectedIso.value.slice(0, 7) === iso.slice(0, 7),
    };
}));

function calendarDayClass(cell) {
    if (cell.selected) {
        return 'cursor-pointer bg-brand font-semibold text-white';
    }

    if (cell.disabled) {
        return 'cursor-not-allowed text-slate-300';
    }

    return cell.today
        ? 'cursor-pointer text-slate-700 ring-1 ring-inset ring-brand/40 hover:bg-brand-muted'
        : 'cursor-pointer text-slate-700 hover:bg-brand-muted';
}

function markCalendarPointer() {
    pointerInCalendar = true;
    window.setTimeout(() => {
        pointerInCalendar = false;
    }, 300);
}

function revealCalendar(iso) {
    const parsed = parseIso(iso) ?? parseIso(`${iso}-01`);

    if (!parsed) {
        return;
    }

    if (!calendarOpen.value || revealedIso !== iso) {
        viewYear.value = parsed.year;
        viewMonth.value = parsed.month;
        revealedIso = iso;
    }

    calendarOpen.value = true;
}

function toggleCalendar() {
    if (props.disabled) {
        return;
    }

    if (calendarOpen.value) {
        calendarOpen.value = false;

        return;
    }

    const parsed = parseTyped(typed.value);

    if (parsed.iso && isSelectable(parsed.iso)) {
        revealCalendar(parsed.iso);

        return;
    }

    const today = parseIso(todayDate());
    const month = parts.value.month.length === 2 ? Number(parts.value.month) : today?.month;
    const year = parts.value.year.length === 4 ? Number(parts.value.year) : today?.year;

    viewYear.value = year || today?.year || 2000;
    viewMonth.value = month >= 1 && month <= 12 ? month : (today?.month || 1);
    calendarOpen.value = true;
}

function shiftCalendar(delta) {
    if (props.precision === 'month') {
        viewYear.value += delta;

        return;
    }

    const next = new Date(viewYear.value, viewMonth.value - 1 + delta, 1);
    viewYear.value = next.getFullYear();
    viewMonth.value = next.getMonth() + 1;
}

function selectCalendarDate(iso) {
    if (!isSelectable(iso)) {
        return;
    }

    showFromIso(iso);
    commit(iso);
    calendarOpen.value = false;
}

function placePanel() {
    const anchor = inputEl.value;
    const panel = panelEl.value;

    if (!anchor || !panel) {
        return;
    }

    const rect = anchor.getBoundingClientRect();
    const width = panel.offsetWidth || 280;
    const height = panel.offsetHeight || 300;
    const gap = 6;
    const margin = 8;
    const below = rect.bottom + gap;
    const top = below + height <= window.innerHeight - margin
        ? below
        : Math.max(margin, rect.top - height - gap);
    let left = rect.left;

    if (left + width > window.innerWidth - margin) {
        left = window.innerWidth - width - margin;
    }

    panelStyle.value = {
        top: `${top}px`,
        left: `${Math.max(margin, left)}px`,
    };
}

function onPointerDown(event) {
    const target = event.target;

    if (panelEl.value?.contains(target) || rootEl.value?.contains(target)) {
        return;
    }

    calendarOpen.value = false;
}

function onEscape(event) {
    if (event.key === 'Escape') {
        calendarOpen.value = false;
    }
}

function bindCalendarListeners() {
    window.addEventListener('resize', placePanel);
    window.addEventListener('scroll', placePanel, true);
    document.addEventListener('pointerdown', onPointerDown);
    document.addEventListener('keydown', onEscape);
}

function unbindCalendarListeners() {
    window.removeEventListener('resize', placePanel);
    window.removeEventListener('scroll', placePanel, true);
    document.removeEventListener('pointerdown', onPointerDown);
    document.removeEventListener('keydown', onEscape);
}

watch(calendarOpen, (open) => {
    if (!open) {
        revealedIso = '';
        unbindCalendarListeners();

        return;
    }

    nextTick(placePanel);
    bindCalendarListeners();
});

watch(() => props.disabled, (disabled) => {
    if (disabled) {
        calendarOpen.value = false;
    }
});

onBeforeUnmount(unbindCalendarListeners);

watch(() => props.modelValue, (value) => {
    if (!focused.value) {
        showFromIso(value);
    }
}, { immediate: true });
</script>

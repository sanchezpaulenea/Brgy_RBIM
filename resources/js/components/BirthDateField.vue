<template>
    <div>
        <label :for="inputId" class="rbim-label">
            {{ label }}<span v-if="required" class="rbim-required" aria-hidden="true">*</span>
        </label>
        <div class="relative">
            <input
                :id="inputId"
                v-model="typed"
                type="text"
                autocomplete="off"
                :placeholder="inputPlaceholder"
                :disabled="disabled"
                class="rbim-input pr-10"
                :class="{ 'rbim-input-error': error }"
                @focus="focused = true"
                @blur="onBlur"
                @input="onTypedInput"
            >
            <span
                class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-500"
                aria-hidden="true"
            >
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path
                        fill-rule="evenodd"
                        d="M5.75 2a.75.75 0 01.75.75V4h7V2.75a.75.75 0 011.5 0V4h.25A2.75 2.75 0 0118 6.75v8.5A2.75 2.75 0 0115.25 18H4.75A2.75 2.75 0 012 15.25v-8.5A2.75 2.75 0 014.75 4H5V2.75A.75.75 0 015.75 2zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75z"
                        clip-rule="evenodd"
                    />
                </svg>
            </span>
            <input
                :type="precision === 'month' ? 'month' : 'date'"
                :value="pickerValue"
                :min="pickerMin"
                :max="pickerMax"
                :disabled="disabled"
                tabindex="-1"
                aria-label="Open calendar"
                class="absolute inset-y-0 right-0 w-10 cursor-pointer opacity-0"
                @change="onPickerChange"
            >
        </div>
        <p v-if="showAge && summary" class="rbim-hint">{{ summary }}</p>
        <p v-if="error" class="rbim-error">{{ error }}</p>
    </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { ageFromDateOfBirth, dateYearsAgo, todayDate } from '@/utils/format';

const OLDEST_SUPPORTED_AGE = 120;
const DEFAULT_FUTURE_YEARS = 10;

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

const typed = ref('');
const focused = ref(false);

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

const pickerMin = computed(() => toPickerValue(minIso.value));
const pickerMax = computed(() => toPickerValue(maxIso.value));
const pickerValue = computed(() => toPickerValue(props.modelValue));

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

function toPickerValue(iso) {
    if (!iso) {
        return '';
    }

    const parsed = parseIso(iso) ?? parseIso(`${iso}-01`);

    if (!parsed) {
        return '';
    }

    if (props.precision === 'month') {
        return `${String(parsed.year).padStart(4, '0')}-${pad(parsed.month)}`;
    }

    return toIso(parsed);
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

function onTypedInput() {
    const parsed = parseTyped(typed.value);

    if (parsed.empty) {
        commit('');

        return;
    }

    if (parsed.iso && isSelectable(parsed.iso)) {
        commit(parsed.iso);
    }
}

function onBlur() {
    focused.value = false;

    const parsed = parseTyped(typed.value);

    if (parsed.empty) {
        commit('');

        return;
    }

    if (parsed.iso && isSelectable(parsed.iso)) {
        commit(parsed.iso);

        return;
    }

    typed.value = formatDisplay(props.modelValue);
}

function onPickerChange(event) {
    const value = event.target.value;

    if (!value) {
        commit('');

        return;
    }

    const iso = props.precision === 'month' ? `${value}-01` : value;

    if (isSelectable(iso)) {
        commit(iso);
    }
}

watch(() => props.modelValue, (value) => {
    if (!focused.value) {
        typed.value = formatDisplay(value);
    }
}, { immediate: true });
</script>

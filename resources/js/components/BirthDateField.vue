<template>
    <div ref="root" class="relative">
        <label :for="inputId" class="rbim-label">
            {{ label }}
            <span v-if="required" class="rbim-required" aria-hidden="true">*</span>
        </label>
        <button
            :id="inputId"
            type="button"
            class="rbim-input flex w-full items-center justify-between gap-2 text-left"
            :class="{ 'rbim-input-error': error }"
            :disabled="disabled"
            :aria-expanded="open"
            aria-haspopup="dialog"
            @click="togglePanel"
        >
            <span :class="displayText ? 'text-slate-900' : 'text-slate-400'">
                {{ displayText || placeholder }}
            </span>
            <svg class="h-4 w-4 shrink-0 text-slate-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
        </button>

        <div
            v-if="open"
            class="absolute z-20 mt-1 w-full rounded-lg border border-slate-200 bg-white p-3 shadow-lg"
        >
            <div class="grid grid-cols-3 gap-2">
                <div ref="monthRoot" class="relative">
                    <span class="mb-1 block text-xs font-medium text-slate-500">Month</span>
                    <button
                        type="button"
                        class="rbim-input flex w-full items-center justify-between py-1.5 text-sm"
                        :aria-expanded="activePart === 'month'"
                        @click="togglePart('month')"
                    >
                        <span>{{ monthLabel }}</span>
                        <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <ul
                        v-if="activePart === 'month'"
                        class="absolute z-30 mt-1 max-h-36 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white py-1 shadow-lg"
                    >
                        <li
                            v-for="option in monthOptions"
                            :key="option.value"
                            class="cursor-pointer px-2.5 py-1.5 text-sm"
                            :class="Number(month) === option.value ? 'bg-brand text-white' : 'text-slate-700 hover:bg-brand-muted'"
                            :data-selected="Number(month) === option.value ? '' : undefined"
                            @mousedown.prevent="selectPart('month', option.value)"
                        >
                            {{ option.label }}
                        </li>
                    </ul>
                </div>

                <div ref="dayRoot" class="relative">
                    <span class="mb-1 block text-xs font-medium text-slate-500">Day</span>
                    <button
                        type="button"
                        class="rbim-input flex w-full items-center justify-between py-1.5 text-sm"
                        :aria-expanded="activePart === 'day'"
                        @click="togglePart('day')"
                    >
                        <span>{{ dayLabel }}</span>
                        <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <ul
                        v-if="activePart === 'day'"
                        ref="dayList"
                        class="absolute z-30 mt-1 max-h-36 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white py-1 shadow-lg"
                    >
                        <li
                            v-for="option in dayOptions"
                            :key="option"
                            class="cursor-pointer px-2.5 py-1.5 text-sm"
                            :class="Number(day) === option ? 'bg-brand text-white' : 'text-slate-700 hover:bg-brand-muted'"
                            :data-selected="Number(day) === option ? '' : undefined"
                            @mousedown.prevent="selectPart('day', option)"
                        >
                            {{ option }}
                        </li>
                    </ul>
                </div>

                <div ref="yearRoot" class="relative">
                    <span class="mb-1 block text-xs font-medium text-slate-500">Year</span>
                    <button
                        type="button"
                        class="rbim-input flex w-full items-center justify-between py-1.5 text-sm"
                        :aria-expanded="activePart === 'year'"
                        @click="togglePart('year')"
                    >
                        <span>{{ yearLabel }}</span>
                        <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <ul
                        v-if="activePart === 'year'"
                        ref="yearList"
                        class="absolute z-30 mt-1 max-h-36 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white py-1 shadow-lg"
                    >
                        <li
                            v-for="option in yearOptions"
                            :key="option"
                            class="cursor-pointer px-2.5 py-1.5 text-sm"
                            :class="Number(year) === option ? 'bg-brand text-white' : 'text-slate-700 hover:bg-brand-muted'"
                            :data-selected="Number(year) === option ? '' : undefined"
                            @mousedown.prevent="selectPart('year', option)"
                        >
                            {{ option }}
                        </li>
                    </ul>
                </div>
            </div>
            <p v-if="summary" class="mt-2 text-xs text-slate-500">{{ summary }}</p>
        </div>

        <p v-if="error" class="rbim-error">{{ error }}</p>
    </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const MONTH_NAMES = [
    'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
    'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
];

const MONTH_NAMES_FULL = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December',
];

const OLDEST_SUPPORTED_AGE = 120;

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
        default: 'Select date of birth',
    },
    max: {
        type: String,
        default: '',
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
});

const emit = defineEmits(['update:modelValue']);

const open = ref(false);
const activePart = ref(null);
const root = ref(null);
const monthRoot = ref(null);
const dayRoot = ref(null);
const yearRoot = ref(null);
const dayList = ref(null);
const yearList = ref(null);
const year = ref('');
const month = ref('');
const day = ref('');

const latest = computed(() => parse(props.max) ?? parse(new Date().toISOString().slice(0, 10)));

const yearOptions = computed(() => {
    const newest = latest.value.year;

    return Array.from({ length: OLDEST_SUPPORTED_AGE + 1 }, (unused, index) => newest - index);
});

const monthOptions = computed(() => {
    const limit = Number(year.value) === latest.value.year ? latest.value.month : 12;

    return MONTH_NAMES
        .slice(0, year.value ? limit : 12)
        .map((label, index) => ({ value: index + 1, label }));
});

const dayOptions = computed(() => {
    if (!month.value) {
        return Array.from({ length: 31 }, (unused, index) => index + 1);
    }

    const selectedYear = Number(year.value) || latest.value.year;
    let total = new Date(selectedYear, Number(month.value), 0).getDate();

    if (selectedYear === latest.value.year && Number(month.value) === latest.value.month) {
        total = Math.min(total, latest.value.day);
    }

    return Array.from({ length: total }, (unused, index) => index + 1);
});

const monthLabel = computed(() => (
    month.value ? MONTH_NAMES[Number(month.value) - 1] : '—'
));

const dayLabel = computed(() => (day.value ? String(day.value) : '—'));

const yearLabel = computed(() => (year.value ? String(year.value) : '—'));

const displayText = computed(() => {
    if (!isComplete()) {
        return '';
    }

    const monthName = MONTH_NAMES_FULL[Number(month.value) - 1] ?? MONTH_NAMES[Number(month.value) - 1];

    return `${monthName} ${day.value}, ${year.value}`;
});

const summary = computed(() => {
    if (!isComplete()) {
        return '';
    }

    const age = ageInYears();

    return age === null ? '' : `${age} years old`;
});

function parse(value) {
    const match = /^(\d{4})-(\d{2})-(\d{2})/.exec(String(value ?? ''));

    if (!match) {
        return null;
    }

    return {
        year: Number(match[1]),
        month: Number(match[2]),
        day: Number(match[3]),
    };
}

function isComplete() {
    return Boolean(year.value && month.value && day.value);
}

function ageInYears() {
    const today = new Date();
    const birth = new Date(Number(year.value), Number(month.value) - 1, Number(day.value));

    if (Number.isNaN(birth.getTime())) {
        return null;
    }

    let age = today.getFullYear() - birth.getFullYear();
    const beforeBirthdayThisYear = today.getMonth() < birth.getMonth()
        || (today.getMonth() === birth.getMonth() && today.getDate() < birth.getDate());

    if (beforeBirthdayThisYear) {
        age -= 1;
    }

    return age < 0 ? null : age;
}

function togglePanel() {
    if (props.disabled) {
        return;
    }

    open.value = !open.value;

    if (!open.value) {
        activePart.value = null;
    }
}

function togglePart(part) {
    activePart.value = activePart.value === part ? null : part;
}

function selectPart(part, value) {
    if (part === 'month') {
        month.value = value;
    } else if (part === 'day') {
        day.value = value;
    } else {
        year.value = value;
    }

    activePart.value = null;
}

function scrollSelected(listRef) {
    nextTick(() => {
        listRef.value?.querySelector('[data-selected]')?.scrollIntoView({ block: 'nearest' });
    });
}

function closeOnOutsidePointer(event) {
    const partRoots = [monthRoot.value, dayRoot.value, yearRoot.value];
    const insidePart = partRoots.some((element) => element?.contains(event.target));

    if (insidePart) {
        return;
    }

    if (root.value?.contains(event.target)) {
        activePart.value = null;

        return;
    }

    open.value = false;
    activePart.value = null;
}

onMounted(() => document.addEventListener('pointerdown', closeOnOutsidePointer, true));
onBeforeUnmount(() => document.removeEventListener('pointerdown', closeOnOutsidePointer, true));

watch(activePart, (part) => {
    if (part === 'day') {
        scrollSelected(dayList);
    }

    if (part === 'year') {
        scrollSelected(yearList);
    }
});

watch(() => props.modelValue, (value) => {
    const parsed = parse(value);

    if (!parsed) {
        if (!value) {
            year.value = '';
            month.value = '';
            day.value = '';
        }

        return;
    }

    year.value = parsed.year;
    month.value = parsed.month;
    day.value = parsed.day;
}, { immediate: true });

watch([year, month], () => {
    const available = dayOptions.value;

    if (day.value && !available.includes(Number(day.value))) {
        day.value = available[available.length - 1];
    }

    if (month.value && !monthOptions.value.some((option) => option.value === Number(month.value))) {
        month.value = '';
    }
});

watch([year, month, day], () => {
    if (!isComplete()) {
        if (open.value) {
            emit('update:modelValue', '');
        }

        return;
    }

    const iso = [
        String(year.value).padStart(4, '0'),
        String(month.value).padStart(2, '0'),
        String(day.value).padStart(2, '0'),
    ].join('-');

    if (iso !== props.modelValue) {
        emit('update:modelValue', iso);
    }

    if (open.value) {
        open.value = false;
        activePart.value = null;
    }
});
</script>

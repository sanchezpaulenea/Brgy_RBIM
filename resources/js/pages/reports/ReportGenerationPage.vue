<template>
    <AppLayout title="Report Generation">
        <div class="space-y-6">
            <div>
                <h1 class="text-xl font-semibold text-slate-900">Report Generation</h1>
                <p class="mt-1 text-sm text-slate-600">
                    Choose a category, filter the records, then preview and export the master list.
                </p>
            </div>

            <ol class="flex flex-wrap gap-2">
                <li
                    v-for="(label, index) in steps"
                    :key="label"
                    class="rounded-full px-3 py-1 text-xs font-semibold"
                    :class="index === step ? 'bg-brand text-white' : 'bg-brand-muted text-slate-600'"
                >
                    {{ index + 1 }}. {{ label }}
                </li>
            </ol>

            <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ error }}
            </div>

            <section v-if="step === 0" class="space-y-4">
                <div v-if="loadingCategories" class="rbim-card p-8 text-center text-sm text-slate-500">
                    Loading categories...
                </div>
                <div v-else-if="!categories.length" class="rbim-card p-8 text-center text-sm text-slate-500">
                    No report categories are available.
                </div>
                <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <button
                        v-for="item in categories"
                        :key="item.key"
                        type="button"
                        class="rbim-card p-5 text-left transition hover:border-brand"
                        :class="selectedCategory?.key === item.key ? 'border-brand ring-2 ring-brand/20' : ''"
                        @click="chooseCategory(item)"
                    >
                        <span class="block text-base font-semibold text-slate-900">{{ item.label }}</span>
                        <span class="mt-1 block text-sm text-slate-500">{{ levelLabel(item.level) }}</span>
                    </button>
                </div>
            </section>

            <section v-else-if="step === 1 && selectedCategory" class="rbim-card space-y-5 p-5">
                <h2 class="text-base font-semibold text-slate-900">{{ selectedCategory.label }} filters</h2>
                <p class="text-sm text-slate-600">Every filter below applies together.</p>
                <div
                    v-for="filter in selectedCategory.filters"
                    :key="filter.key"
                    class="space-y-3 rounded-lg border border-slate-200 p-4"
                >
                    <h3 class="text-sm font-semibold text-slate-900">{{ filter.label }}</h3>

                    <fieldset v-if="filter.type === 'lookup'" class="space-y-3">
                        <legend class="sr-only">{{ filter.label }}</legend>
                        <label
                            v-for="mode in filter.modes"
                            :key="mode.value"
                            class="flex items-center gap-2 text-sm text-slate-700"
                        >
                            <input
                                type="radio"
                                class="text-brand focus:ring-brand"
                                :name="`report-filter-${filter.key}`"
                                :checked="filterState[filter.key]?.mode === mode.value"
                                @change="setFilterMode(filter, mode.value)"
                            >
                            {{ mode.label }}
                        </label>

                        <div v-if="filterState[filter.key]?.mode === 'one'" class="max-w-md">
                            <LookupCombobox
                                :model-value="filterState[filter.key].ids[0] ?? null"
                                :options="dropdownOptions(filter.key)"
                                :limit="5"
                                :label="`Search ${filter.label.toLowerCase()}`"
                                :placeholder="`Type to search ${filter.label.toLowerCase()}`"
                                :input-id="`report-filter-${filter.key}-one`"
                                hint="Up to 5 matches are shown."
                                @update:model-value="(id) => setSingleId(filter.key, id)"
                                @update:query="(value) => onFilterQuery(filter.key, value)"
                            />
                        </div>

                        <div
                            v-else-if="filterState[filter.key]?.mode === 'multiple'"
                            class="max-h-52 max-w-md space-y-2 overflow-y-auto rounded-lg border border-slate-200 p-3"
                        >
                            <p v-if="!(optionResults[filter.key] || []).length" class="text-sm text-slate-500">
                                No records are available.
                            </p>
                            <label
                                v-for="option in optionResults[filter.key] || []"
                                :key="option.id"
                                class="flex items-center gap-2 text-sm text-slate-700"
                            >
                                <input
                                    type="checkbox"
                                    class="text-brand focus:ring-brand"
                                    :checked="filterState[filter.key].ids.includes(Number(option.id))"
                                    @change="toggleChecked(filter.key, option.id)"
                                >
                                {{ option.label }}
                            </label>
                        </div>
                    </fieldset>

                    <fieldset v-else class="space-y-3">
                        <legend class="sr-only">{{ filter.label }}</legend>
                        <label
                            v-for="choice in filter.choices"
                            :key="choice.value"
                            class="flex items-center gap-2 text-sm text-slate-700"
                        >
                            <input
                                v-model="filterState[filter.key].mode"
                                type="radio"
                                class="text-brand focus:ring-brand"
                                :name="`report-filter-${filter.key}`"
                                :value="choice.value"
                            >
                            {{ choice.label }}
                        </label>
                        <div v-if="numericChoice(filter)" class="max-w-xs">
                            <label :for="`report-filter-${filter.key}-number`" class="rbim-label">Number</label>
                            <input
                                :id="`report-filter-${filter.key}-number`"
                                v-model="filterState[filter.key].value"
                                type="number"
                                class="rbim-input"
                                :min="numericChoice(filter).min"
                                :max="numericChoice(filter).max"
                            >
                        </div>
                    </fieldset>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="button" class="rbim-btn-outline" @click="step = 0">Back</button>
                    <button type="button" class="rbim-btn" :disabled="!filterReady" @click="step = 2">
                        Continue
                    </button>
                </div>
            </section>

            <section v-else-if="step === 2 && selectedCategory" class="rbim-card space-y-5 p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-base font-semibold text-slate-900">Columns</h2>
                    <div class="flex gap-2">
                        <button type="button" class="rbim-btn-outline" @click="useDefaultColumns">Defaults</button>
                        <button type="button" class="rbim-btn-outline" @click="selectAllColumns">Select all</button>
                    </div>
                </div>
                <p class="text-sm text-slate-600">
                    A column that names this filter stays in the report when the filter matches more than one value, and the rows are sorted by it.
                </p>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    <label
                        v-for="column in pickerColumns"
                        :key="column.key"
                        class="flex items-start gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700"
                    >
                        <input
                            type="checkbox"
                            class="mt-0.5 text-brand focus:ring-brand"
                            :checked="selectedColumns.includes(column.key)"
                            :disabled="column.locked"
                            @change="toggleColumn(column.key)"
                        >
                        <span>
                            <span class="font-medium text-slate-900">{{ column.label }}</span>
                            <span v-if="column.is_group" class="mt-1 block text-xs text-slate-500">
                                {{ column.columns.map((child) => child.label).join(', ') }}
                            </span>
                        </span>
                    </label>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button type="button" class="rbim-btn-outline" @click="step = 1">Back</button>
                    <button type="button" class="rbim-btn" :disabled="!selectedColumns.length || previewLoading" @click="runPreview(1, true)">
                        {{ previewLoading ? 'Building preview...' : 'Preview report' }}
                    </button>
                </div>
            </section>

            <section v-else-if="step === 3 && preview" class="space-y-4">
                <div class="rbim-card space-y-4 p-5">
                    <div class="max-w-xl space-y-4">
                        <div>
                            <label for="report-title" class="rbim-label">Report title</label>
                            <input
                                id="report-title"
                                v-model="title"
                                type="text"
                                maxlength="100"
                                class="rbim-input"
                                :class="{ 'rbim-input-error': titleProblem }"
                            >
                            <p v-if="titleProblem" class="rbim-error">{{ titleProblem }}</p>
                            <p v-else class="rbim-hint">You can change the default title before exporting.</p>
                        </div>
                        <div>
                            <label for="report-subtitle" class="rbim-label">Report subtitle</label>
                            <input
                                id="report-subtitle"
                                v-model="subtitle"
                                type="text"
                                maxlength="100"
                                class="rbim-input"
                                :class="{ 'rbim-input-error': subtitleProblem }"
                            >
                            <p v-if="subtitleProblem" class="rbim-error">{{ subtitleProblem }}</p>
                            <p v-else class="rbim-hint">Leave this blank when the report covers every record.</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button type="button" class="rbim-btn-outline" @click="step = 2">Back</button>
                        <button
                            v-if="canExport"
                            type="button"
                            class="rbim-btn"
                            :disabled="titleProblem !== '' || subtitleProblem !== '' || exporting !== ''"
                            @click="download('pdf')"
                        >
                            {{ exporting === 'pdf' ? 'Preparing PDF...' : 'Export PDF' }}
                        </button>
                        <button
                            v-if="canExport"
                            type="button"
                            class="rbim-btn-outline"
                            :disabled="titleProblem !== '' || subtitleProblem !== '' || exporting !== ''"
                            @click="download('excel')"
                        >
                            {{ exporting === 'excel' ? 'Preparing Excel...' : 'Export Excel' }}
                        </button>
                    </div>
                </div>

                <p v-if="preview.unanswered_note" class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                    {{ preview.unanswered_note }}
                </p>

                <div class="rbim-card overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr v-if="!hasGroupColumns">
                                    <th
                                        v-for="column in preview.columns"
                                        :key="column.key"
                                        class="px-3 py-3 text-left font-semibold text-slate-600"
                                    >
                                        {{ column.label }}
                                    </th>
                                </tr>
                                <template v-else>
                                    <tr>
                                        <th
                                            v-for="column in preview.columns"
                                            :key="column.key"
                                            class="px-3 py-3 text-left font-semibold text-slate-600"
                                            :colspan="column.is_group ? column.columns.length : 1"
                                            :rowspan="column.is_group ? 1 : 2"
                                        >
                                            {{ column.label }}
                                        </th>
                                    </tr>
                                    <tr>
                                        <template v-for="column in preview.columns" :key="`${column.key}-children`">
                                            <th
                                                v-for="child in column.is_group ? column.columns : []"
                                                :key="`${column.key}-${child.key}`"
                                                class="px-3 py-2 text-left text-xs font-semibold text-slate-500"
                                            >
                                                {{ child.label }}
                                            </th>
                                        </template>
                                    </tr>
                                </template>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-if="!preview.rows.length">
                                    <td class="px-3 py-6 text-center text-slate-500" :colspan="columnCount">
                                        No records match this report.
                                    </td>
                                </tr>
                                <tr v-for="row in preview.rows" :key="row.id">
                                    <template v-for="column in preview.columns" :key="`${row.id}-${column.key}`">
                                        <td
                                            v-if="!column.is_group"
                                            class="whitespace-pre-line px-3 py-3 align-top text-slate-800"
                                        >
                                            {{ displayValue(row.values[column.key]) }}
                                        </td>
                                        <template v-else>
                                            <td
                                                v-for="child in column.columns"
                                                :key="`${row.id}-${column.key}-${child.key}`"
                                                class="whitespace-pre-line px-3 py-3 align-top text-slate-800"
                                            >
                                                {{ groupDisplay(row.values[column.key], child.key) }}
                                            </td>
                                        </template>
                                    </template>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div
                        v-if="preview.pagination && preview.pagination.last_page > 1"
                        class="flex items-center justify-between gap-3 border-t border-slate-200 px-4 py-3 text-sm"
                    >
                        <span class="text-slate-500">
                            Page {{ preview.pagination.current_page }} of {{ preview.pagination.last_page }}
                        </span>
                        <div class="flex gap-2">
                            <button
                                type="button"
                                class="rbim-btn-outline"
                                :disabled="preview.pagination.current_page <= 1 || previewLoading"
                                @click="runPreview(preview.pagination.current_page - 1, false)"
                            >
                                Previous
                            </button>
                            <button
                                type="button"
                                class="rbim-btn-outline"
                                :disabled="preview.pagination.current_page >= preview.pagination.last_page || previewLoading"
                                @click="runPreview(preview.pagination.current_page + 1, false)"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import LookupCombobox from '@/components/LookupCombobox.vue';
import { useAuth } from '@/composables/useAuth';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    exportReport,
    fetchReportCategories,
    fetchReportOptions,
    previewReport,
    reportErrorMessage,
} from '@/services/reportService';

const { hasPermission } = useAuth();
const canExport = computed(() => hasPermission('report.create'));

const steps = ['Category', 'Filter', 'Columns', 'Preview'];
const step = ref(0);
const error = ref('');
const loadingCategories = ref(true);
const categories = ref([]);
const selectedCategory = ref(null);
const filterState = ref({});
const optionResults = ref({});
const knownOptions = ref({});
const selectedColumns = ref([]);
const preview = ref(null);
const title = ref('');
const subtitle = ref('');
const previewLoading = ref(false);
const exporting = ref('');

let searchTimer = null;

const hiddenKeys = computed(() => keysFor((filter) => isSingular(filter)));
const forcedKeys = computed(() => keysFor((filter) => !isSingular(filter) && (filter.owns ?? []).length > 0));

const pickerColumns = computed(() => (selectedCategory.value?.columns ?? [])
    .map((column) => {
        if (hiddenKeys.value.has(column.key)) {
            return null;
        }

        if (!column.is_group) {
            return {
                ...column,
                locked: forcedKeys.value.has(column.key),
            };
        }

        const children = column.columns.filter((child) => !hiddenKeys.value.has(`${column.key}.${child.key}`) && !hiddenKeys.value.has(child.key));

        if (!children.length) {
            return null;
        }

        return {
            ...column,
            columns: children,
            locked: children.some((child) => forcedKeys.value.has(`${column.key}.${child.key}`) || forcedKeys.value.has(child.key)),
        };
    })
    .filter(Boolean));

const filterReady = computed(() => {
    const filters = selectedCategory.value?.filters ?? [];

    if (!filters.length) {
        return false;
    }

    return filters.every((filter) => filterIsReady(filter));
});

const hasGroupColumns = computed(() => (preview.value?.columns ?? []).some((column) => column.is_group));

const columnCount = computed(() => (preview.value?.columns ?? []).reduce(
    (count, column) => count + (column.is_group ? column.columns.length : 1),
    0,
));

const titleProblem = computed(() => titleValidationError(title.value));
const subtitleProblem = computed(() => subtitleValidationError(subtitle.value));

onMounted(async () => {
    try {
        categories.value = await fetchReportCategories();
    } catch (requestError) {
        error.value = await reportErrorMessage(requestError, 'Unable to load report categories.');
    } finally {
        loadingCategories.value = false;
    }
});

watch([hiddenKeys, forcedKeys, pickerColumns], syncLockedColumns);

function chooseCategory(category) {
    selectedCategory.value = category;
    filterState.value = blankFilters(category);
    optionResults.value = {};
    knownOptions.value = {};
    useDefaultColumns();
    preview.value = null;
    title.value = '';
    subtitle.value = '';
    error.value = '';
    step.value = 1;
}

function blankFilters(category) {
    const next = {};

    (category.filters ?? []).forEach((filter) => {
        next[filter.key] = {
            mode: filter.type === 'lookup' ? 'all' : (filter.choices?.[0]?.value ?? ''),
            ids: [],
            value: '',
        };
    });

    return next;
}

function useDefaultColumns() {
    selectedColumns.value = (selectedCategory.value?.columns ?? [])
        .filter((column) => column.default)
        .map((column) => column.key);
    syncLockedColumns();
}

function selectAllColumns() {
    selectedColumns.value = pickerColumns.value.map((column) => column.key);
    syncLockedColumns();
}

function toggleColumn(key) {
    const column = pickerColumns.value.find((item) => item.key === key);

    if (column?.locked) {
        return;
    }

    if (selectedColumns.value.includes(key)) {
        selectedColumns.value = selectedColumns.value.filter((item) => item !== key);

        return;
    }

    selectedColumns.value = [...selectedColumns.value, key];
}

function syncLockedColumns() {
    const hidden = hiddenKeys.value;
    const locked = new Set(pickerColumns.value.filter((column) => column.locked).map((column) => column.key));
    let next = selectedColumns.value.filter((key) => !hidden.has(key));

    locked.forEach((key) => {
        if (!next.includes(key)) {
            next = [...next, key];
        }
    });

    selectedColumns.value = next;
}

function setFilterMode(filter, mode) {
    const current = filterState.value[filter.key];

    if (!current) {
        return;
    }

    current.mode = mode;
    current.ids = [];

    if (mode === 'multiple') {
        loadOptions(filter.key, '', true);
    }

    if (mode === 'one') {
        loadOptions(filter.key, '', false);
    }
}

function setSingleId(filterKey, id) {
    const current = filterState.value[filterKey];

    if (!current) {
        return;
    }

    current.ids = id ? [Number(id)] : [];
    const known = (optionResults.value[filterKey] ?? []).find((option) => Number(option.id) === Number(id));

    if (known) {
        knownOptions.value = {
            ...knownOptions.value,
            [filterKey]: [known],
        };
    }
}

function toggleChecked(filterKey, id) {
    const current = filterState.value[filterKey];

    if (!current) {
        return;
    }

    const numericId = Number(id);

    if (current.ids.includes(numericId)) {
        current.ids = current.ids.filter((item) => item !== numericId);

        return;
    }

    current.ids = [...current.ids, numericId];
}

function onFilterQuery(filterKey, value) {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => loadOptions(filterKey, value, false), 250);
}

async function loadOptions(filterKey, search, all) {
    if (!selectedCategory.value) {
        return;
    }

    try {
        const items = await fetchReportOptions(selectedCategory.value.key, filterKey, search, all);
        optionResults.value = {
            ...optionResults.value,
            [filterKey]: items,
        };
    } catch (requestError) {
        error.value = await reportErrorMessage(requestError, 'Unable to search filter options.');
    }
}

function dropdownOptions(filterKey) {
    const merged = new Map();

    (optionResults.value[filterKey] ?? []).forEach((option) => merged.set(Number(option.id), option));
    (knownOptions.value[filterKey] ?? []).forEach((option) => merged.set(Number(option.id), option));

    return [...merged.values()];
}

function numericChoice(filter) {
    const mode = filterState.value[filter.key]?.mode;

    return (filter.choices ?? []).find((choice) => choice.value === mode && choice.numeric) ?? null;
}

function filterIsReady(filter) {
    const state = filterState.value[filter.key];

    if (!state) {
        return false;
    }

    if (filter.type === 'lookup') {
        if (state.mode === 'all') {
            return true;
        }

        if (state.mode === 'one') {
            return state.ids.length === 1;
        }

        return state.ids.length > 0;
    }

    const choice = numericChoice(filter);

    if (!choice) {
        return state.mode !== '';
    }

    const number = Number(state.value);

    return state.value !== '' && Number.isInteger(number) && number >= choice.min && number <= choice.max;
}

function isSingular(filter) {
    const state = filterState.value[filter.key];

    if (!state) {
        return false;
    }

    if (filter.type === 'lookup') {
        if (state.mode === 'one') {
            return true;
        }

        return state.mode === 'multiple' && state.ids.length === 1;
    }

    const choice = (filter.choices ?? []).find((item) => item.value === state.mode);

    return Boolean(choice?.singular);
}

function keysFor(predicate) {
    const keys = new Set();

    (selectedCategory.value?.filters ?? []).forEach((filter) => {
        if (!predicate(filter)) {
            return;
        }

        (filter.owns ?? []).forEach((key) => keys.add(key));
    });

    return keys;
}

function filterPayload(page) {
    const filters = {};

    (selectedCategory.value?.filters ?? []).forEach((filter) => {
        const state = filterState.value[filter.key] ?? { mode: 'all', ids: [], value: '' };
        const choice = numericChoice(filter);

        filters[filter.key] = {
            mode: state.mode,
            ids: filter.type === 'lookup' && state.mode !== 'all' ? state.ids.map(Number) : [],
            value: choice ? Number(state.value) : null,
        };
    });

    return {
        category: selectedCategory.value.key,
        filters,
        selected_columns: selectedColumns.value,
        page,
    };
}

async function runPreview(page, resetTitle) {
    error.value = '';
    previewLoading.value = true;

    try {
        const data = await previewReport(filterPayload(page));
        preview.value = data;

        if (resetTitle) {
            title.value = data.title ?? '';
            subtitle.value = data.subtitle ?? '';
        }

        step.value = 3;
    } catch (requestError) {
        error.value = await reportErrorMessage(requestError, 'Unable to build the report preview.');
    } finally {
        previewLoading.value = false;
    }
}

async function download(format) {
    if (titleProblem.value || subtitleProblem.value || !canExport.value) {
        return;
    }

    error.value = '';
    exporting.value = format;

    try {
        await exportReport({
            ...filterPayload(1),
            format,
            title: title.value.trim(),
            subtitle: subtitle.value.trim(),
        });
    } catch (requestError) {
        error.value = await reportErrorMessage(requestError, 'Unable to export the report.');
    } finally {
        exporting.value = '';
    }
}

function levelLabel(level) {
    return level === 'resident' ? 'Resident master list' : 'Household master list';
}

function displayValue(value) {
    if (value == null || value === '') {
        return '—';
    }

    if (Array.isArray(value)) {
        const lines = value
            .map((item) => (item == null || item === '' ? '' : String(item)))
            .filter(Boolean);

        return lines.length ? lines.join('\n') : '—';
    }

    return String(value);
}

function groupDisplay(groupValue, childKey) {
    if (groupValue == null) {
        return '—';
    }

    if (Array.isArray(groupValue)) {
        return displayValue(groupValue.map((item) => item?.[childKey]));
    }

    return displayValue(groupValue[childKey]);
}

function titleValidationError(value) {
    const text = typeof value === 'string' ? value.trim() : '';

    if (!text) {
        return 'Report title is required.';
    }

    if (text.length > 100) {
        return 'Report title may not be longer than 100 characters.';
    }

    if (!/[\p{L}\p{N}]/u.test(text)) {
        return 'Report title must include letters or numbers.';
    }

    return '';
}

function subtitleValidationError(value) {
    const text = typeof value === 'string' ? value.trim() : '';

    if (!text) {
        return '';
    }

    if (text.length > 100) {
        return 'Report subtitle may not be longer than 100 characters.';
    }

    if (!/[\p{L}\p{N}]/u.test(text)) {
        return 'Report subtitle must include letters or numbers.';
    }

    return '';
}
</script>

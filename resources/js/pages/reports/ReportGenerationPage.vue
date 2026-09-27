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
                <h2 class="text-base font-semibold text-slate-900">{{ selectedCategory.label }} filter</h2>
                <fieldset class="space-y-3">
                    <legend class="rbim-label">Which records should this report include?</legend>
                    <label
                        v-for="mode in selectedCategory.filter_modes"
                        :key="mode.value"
                        class="flex items-center gap-2 text-sm text-slate-700"
                    >
                        <input
                            v-model="filterMode"
                            type="radio"
                            name="report-filter-mode"
                            class="text-brand focus:ring-brand"
                            :value="mode.value"
                        >
                        {{ mode.label }}
                    </label>
                </fieldset>

                <div v-if="filterMode === 'one'" class="max-w-md">
                    <LookupCombobox
                        v-model="singleId"
                        :options="dropdownOptions"
                        :limit="5"
                        :label="`Search ${selectedCategory.label.toLowerCase()}`"
                        :placeholder="`Type to search ${selectedCategory.label.toLowerCase()}`"
                        input-id="report-filter-one"
                        hint="Up to 5 matches are shown."
                        @update:query="onFilterQuery"
                    />
                </div>

                <div v-else-if="filterMode === 'multiple'" class="max-w-md space-y-3">
                    <LookupCombobox
                        :key="multiPickerKey"
                        :model-value="null"
                        :options="dropdownOptions"
                        :limit="5"
                        :label="`Search ${selectedCategory.label.toLowerCase()}`"
                        :placeholder="`Type to add a ${selectedCategory.label.toLowerCase()}`"
                        input-id="report-filter-multiple"
                        hint="Up to 5 matches are shown. Select more than one."
                        @update:model-value="addSelected"
                        @update:query="onFilterQuery"
                    />
                    <ul v-if="selectedOptions.length" class="flex flex-wrap gap-2">
                        <li
                            v-for="option in selectedOptions"
                            :key="option.id"
                            class="inline-flex items-center gap-2 rounded-full bg-brand-muted px-3 py-1 text-sm text-slate-800"
                        >
                            {{ option.label }}
                            <button
                                type="button"
                                class="font-semibold text-slate-500 hover:text-slate-900"
                                :aria-label="`Remove ${option.label}`"
                                @click="removeSelected(option.id)"
                            >
                                ×
                            </button>
                        </li>
                    </ul>
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
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    <label
                        v-for="column in selectedCategory.columns"
                        :key="column.key"
                        class="flex items-start gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700"
                    >
                        <input
                            type="checkbox"
                            class="mt-0.5 text-brand focus:ring-brand"
                            :checked="selectedColumns.includes(column.key)"
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
                    <div class="max-w-xl">
                        <label for="report-title" class="rbim-label">Report title</label>
                        <input
                            id="report-title"
                            v-model="title"
                            type="text"
                            maxlength="120"
                            class="rbim-input"
                            :class="{ 'rbim-input-error': titleProblem }"
                        >
                        <p v-if="titleProblem" class="rbim-error">{{ titleProblem }}</p>
                        <p v-else class="rbim-hint">You can change the default title before exporting.</p>
                    </div>
                    <p v-if="preview.subtitle" class="text-sm text-slate-600">{{ preview.subtitle }}</p>
                    <div class="flex flex-wrap gap-3">
                        <button type="button" class="rbim-btn-outline" @click="step = 2">Back</button>
                        <button
                            v-if="canExport"
                            type="button"
                            class="rbim-btn"
                            :disabled="titleProblem !== '' || exporting !== ''"
                            @click="download('pdf')"
                        >
                            {{ exporting === 'pdf' ? 'Preparing PDF...' : 'Export PDF' }}
                        </button>
                        <button
                            v-if="canExport"
                            type="button"
                            class="rbim-btn-outline"
                            :disabled="titleProblem !== '' || exporting !== ''"
                            @click="download('excel')"
                        >
                            {{ exporting === 'excel' ? 'Preparing Excel...' : 'Export Excel' }}
                        </button>
                    </div>
                </div>

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
const filterMode = ref('all');
const singleId = ref(null);
const selectedOptions = ref([]);
const optionResults = ref([]);
const multiPickerKey = ref(0);
const selectedColumns = ref([]);
const preview = ref(null);
const title = ref('');
const previewLoading = ref(false);
const exporting = ref('');

let searchTimer = null;

const dropdownOptions = computed(() => {
    const merged = new Map();

    optionResults.value.forEach((option) => merged.set(Number(option.id), option));

    if (filterMode.value === 'one' && singleId.value) {
        const known = selectedOptions.value.find((option) => Number(option.id) === Number(singleId.value));

        if (known) {
            merged.set(Number(known.id), known);
        }
    }

    return [...merged.values()];
});

const filterReady = computed(() => {
    if (filterMode.value === 'all') {
        return true;
    }

    if (filterMode.value === 'one') {
        return Boolean(singleId.value);
    }

    return selectedOptions.value.length > 0;
});

const hasGroupColumns = computed(() => (preview.value?.columns ?? []).some((column) => column.is_group));

const columnCount = computed(() => (preview.value?.columns ?? []).reduce(
    (count, column) => count + (column.is_group ? column.columns.length : 1),
    0,
));

const titleProblem = computed(() => titleValidationError(title.value));

onMounted(async () => {
    try {
        categories.value = await fetchReportCategories();
    } catch (requestError) {
        error.value = await reportErrorMessage(requestError, 'Unable to load report categories.');
    } finally {
        loadingCategories.value = false;
    }
});

watch(singleId, (id) => {
    if (!id) {
        return;
    }

    const option = optionResults.value.find((item) => Number(item.id) === Number(id));

    if (option) {
        selectedOptions.value = [option];
    }
});

watch(filterMode, () => {
    singleId.value = null;
    selectedOptions.value = [];
    optionResults.value = [];
    multiPickerKey.value += 1;

    if (filterMode.value !== 'all') {
        loadOptions('');
    }
});

function chooseCategory(category) {
    selectedCategory.value = category;
    filterMode.value = 'all';
    singleId.value = null;
    selectedOptions.value = [];
    useDefaultColumns();
    preview.value = null;
    title.value = '';
    error.value = '';
    step.value = 1;
}

function useDefaultColumns() {
    selectedColumns.value = (selectedCategory.value?.columns ?? [])
        .filter((column) => column.default)
        .map((column) => column.key);
}

function selectAllColumns() {
    selectedColumns.value = (selectedCategory.value?.columns ?? []).map((column) => column.key);
}

function toggleColumn(key) {
    if (selectedColumns.value.includes(key)) {
        selectedColumns.value = selectedColumns.value.filter((item) => item !== key);

        return;
    }

    selectedColumns.value = [...selectedColumns.value, key];
}

function onFilterQuery(value) {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => loadOptions(value), 250);
}

async function loadOptions(search) {
    if (!selectedCategory.value) {
        return;
    }

    try {
        optionResults.value = await fetchReportOptions(selectedCategory.value.key, search);
    } catch (requestError) {
        error.value = await reportErrorMessage(requestError, 'Unable to search filter options.');
    }
}

function addSelected(id) {
    if (!id) {
        return;
    }

    const option = dropdownOptions.value.find((item) => Number(item.id) === Number(id));

    if (option && !selectedOptions.value.some((item) => Number(item.id) === Number(option.id))) {
        selectedOptions.value = [...selectedOptions.value, option];
    }

    multiPickerKey.value += 1;
    loadOptions('');
}

function removeSelected(id) {
    selectedOptions.value = selectedOptions.value.filter((option) => Number(option.id) !== Number(id));
}

function filterPayload(page) {
    let ids = [];

    if (filterMode.value === 'one' && singleId.value) {
        ids = [Number(singleId.value)];
    }

    if (filterMode.value === 'multiple') {
        ids = selectedOptions.value.map((option) => Number(option.id));
    }

    return {
        category: selectedCategory.value.key,
        filter: {
            mode: filterMode.value,
            ids,
        },
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
        }

        step.value = 3;
    } catch (requestError) {
        error.value = await reportErrorMessage(requestError, 'Unable to build the report preview.');
    } finally {
        previewLoading.value = false;
    }
}

async function download(format) {
    if (titleProblem.value || !canExport.value) {
        return;
    }

    error.value = '';
    exporting.value = format;

    try {
        await exportReport({
            ...filterPayload(1),
            format,
            title: title.value.trim(),
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

    if (text.length > 120) {
        return 'Report title may not be longer than 120 characters.';
    }

    if (!/[\p{L}\p{N}]/u.test(text)) {
        return 'Report title must include letters or numbers.';
    }

    return '';
}
</script>

<template>
    <AppLayout :title="payload?.statistic?.label || 'Statistic'">
        <div class="space-y-5">
            <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-600" aria-label="Breadcrumb">
                <RouterLink :to="{ name: 'dashboard' }" class="font-medium text-[#247a3f] hover:underline">
                    Dashboard
                </RouterLink>
                <span aria-hidden="true">/</span>
                <span class="font-semibold text-slate-900">{{ payload?.statistic?.label || 'Statistic' }}</span>
                <button type="button" class="ml-auto cursor-pointer text-sm font-medium text-[#344d3f] hover:underline" @click="goBack">
                    Back
                </button>
            </nav>

            <div
                v-if="error"
                class="rounded-2xl bg-white px-6 py-14 text-center shadow-sm ring-1 ring-slate-200"
                role="alert"
            >
                <h2 class="text-base font-semibold text-slate-900">This statistic could not be loaded</h2>
                <p class="mt-1 text-sm text-slate-600">{{ error }}</p>
                <button type="button" class="rbim-btn mt-5" @click="load">
                    Try again
                </button>
            </div>

            <template v-else>
                <section class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:p-5" aria-label="Filters">
                    <div v-if="loading && !payload" class="grid animate-pulse grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div v-for="item in 4" :key="item" class="h-16 rounded-lg bg-slate-100" />
                    </div>
                    <div v-else class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <label v-for="filter in payload?.filters ?? []" :key="filter.key" class="block text-sm font-medium text-slate-700">
                            {{ filter.label }}
                            <select
                                class="rbim-input mt-1"
                                :value="filter.value || ''"
                                @change="setFilter(filter.key, $event.target.value)"
                            >
                                <option value="">All</option>
                                <option v-for="option in filter.options" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                        </label>
                    </div>

                    <div v-if="payload?.active?.length" class="mt-4 flex flex-wrap items-center gap-2">
                        <button
                            v-for="chip in payload.active"
                            :key="chip.key"
                            type="button"
                            class="inline-flex cursor-pointer items-center gap-2 rounded-full bg-[#c6cbbb] px-3 py-1 text-sm font-medium text-[#344d3f]"
                            :aria-label="`Remove ${chip.label} filter`"
                            @click="setFilter(chip.key, '')"
                        >
                            {{ chip.label }}: {{ chip.value }}
                            <span aria-hidden="true">×</span>
                        </button>
                        <button type="button" class="cursor-pointer text-sm font-semibold text-[#247a3f] hover:underline" @click="clearFilters">
                            Clear all
                        </button>
                    </div>

                    <p class="mt-4 text-sm font-medium text-slate-700" aria-live="polite">
                        Showing {{ filteredLabel }} of {{ totalLabel }}
                    </p>
                </section>

                <section
                    v-if="loading"
                    class="rbim-card grid animate-pulse grid-cols-1 items-center gap-8 p-5 sm:p-6 lg:grid-cols-[minmax(0,3fr)_minmax(0,2fr)]"
                    aria-hidden="true"
                >
                    <div class="mx-auto aspect-square w-full max-w-[34rem] rounded-full bg-slate-100" />
                    <div class="space-y-4">
                        <div class="h-6 w-48 rounded-full bg-slate-100" />
                        <div v-for="item in 5" :key="item" class="space-y-2">
                            <div class="h-4 rounded bg-slate-100" />
                            <div class="h-1.5 rounded-full bg-slate-100" />
                        </div>
                    </div>
                </section>
                <section v-else-if="!payload?.chart?.slices?.length" class="rbim-card px-6 py-16 text-center">
                    <p class="text-base font-semibold text-slate-900">{{ payload?.statistic?.empty || 'No records match these filters.' }}</p>
                    <button type="button" class="rbim-btn mt-4" @click="clearFilters">
                        Clear filters
                    </button>
                </section>
                <DashboardPieCard
                    v-else
                    :title="payload.chart.title"
                    :views="pieViews"
                    :charts="pieCharts"
                    hint="Click a slice or a value to filter these records."
                    @select-slice="onPieSlice"
                />

                <section class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                    <div class="flex flex-wrap items-end justify-between gap-3 border-b border-slate-100 px-4 py-4 sm:px-5">
                        <h2 class="text-sm font-bold uppercase tracking-wide text-slate-900">
                            {{ payload?.statistic?.table_title || 'Records' }}
                        </h2>
                        <div class="flex flex-wrap items-end gap-2">
                            <label class="block text-sm font-medium text-slate-700">
                                Search
                                <input
                                    v-model="search"
                                    type="search"
                                    class="rbim-input mt-1 w-56"
                                    placeholder="Search records"
                                    @input="onSearch"
                                >
                            </label>
                            <button
                                v-if="payload?.can_export"
                                type="button"
                                class="rbim-btn"
                                :disabled="exporting"
                                @click="download"
                            >
                                {{ exporting ? 'Exporting…' : 'Export CSV' }}
                            </button>
                            <p v-if="exportError" class="rbim-error" role="alert">{{ exportError }}</p>
                        </div>
                    </div>

                    <div v-if="!loading && !(payload?.rows?.length)" class="px-5 py-14 text-center">
                        <p class="text-base font-semibold text-slate-900">{{ payload?.statistic?.empty || 'No records match these filters.' }}</p>
                        <button type="button" class="rbim-btn mt-4" @click="clearFilters">
                            Clear filters
                        </button>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th v-for="column in payload?.columns ?? []" :key="column.key" class="px-4 py-3 font-semibold" scope="col" :aria-sort="sort === column.key ? (dir === 'asc' ? 'ascending' : 'descending') : 'none'">
                                        <button
                                            type="button"
                                            class="inline-flex cursor-pointer items-center gap-1 hover:text-slate-900"
                                            @click="toggleSort(column.key)"
                                        >
                                            {{ column.label }}
                                            <span v-if="sort === column.key" aria-hidden="true">{{ dir === 'asc' ? '↑' : '↓' }}</span>
                                            <span class="sr-only">{{ sort === column.key ? (dir === 'asc' ? 'sorted ascending' : 'sorted descending') : 'sort' }}</span>
                                        </button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="loading">
                                    <td :colspan="payload?.columns?.length || 6" class="px-4 py-8 text-slate-500">Loading records…</td>
                                </tr>
                                <tr v-for="(row, index) in payload?.rows ?? []" v-else :key="index" class="border-b border-slate-100">
                                    <td v-for="column in payload.columns" :key="column.key" class="whitespace-nowrap px-4 py-3 text-slate-800">
                                        {{ row[column.key] }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="payload" class="flex items-center justify-between gap-3 px-4 py-3 text-sm">
                        <p class="text-slate-600">
                            Page {{ payload.pagination.page }} of {{ payload.pagination.last_page }}
                        </p>
                        <div class="flex gap-2">
                            <button type="button" class="rbim-btn-outline px-3 py-1.5 text-xs" :disabled="payload.pagination.page <= 1" @click="setPage(payload.pagination.page - 1)">
                                Previous
                            </button>
                            <button type="button" class="rbim-btn-outline px-3 py-1.5 text-xs" :disabled="payload.pagination.page >= payload.pagination.last_page" @click="setPage(payload.pagination.page + 1)">
                                Next
                            </button>
                        </div>
                    </div>
                </section>
            </template>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import DashboardPieCard from '@/components/dashboard/DashboardPieCard.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { exportCensusStatistic, fetchCensusStatistic } from '@/services/censusStatisticService';
import { extractErrorMessage } from '@/services/http';

const route = useRoute();
const router = useRouter();
const payload = ref(null);
const loading = ref(true);
const error = ref('');
const exporting = ref(false);
const exportError = ref('');
const search = ref(String(route.query.q || ''));
let searchTimer = null;

const pieViews = [{ key: 'current', label: 'Records' }];

const pieCharts = computed(() => ({
    current: {
        metric: payload.value?.statistic?.label || 'Records',
        total: Number(payload.value?.filtered ?? 0),
        slices: (payload.value?.chart?.slices ?? []).map((slice) => ({
            label: slice.label,
            count: slice.count,
            id: slice.id,
            filter: slice.filter,
            kind: slice.id === 'none' ? 'missing' : null,
        })),
    },
}));

const sort = computed(() => String(route.query.sort || 'name'));
const dir = computed(() => String(route.query.dir || 'asc'));
const filteredLabel = computed(() => Number(payload.value?.filtered ?? 0).toLocaleString('en-US'));
const totalLabel = computed(() => Number(payload.value?.total ?? 0).toLocaleString('en-US'));

const activeChartValue = computed(() => {
    const filter = payload.value?.chart?.slices?.[0]?.filter;

    if (!filter) {
        return '';
    }

    return String(route.query[filter] || '');
});

function currentParams() {
    const params = {};

    ['street', 'clan', 'age', 'sex', 'q', 'sort', 'dir', 'page'].forEach((key) => {
        const value = route.query[key];

        if (value !== undefined && value !== '') {
            params[key] = value;
        }
    });

    return params;
}

function replaceQuery(patch, resetPage = false) {
    const next = { ...route.query, ...patch };

    if (resetPage) {
        delete next.page;
    }

    Object.keys(next).forEach((key) => {
        if (next[key] === '' || next[key] == null) {
            delete next[key];
        }
    });

    router.push({ name: 'dashboard-statistic', params: { statId: route.params.statId }, query: next });
}

function setFilter(key, value) {
    replaceQuery({ [key]: value }, true);
}

function clearFilters() {
    search.value = '';
    router.push({ name: 'dashboard-statistic', params: { statId: route.params.statId } });
}

function onPieSlice(slice) {
    if (!slice?.filter) {
        return;
    }

    const value = String(slice.id) === String(activeChartValue.value) ? '' : String(slice.id);
    setFilter(slice.filter, value);
}

function onSearch() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        replaceQuery({ q: search.value.trim() }, true);
    }, 300);
}

function toggleSort(key) {
    const nextDir = sort.value === key && dir.value === 'asc' ? 'desc' : 'asc';
    replaceQuery({ sort: key, dir: nextDir }, true);
}

function setPage(page) {
    replaceQuery({ page: String(page) });
}

function goBack() {
    if (window.history.length > 1) {
        router.back();

        return;
    }

    router.push({ name: 'dashboard' });
}

async function load() {
    loading.value = true;
    error.value = '';

    try {
        payload.value = await fetchCensusStatistic(route.params.statId, currentParams());
        search.value = String(route.query.q || '');
    } catch (exception) {
        error.value = extractErrorMessage(exception, 'Please check your connection and try again.');
    } finally {
        loading.value = false;
    }
}

async function download() {
    exporting.value = true;
    exportError.value = '';

    try {
        await exportCensusStatistic(route.params.statId, currentParams());
    } catch (exception) {
        exportError.value = extractErrorMessage(exception, 'Unable to export this statistic.');
    } finally {
        exporting.value = false;
    }
}

watch(() => [route.params.statId, JSON.stringify(route.query)], load, { immediate: true });
</script>

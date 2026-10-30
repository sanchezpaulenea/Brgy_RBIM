<template>
    <AppLayout :title="chart?.title || 'Statistic'">
        <div class="@container space-y-6">
            <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-600" aria-label="Breadcrumb">
                <RouterLink :to="{ name: 'dashboard' }" class="font-medium text-[#247a3f] hover:underline">
                    Dashboard
                </RouterLink>
                <span aria-hidden="true">/</span>
                <span class="font-semibold text-slate-900">{{ chart?.title || 'Statistic' }}</span>
                <button type="button" class="ml-auto cursor-pointer text-sm font-medium text-[#344d3f] hover:underline" @click="goBack">
                    Back
                </button>
            </nav>

            <div
                v-if="missing"
                class="rbim-card px-6 py-14 text-center"
                role="alert"
            >
                <h2 class="text-base font-semibold text-slate-900">This category is not available</h2>
                <RouterLink :to="{ name: 'dashboard' }" class="rbim-btn mt-5 inline-flex">
                    Back to dashboard
                </RouterLink>
            </div>

            <div
                v-else-if="error && !statistics"
                class="rbim-card px-6 py-14 text-center"
                role="alert"
            >
                <h2 class="text-base font-semibold text-slate-900">This category could not be loaded</h2>
                <p class="mt-1 text-sm text-slate-600">{{ error }}</p>
                <button type="button" class="rbim-btn mt-5" @click="load">
                    Try again
                </button>
            </div>

            <template v-else>
                <p
                    v-if="error && statistics"
                    class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                    role="alert"
                >
                    Refresh failed: {{ error }} Showing the last loaded statistics.
                </p>

                <section
                    v-if="showsPopulationFilters"
                    class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:p-5"
                    aria-label="Filters"
                >
                    <div v-if="showSkeleton" class="grid animate-pulse grid-cols-1 gap-3 sm:grid-cols-2" :class="filterGridClass">
                        <div v-for="item in applicableFilterKeys.length" :key="item" class="h-16 rounded-lg bg-slate-100" />
                    </div>
                    <div v-else class="grid grid-cols-1 gap-3 sm:grid-cols-2" :class="filterGridClass">
                        <label v-for="filter in visiblePopulationFilters" :key="filter.key" class="block text-sm font-medium text-slate-700">
                            {{ filter.label }}
                            <select
                                class="rbim-input mt-1"
                                :value="String(route.query[filter.key] || '')"
                                @change="setPopulationFilter(filter.key, $event.target.value)"
                            >
                                <option value="">All</option>
                                <option v-for="option in filter.options" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                        </label>
                    </div>

                    <div v-if="activePopulationFilters.length" class="mt-4 flex flex-wrap items-center gap-2">
                        <button
                            v-for="chip in activePopulationFilters"
                            :key="chip.key"
                            type="button"
                            class="inline-flex cursor-pointer items-center gap-2 rounded-full bg-[#c6cbbb] px-3 py-1 text-sm font-medium text-[#344d3f]"
                            :aria-label="`Remove ${chip.label} filter`"
                            @click="setPopulationFilter(chip.key, '')"
                        >
                            {{ chip.label }}: {{ chip.value }}
                            <span aria-hidden="true">×</span>
                        </button>
                        <button type="button" class="cursor-pointer text-sm font-semibold text-[#247a3f] hover:underline" @click="clearPopulationFilters">
                            Clear all
                        </button>
                    </div>

                    <p v-if="statistics?.population" class="mt-4 text-sm font-medium text-slate-700" aria-live="polite">
                        Showing {{ populationCounts.filtered }} of {{ populationCounts.total }}
                    </p>
                </section>

                <header v-if="!showsPopulationFilters" class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0">
                        <h2 class="text-xl font-semibold text-slate-900">{{ reportTitle }}</h2>
                        <p class="mt-1 text-sm text-slate-600">
                            {{ reportSubtitle || chart?.subtitle }}
                        </p>
                    </div>
                    <button type="button" class="rbim-btn-outline" :disabled="loading" @click="load">
                        {{ loading ? 'Refreshing…' : 'Refresh' }}
                    </button>
                </header>

                <div
                    class="grid grid-cols-1 gap-4"
                    :class="visiblePanels.length > 1 ? '@3xl:grid-cols-2' : ''"
                >
                    <template v-for="panel in visiblePanels" :key="panel.key">
                        <div
                            v-if="showSkeleton && panel.visualization === 'boolean'"
                            class="h-48 animate-pulse rounded-xl bg-slate-100"
                        />
                        <div
                            v-else-if="panel.visualization === 'boolean'"
                            class="rounded-xl border border-slate-200 bg-white p-4"
                        >
                            <h3 v-if="visiblePanels.length > 1" class="text-base font-semibold text-slate-900">{{ panel.title }}</h3>
                            <div class="mt-4">
                                <DashboardBooleanLists
                                    :chart="panelChart(panel)"
                                    @select-group="(group) => openSlice(group, panel)"
                                />
                            </div>
                        </div>
                        <DashboardPieCard
                            v-else
                            :title="visiblePanels.length > 1 ? panel.title : ''"
                            :views="panelViews(panel)"
                            :charts="panelCharts(panel)"
                            :loading="showSkeleton"
                            hint="Click a slice or a value to open the matching records."
                            @select-slice="(slice) => openSlice(slice, panel)"
                        />
                    </template>
                </div>

                <section v-if="showsPopulationFilters" class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-4 py-3 sm:px-5">
                        <h2 class="text-sm font-bold uppercase tracking-wide text-slate-900">
                            {{ overviewTitle }}
                        </h2>
                        <label class="flex items-center gap-3 text-sm font-medium text-slate-700">
                            Search
                            <input
                                v-model="search"
                                type="search"
                                class="rbim-input w-56"
                                placeholder="Search records"
                                @input="onOverviewSearch"
                            >
                        </label>
                    </div>

                    <div v-if="!overviewLoading && !(overview?.rows?.length)" class="px-5 py-14 text-center">
                        <p class="text-base font-semibold text-slate-900">{{ overview?.empty || 'No records match these filters.' }}</p>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th v-for="column in overview?.columns ?? []" :key="column.key" class="px-4 py-3 font-semibold" scope="col" :aria-sort="overviewSort === column.key ? (overviewDir === 'asc' ? 'ascending' : 'descending') : 'none'">
                                        <button
                                            type="button"
                                            class="inline-flex cursor-pointer items-center gap-1 hover:text-slate-900"
                                            @click="toggleOverviewSort(column.key)"
                                        >
                                            {{ column.label }}
                                            <span v-if="overviewSort === column.key" aria-hidden="true">{{ overviewDir === 'asc' ? '↑' : '↓' }}</span>
                                            <span class="sr-only">{{ overviewSort === column.key ? (overviewDir === 'asc' ? 'sorted ascending' : 'sorted descending') : 'sort' }}</span>
                                        </button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="overviewLoading">
                                    <td :colspan="overview?.columns?.length || 5" class="px-4 py-8 text-slate-500">Loading records…</td>
                                </tr>
                                <tr v-for="(row, index) in overview?.rows ?? []" v-else :key="index" class="border-b border-slate-100">
                                    <td v-for="column in overview.columns" :key="column.key" class="whitespace-nowrap px-4 py-3 text-slate-800">
                                        {{ row[column.key] }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="overview" class="grid grid-cols-3 items-center gap-3 px-4 py-3 text-sm">
                        <div>
                            <button type="button" class="rbim-btn-outline px-3 py-1.5 text-xs" :disabled="overview.pagination.page <= 1" @click="setOverviewPage(overview.pagination.page - 1)">
                                Previous
                            </button>
                        </div>
                        <p class="text-center text-slate-600">
                            Page {{ overview.pagination.page }} of {{ overview.pagination.last_page }}
                        </p>
                        <div class="flex justify-end">
                            <button type="button" class="rbim-btn-outline px-3 py-1.5 text-xs" :disabled="overview.pagination.page >= overview.pagination.last_page" @click="setOverviewPage(overview.pagination.page + 1)">
                                Next
                            </button>
                        </div>
                    </div>
                </section>

                <article
                    v-if="canViewReports && selectedCategory"
                    class="rbim-card space-y-4 p-5 sm:p-6"
                >
                    <h3 class="text-sm font-semibold text-slate-900">Filters</h3>
                    <DashboardModuleFilters
                        v-model="filterState"
                        :category="selectedCategory"
                        :options="optionResults"
                        @search="onFilterQuery"
                    />
                </article>

                <DashboardDrilldown
                    v-if="canViewReports && (preview || previewLoading || previewError)"
                    :title="drillTitle"
                    :subtitle="drillSubtitle"
                    :preview="preview"
                    :loading="previewLoading"
                    :error="previewError"
                    :can-export="canExport"
                    :busy="exporting"
                    @export="download"
                    @page="(page) => runPreview(page, false)"
                />
            </template>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import DashboardBooleanLists from '@/components/dashboard/DashboardBooleanLists.vue';
import DashboardDrilldown from '@/components/dashboard/DashboardDrilldown.vue';
import DashboardModuleFilters from '@/components/dashboard/DashboardModuleFilters.vue';
import DashboardPieCard from '@/components/dashboard/DashboardPieCard.vue';
import { useAuth } from '@/composables/useAuth';
import { DASHBOARD_CATEGORY_SECTIONS, HOUSEHOLD_STATISTIC_FILTERS, findDashboardChart } from '@/constants/dashboardCharts';
import AppLayout from '@/layouts/AppLayout.vue';
import { fetchDashboardOverview, fetchDashboardStatistics } from '@/services/dashboardService';
import { extractErrorMessage } from '@/services/http';
import {
    exportReport,
    fetchReportCategories,
    fetchReportOptions,
    previewReport,
    reportErrorMessage,
} from '@/services/reportService';
import {
    applySliceToFilters,
    blankFilters,
    buildFilterPayload,
    defaultColumnKeys,
    reportHeading,
    reportMapping,
    visibleSlices,
} from '@/utils/dashboardAnalytics';

const route = useRoute();
const router = useRouter();
const { hasPermission } = useAuth();

const canViewReports = computed(() => hasPermission('report.view'));
const canExport = computed(() => hasPermission('report.create'));

const statistics = ref(null);
const loading = ref(true);
const error = ref('');
const categories = ref([]);
const filterState = ref({});
const optionResults = ref({});
const preview = ref(null);
const previewLoading = ref(false);
const previewError = ref('');
const exporting = ref('');
const drillTitle = ref('');
const drillSubtitle = ref('');
const overview = ref(null);
const overviewLoading = ref(false);
const search = ref(String(route.query.q || ''));
let loadToken = 0;
let overviewToken = 0;
let overviewSearchTimer = null;

const located = computed(() => findDashboardChart(String(route.params.chartKey || '')));
const missing = computed(() => !located.value);
const chart = computed(() => located.value?.chart ?? null);
const populationSection = computed(() => {
    const match = DASHBOARD_CATEGORY_SECTIONS.find((section) => section.cards.some((card) => card.key === chart.value?.key));

    if (match) {
        return match;
    }

    const sectionKey = located.value?.section?.key;

    if (!sectionKey) {
        return null;
    }

    const householdSections = ['household-profile', 'housing-utilities', 'barangay-records'];

    return {
        key: householdSections.includes(sectionKey) ? 'household' : 'resident',
        title: located.value.section.title,
    };
});
const showsPopulationFilters = computed(() => populationSection.value !== null);
const overviewTitle = computed(() => `${chart.value?.title || 'Records'} Overview`);
const applicableFilterKeys = computed(() => (
    populationSection.value?.key === 'resident'
        ? ['street', 'clan', 'age', 'sex']
        : ['street', 'clan', ...(HOUSEHOLD_STATISTIC_FILTERS[chart.value?.key] ?? [])]
));
const filterGridClass = computed(() => {
    const count = applicableFilterKeys.value.length;

    if (count >= 4) {
        return 'lg:grid-cols-4';
    }

    if (count === 3) {
        return 'lg:grid-cols-3';
    }

    return 'lg:grid-cols-2';
});
const visiblePopulationFilters = computed(() => {
    const shared = (statistics.value?.population_filters ?? [])
        .filter((filter) => applicableFilterKeys.value.includes(filter.key));
    const own = (statistics.value?.statistic_filters ?? [])
        .filter((filter) => applicableFilterKeys.value.includes(filter.key));

    return [...shared, ...own];
});
const activePopulationFilters = computed(() => (
    visiblePopulationFilters.value
        .filter((filter) => String(route.query[filter.key] || '') !== '')
        .map((filter) => ({
            key: filter.key,
            label: filter.label,
            value: filter.options.find((option) => option.value === String(route.query[filter.key]))?.label
                || String(route.query[filter.key]),
        }))
));
const populationCounts = computed(() => {
    const key = populationSection.value?.key === 'household' ? 'households' : 'residents';
    const counts = statistics.value?.population?.[key] ?? { filtered: 0, total: 0 };

    return {
        filtered: Number(counts.filtered || 0).toLocaleString('en-US'),
        total: Number(counts.total || 0).toLocaleString('en-US'),
    };
});
const showSkeleton = computed(() => loading.value && !statistics.value);

const modulePanels = computed(() => (
    chart.value?.panels ?? (chart.value ? [{
        key: chart.value.key,
        title: chart.value.title,
        visualization: chart.value.visualization,
        views: chart.value.views,
    }] : [])
));

const visiblePanels = computed(() => {
    const topicsState = filterState.value.topics;

    return modulePanels.value.filter((panel) => {
        if (panel.topic == null || !topicsState) {
            return true;
        }

        return (topicsState.ids ?? []).includes(panel.topic);
    });
});

const selectedCategory = computed(() => {
    const mapping = reportMapping(chart.value?.key);

    return categories.value.find((item) => item.key === mapping?.category) ?? null;
});

const heading = computed(() => (
    selectedCategory.value && chart.value
        ? reportHeading(selectedCategory.value, filterState.value)
        : { title: chart.value?.title || 'Statistic', subtitle: chart.value?.subtitle || '' }
));

const reportTitle = computed(() => heading.value.title);
const reportSubtitle = computed(() => heading.value.subtitle);

function panelDataKey(panel) {
    return panel.dataKey ?? panel.key;
}

function panelChart(panel) {
    return statistics.value?.charts?.[panelDataKey(panel)] ?? { metric: '', total: 0, groups: [], slices: [] };
}

function panelViews(panel) {
    if (panel.views) {
        return panel.views;
    }

    if (panel.key === chart.value?.key && chart.value?.views) {
        return chart.value.views;
    }

    return [{ key: panelDataKey(panel), label: panel.title }];
}

function panelCharts(panel) {
    const charts = statistics.value?.charts ?? {};
    const next = {};

    panelViews(panel).forEach((view) => {
        const source = charts[view.key] ?? charts[panelDataKey(panel)];

        if (!source || source.kind === 'boolean') {
            return;
        }

        const slices = visibleSlices(source, selectedCategory.value, filterState.value, chart.value?.key, panel);
        next[view.key] = {
            ...source,
            slices,
            total: slices.reduce((sum, slice) => sum + (slice.count ?? 0), 0),
        };
    });

    return next;
}

function primedFilters(category) {
    const next = blankFilters(category);
    const mapping = reportMapping(chart.value?.key);

    if (mapping?.topics && next.topics) {
        next.topics.ids = [...mapping.topics];
    }

    return next;
}

function resetModule() {
    preview.value = null;
    previewError.value = '';
    optionResults.value = {};

    if (selectedCategory.value) {
        filterState.value = primedFilters(selectedCategory.value);
        loadAllOptions(selectedCategory.value);
    } else {
        filterState.value = {};
    }
}

async function loadAllOptions(category) {
    await Promise.all((category.filters ?? [])
        .filter((filter) => filter.type === 'lookup')
        .map(async (filter) => {
            try {
                optionResults.value = {
                    ...optionResults.value,
                    [filter.key]: await fetchReportOptions(category.key, filter.key, '', true, 'multiple'),
                };
            } catch {
                optionResults.value = { ...optionResults.value, [filter.key]: [] };
            }
        }));
}

let searchTimer = null;

function onFilterQuery(key, value) {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(async () => {
        if (!selectedCategory.value) {
            return;
        }

        try {
            optionResults.value = {
                ...optionResults.value,
                [key]: await fetchReportOptions(selectedCategory.value.key, key, value, false, 'one'),
            };
        } catch {
            optionResults.value = { ...optionResults.value, [key]: [] };
        }
    }, 250);
}

async function openSlice(slice, panel = null) {
    if (!canViewReports.value || !selectedCategory.value || !chart.value) {
        return;
    }

    filterState.value = applySliceToFilters(selectedCategory.value, filterState.value, chart.value.key, slice, panel);
    const headingNow = reportHeading(selectedCategory.value, filterState.value);
    drillTitle.value = headingNow.title;
    drillSubtitle.value = slice.label || headingNow.subtitle;
    await runPreview(1, false);
}

async function runPreview(page, resetTitle) {
    if (!selectedCategory.value) {
        return;
    }

    previewError.value = '';
    previewLoading.value = true;

    try {
        const data = await previewReport(buildFilterPayload(
            selectedCategory.value,
            filterState.value,
            page,
            defaultColumnKeys(selectedCategory.value),
        ));
        preview.value = data;

        if (resetTitle) {
            drillTitle.value = data.title ?? reportTitle.value;
            drillSubtitle.value = data.subtitle ?? reportSubtitle.value;
        }
    } catch (requestError) {
        previewError.value = await reportErrorMessage(requestError, 'Unable to load matching records.');
    } finally {
        previewLoading.value = false;
    }
}

async function download(format) {
    if (!canExport.value || !selectedCategory.value) {
        return;
    }

    exporting.value = format;

    try {
        await exportReport({
            ...buildFilterPayload(selectedCategory.value, filterState.value, 1, defaultColumnKeys(selectedCategory.value)),
            format,
            title: (drillTitle.value || reportTitle.value).trim(),
            subtitle: (drillSubtitle.value || reportSubtitle.value || '').trim(),
        });
    } catch (requestError) {
        previewError.value = await reportErrorMessage(requestError, 'Unable to export the report.');
    } finally {
        exporting.value = '';
    }
}

function populationParams() {
    const params = {};

    if (populationSection.value?.key === 'household' && chart.value?.key) {
        params.chart = chart.value.key;
    }

    applicableFilterKeys.value.forEach((key) => {
        const value = route.query[key];

        if (value !== undefined && value !== '') {
            params[key] = value;
        }
    });

    return params;
}

const overviewSort = computed(() => String(route.query.sort || 'name'));
const overviewDir = computed(() => String(route.query.dir || 'asc'));

function overviewParams() {
    const params = {
        population: populationSection.value?.key === 'household' ? 'households' : 'residents',
        ...populationParams(),
    };

    ['q', 'sort', 'dir', 'page'].forEach((key) => {
        const value = route.query[key];

        if (value !== undefined && value !== '') {
            params[key] = value;
        }
    });

    return params;
}

function replaceOverviewQuery(patch, resetPage = false) {
    const next = { ...route.query, ...patch };

    if (resetPage) {
        delete next.page;
    }

    Object.keys(next).forEach((key) => {
        if (next[key] === '' || next[key] == null) {
            delete next[key];
        }
    });

    router.push({ name: 'dashboard-category', params: { chartKey: route.params.chartKey }, query: next });
}

function setPopulationFilter(key, value) {
    replaceOverviewQuery({ [key]: value }, true);
}

function clearPopulationFilters() {
    search.value = '';
    router.push({ name: 'dashboard-category', params: { chartKey: route.params.chartKey } });
}

function onOverviewSearch() {
    clearTimeout(overviewSearchTimer);
    overviewSearchTimer = setTimeout(() => {
        replaceOverviewQuery({ q: search.value.trim() }, true);
    }, 300);
}

function toggleOverviewSort(key) {
    const nextDir = overviewSort.value === key && overviewDir.value === 'asc' ? 'desc' : 'asc';
    replaceOverviewQuery({ sort: key, dir: nextDir }, true);
}

function setOverviewPage(page) {
    replaceOverviewQuery({ page: String(page) });
}

async function load() {
    if (missing.value) {
        loading.value = false;

        return;
    }

    const token = ++loadToken;
    loading.value = true;
    error.value = '';

    try {
        const data = await fetchDashboardStatistics(populationParams());

        if (token !== loadToken) {
            return;
        }

        statistics.value = data;
    } catch (exception) {
        if (token !== loadToken) {
            return;
        }

        error.value = extractErrorMessage(exception, 'Please check your connection and try again.');
    } finally {
        if (token === loadToken) {
            loading.value = false;
        }
    }
}

async function loadOverview() {
    if (!showsPopulationFilters.value) {
        overview.value = null;

        return;
    }

    const token = ++overviewToken;
    overviewLoading.value = true;

    try {
        const data = await fetchDashboardOverview(overviewParams());

        if (token !== overviewToken) {
            return;
        }

        overview.value = data;
        search.value = String(route.query.q || '');
    } catch (exception) {
        if (token !== overviewToken) {
            return;
        }

        overview.value = {
            table_title: populationSection.value?.key === 'household' ? 'Households Overview' : 'Residents Overview',
            empty: extractErrorMessage(exception, 'No records match these filters.'),
            columns: [],
            rows: [],
            pagination: { page: 1, last_page: 1 },
        };
    } finally {
        if (token === overviewToken) {
            overviewLoading.value = false;
        }
    }
}

function goBack() {
    if (window.history.length > 1) {
        router.back();

        return;
    }

    router.push({ name: 'dashboard' });
}

watch(() => route.params.chartKey, async () => {
    statistics.value = null;
    overview.value = null;
    await Promise.all([load(), loadOverview()]);
    resetModule();
});

watch(() => JSON.stringify(populationParams()), () => {
    if (!missing.value) {
        load();
    }
});

watch(() => JSON.stringify(overviewParams()), () => {
    if (showsPopulationFilters.value) {
        loadOverview();
    }
});

onMounted(async () => {
    await Promise.all([load(), loadOverview()]);

    if (!canViewReports.value) {
        return;
    }

    try {
        categories.value = await fetchReportCategories();
        resetModule();
    } catch {
        categories.value = [];
    }
});
</script>

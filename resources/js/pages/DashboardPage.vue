<template>
    <AppLayout title="Dashboard">
        <div class="@container space-y-8">
            <header class="flex flex-wrap items-end justify-between gap-4 print:hidden">
                <div>
                    <h2 class="text-xl font-semibold text-slate-900">
                        Welcome back, {{ displayName }}!
                    </h2>
                    <p class="mt-1 text-sm text-slate-600">
                        Barangay statistics at a glance.<template v-if="canViewReports"> Click a chart value to open the matching records.</template>
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <p v-if="updatedLabel" class="text-xs text-slate-500">
                        Updated {{ updatedLabel }}
                    </p>
                    <button
                        type="button"
                        class="rbim-btn-outline"
                        :disabled="loading"
                        @click="load"
                    >
                        <svg
                            class="h-4 w-4"
                            :class="loading ? 'animate-spin' : ''"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            aria-hidden="true"
                        >
                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                        </svg>
                        {{ loading ? 'Refreshing…' : 'Refresh' }}
                    </button>
                </div>
            </header>

            <div
                v-if="error && !statistics"
                class="rbim-card flex flex-col items-center px-6 py-14 text-center"
                role="alert"
            >
                <span class="flex h-12 w-12 items-center justify-center rounded-full bg-red-50 text-red-600">
                    <svg class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </span>
                <h3 class="mt-4 text-base font-semibold text-slate-900">
                    Statistics could not be loaded
                </h3>
                <p class="mt-1 max-w-md text-sm text-slate-600">
                    {{ error }}
                </p>
                <button type="button" class="rbim-btn mt-5" @click="load">
                    Try again
                </button>
            </div>

            <template v-else>
                <div
                    v-if="error"
                    class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 print:hidden"
                    role="alert"
                >
                    <span>Refresh failed: {{ error }} Showing the last loaded statistics.</span>
                    <button type="button" class="rbim-btn-danger" @click="load">
                        Retry
                    </button>
                </div>

                <section aria-label="Summary" class="grid grid-cols-1 gap-4 print:hidden @lg:grid-cols-2 @4xl:grid-cols-4">
                    <DashboardStatCard
                        v-for="card in SUMMARY_CARDS"
                        :key="card.key"
                        :label="card.label"
                        :icon="card.icon"
                        :value="statistics?.summary?.[card.key] ?? null"
                        :loading="showSkeleton"
                    />
                </section>

                <article class="rbim-card @container space-y-6 p-5 sm:p-6">
                    <header class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 pb-5">
                        <div class="min-w-0">
                            <h2 class="text-base font-semibold text-slate-900">{{ reportTitle }}</h2>
                            <p class="mt-0.5 text-sm text-slate-500">
                                {{ reportSubtitle || `${selectedSection.title} · ${selectedChart.subtitle}` }}
                            </p>
                        </div>
                        <div class="flex w-full flex-wrap gap-3 @2xl:w-auto">
                            <label class="min-w-[12rem] flex-1 @2xl:flex-none">
                                <span class="rbim-label">Category</span>
                                <select v-model="sectionKey" class="rbim-input">
                                    <option v-for="section in CHART_SECTIONS" :key="section.key" :value="section.key">
                                        {{ section.title }}
                                    </option>
                                </select>
                            </label>
                            <label class="min-w-[14rem] flex-1 @2xl:flex-none">
                                <span class="rbim-label">Statistic</span>
                                <select v-model="chartKey" class="rbim-input">
                                    <option v-for="chart in selectedSection.charts" :key="chart.key" :value="chart.key">
                                        {{ chart.title }}
                                    </option>
                                </select>
                            </label>
                        </div>
                    </header>

                    <div
                        class="grid grid-cols-1 gap-4"
                        :class="visiblePanels.length > 1 ? '@3xl:grid-cols-2' : ''"
                    >
                        <p
                            v-if="!visiblePanels.length"
                            class="col-span-full py-10 text-center text-sm text-slate-500"
                        >
                            Select at least one sociocivic category to see its chart.
                        </p>
                        <template v-for="panel in visiblePanels" :key="panel.key">
                            <div
                                v-if="showSkeleton && panel.visualization === 'boolean'"
                                class="h-48 animate-pulse rounded-xl bg-slate-100"
                            />
                            <div
                                v-else-if="panel.visualization === 'boolean'"
                                class="rounded-xl border border-slate-200 p-4"
                            >
                                <h3 class="text-base font-semibold text-slate-900">{{ panel.title }}</h3>
                                <div class="mt-4">
                                    <DashboardBooleanLists
                                        :chart="panelChart(panel)"
                                        @select-group="(group) => openSlice(group, panel)"
                                    />
                                </div>
                            </div>
                            <DashboardPieCard
                                v-else
                                compact
                                :title="panel.title"
                                :views="panelViews(panel)"
                                :charts="panelCharts(panel)"
                                :loading="showSkeleton"
                                @select-slice="(slice) => openSlice(slice, panel)"
                            />
                        </template>
                    </div>
                </article>

                <article
                    v-if="canViewReports && selectedCategory"
                    class="rbim-card space-y-4 p-5 print:hidden sm:p-6"
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
import AppLayout from '@/layouts/AppLayout.vue';
import DashboardBooleanLists from '@/components/dashboard/DashboardBooleanLists.vue';
import DashboardDrilldown from '@/components/dashboard/DashboardDrilldown.vue';
import DashboardModuleFilters from '@/components/dashboard/DashboardModuleFilters.vue';
import DashboardPieCard from '@/components/dashboard/DashboardPieCard.vue';
import DashboardStatCard from '@/components/dashboard/DashboardStatCard.vue';
import { useAuth } from '@/composables/useAuth';
import { CHART_SECTIONS, SUMMARY_CARDS } from '@/constants/dashboardCharts';
import { extractErrorMessage } from '@/services/http';
import { fetchDashboardStatistics } from '@/services/dashboardService';
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
import { formatDateTime, formatDisplayName } from '@/utils/format';

const { user, hasPermission } = useAuth();

const canViewReports = computed(() => hasPermission('report.view'));
const canExport = computed(() => hasPermission('report.create'));

const statistics = ref(null);
const loading = ref(false);
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

const sectionKey = ref(CHART_SECTIONS[0].key);
const chartKey = ref(CHART_SECTIONS[0].charts[0].key);

const showSkeleton = computed(() => loading.value && !statistics.value);

const selectedSection = computed(() => (
    CHART_SECTIONS.find((section) => section.key === sectionKey.value) ?? CHART_SECTIONS[0]
));

const selectedChart = computed(() => (
    selectedSection.value.charts.find((chart) => chart.key === chartKey.value) ?? selectedSection.value.charts[0]
));

const modulePanels = computed(() => (
    selectedChart.value.panels ?? [{
        key: selectedChart.value.key,
        title: selectedChart.value.title,
        visualization: selectedChart.value.visualization,
        views: selectedChart.value.views,
    }]
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
    const mapping = reportMapping(chartKey.value);

    return categories.value.find((item) => item.key === mapping?.category) ?? null;
});

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

    if (panel.key === selectedChart.value.key && selectedChart.value.views) {
        return selectedChart.value.views;
    }

    return [{ key: panelDataKey(panel), label: panel.title }];
}

function panelCharts(panel) {
    const charts = statistics.value?.charts ?? {};
    const next = {};

    panelViews(panel).forEach((view) => {
        const chart = charts[view.key] ?? charts[panelDataKey(panel)];

        if (!chart || chart.kind === 'boolean') {
            return;
        }

        const slices = visibleSlices(chart, selectedCategory.value, filterState.value, chartKey.value, panel);
        next[view.key] = {
            ...chart,
            slices,
            total: slices.reduce((sum, slice) => sum + (slice.count ?? 0), 0),
        };
    });

    return next;
}

const heading = computed(() => (
    selectedCategory.value
        ? reportHeading(selectedCategory.value, filterState.value)
        : { title: selectedChart.value.title, subtitle: selectedChart.value.subtitle }
));

const reportTitle = computed(() => heading.value.title);
const reportSubtitle = computed(() => heading.value.subtitle);

watch(sectionKey, () => {
    chartKey.value = selectedSection.value.charts[0].key;
});

watch([sectionKey, chartKey], () => {
    resetModule();
});

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

function primedFilters(category) {
    const next = blankFilters(category);
    const mapping = reportMapping(chartKey.value);

    if (mapping?.topics && next.topics) {
        next.topics.ids = [...mapping.topics];
    }

    return next;
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
    if (!canViewReports.value || !selectedCategory.value) {
        return;
    }

    filterState.value = applySliceToFilters(selectedCategory.value, filterState.value, chartKey.value, slice, panel);
    const names = slice.label ? [slice.label] : [];
    const headingNow = reportHeading(selectedCategory.value, filterState.value);
    drillTitle.value = headingNow.title;
    drillSubtitle.value = names[0] || headingNow.subtitle;
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

const displayName = computed(() => (
    user.value?.full_name || formatDisplayName(user.value?.username) || 'User'
));

const updatedLabel = computed(() => (
    statistics.value?.generated_at ? formatDateTime(statistics.value.generated_at) : ''
));

async function load() {
    loading.value = true;
    error.value = '';

    try {
        statistics.value = await fetchDashboardStatistics();
    } catch (exception) {
        error.value = extractErrorMessage(exception, 'Please check your connection and try again.');
    } finally {
        loading.value = false;
    }
}

onMounted(async () => {
    await load();

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

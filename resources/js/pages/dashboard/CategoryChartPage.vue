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
                <header class="flex flex-wrap items-start justify-between gap-4">
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
                            :title="panel.title"
                            :views="panelViews(panel)"
                            :charts="panelCharts(panel)"
                            :loading="showSkeleton"
                            hint="Click a slice or a value to open the matching records."
                            @select-slice="(slice) => openSlice(slice, panel)"
                        />
                    </template>
                </div>

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
import { findDashboardChart } from '@/constants/dashboardCharts';
import AppLayout from '@/layouts/AppLayout.vue';
import { fetchDashboardStatistics } from '@/services/dashboardService';
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

const located = computed(() => findDashboardChart(String(route.params.chartKey || '')));
const missing = computed(() => !located.value);
const chart = computed(() => located.value?.chart ?? null);
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

async function load() {
    if (missing.value) {
        loading.value = false;

        return;
    }

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

function goBack() {
    if (window.history.length > 1) {
        router.back();

        return;
    }

    router.push({ name: 'dashboard' });
}

watch(() => route.params.chartKey, async () => {
    statistics.value = null;
    await load();
    resetModule();
});

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

<template>
    <AppLayout title="Dashboard">
        <div class="@container space-y-8">
            <header class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-slate-900">
                        Welcome back, {{ displayName }}!
                    </h2>
                    <p class="mt-1 text-sm text-slate-600">
                        Barangay statistics at a glance.<template v-if="canViewReports"> Use Report Generation for filtered, detailed reports.</template>
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
                    class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                    role="alert"
                >
                    <span>Refresh failed: {{ error }} Showing the last loaded statistics.</span>
                    <button type="button" class="rbim-btn-danger" @click="load">
                        Retry
                    </button>
                </div>

                <section aria-label="Summary" class="grid grid-cols-1 gap-4 @lg:grid-cols-2 @4xl:grid-cols-4">
                    <DashboardStatCard
                        v-for="card in SUMMARY_CARDS"
                        :key="card.key"
                        :label="card.label"
                        :icon="card.icon"
                        :value="statistics?.summary?.[card.key] ?? null"
                        :loading="showSkeleton"
                    />
                </section>

                <DashboardPieCard
                    :title="selectedChart.title"
                    :subtitle="`${selectedSection.title} · ${selectedChart.subtitle}`"
                    :views="selectedViews"
                    :charts="statistics?.charts ?? null"
                    :loading="showSkeleton"
                >
                    <template #controls>
                        <label class="min-w-[12rem] flex-1 @2xl:flex-none">
                            <span class="rbim-label">Category</span>
                            <select v-model="sectionKey" class="rbim-input" @change="onSectionChange">
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
                    </template>
                </DashboardPieCard>
            </template>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import DashboardPieCard from '@/components/dashboard/DashboardPieCard.vue';
import DashboardStatCard from '@/components/dashboard/DashboardStatCard.vue';
import { useAuth } from '@/composables/useAuth';
import { CHART_SECTIONS, SUMMARY_CARDS } from '@/constants/dashboardCharts';
import { extractErrorMessage } from '@/services/http';
import { fetchDashboardStatistics } from '@/services/dashboardService';
import { formatDateTime, formatDisplayName } from '@/utils/format';

const { user, hasPermission } = useAuth();

const canViewReports = computed(() => hasPermission('report.view'));

const statistics = ref(null);
const loading = ref(false);
const error = ref('');

const sectionKey = ref(CHART_SECTIONS[0].key);
const chartKey = ref(CHART_SECTIONS[0].charts[0].key);

const showSkeleton = computed(() => loading.value && !statistics.value);

const selectedSection = computed(() => (
    CHART_SECTIONS.find((section) => section.key === sectionKey.value) ?? CHART_SECTIONS[0]
));

const selectedChart = computed(() => (
    selectedSection.value.charts.find((chart) => chart.key === chartKey.value) ?? selectedSection.value.charts[0]
));

const selectedViews = computed(() => (
    selectedChart.value.views ?? [{ key: selectedChart.value.key, label: selectedChart.value.title }]
));

function onSectionChange() {
    chartKey.value = selectedSection.value.charts[0].key;
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

onMounted(load);
</script>

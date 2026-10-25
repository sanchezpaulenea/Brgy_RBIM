<template>
    <article class="rbim-card @container p-5 sm:p-6">
        <header class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 pb-5">
            <div class="min-w-0">
                <h2 class="text-base font-semibold text-slate-900">
                    {{ title }}
                </h2>
                <p class="mt-0.5 text-sm text-slate-500">
                    {{ subtitle }}
                </p>
            </div>

            <div class="flex w-full flex-wrap gap-3 @2xl:w-auto">
                <slot name="controls" />
            </div>
        </header>

        <div v-if="loading" class="grid animate-pulse grid-cols-1 items-center gap-8 pt-6 @3xl:grid-cols-[minmax(0,3fr)_minmax(0,2fr)] @3xl:gap-10" aria-hidden="true">
            <div class="mx-auto aspect-square w-full max-w-[34rem] rounded-full bg-slate-100" />
            <div class="space-y-4 @3xl:self-start">
                <div class="h-6 w-48 rounded-full bg-slate-100" />
                <div v-for="line in 5" :key="line" class="space-y-2">
                    <div class="flex items-center gap-3">
                        <span class="h-3 w-3 rounded-full bg-slate-200" />
                        <span class="h-3.5 flex-1 rounded bg-slate-100" />
                        <span class="h-3.5 w-16 rounded bg-slate-100" />
                    </div>
                    <div class="h-1.5 rounded-full bg-slate-100" />
                </div>
            </div>
        </div>

        <template v-else-if="chart">
            <div class="flex flex-wrap items-center justify-between gap-3 pt-5">
                <p class="inline-flex items-center gap-1.5 rounded-full bg-brand-muted px-3 py-1.5 text-sm font-medium text-brand">
                    {{ chart.metric }}:
                    <span class="font-semibold tabular-nums">{{ formatNumber(chart.total) }}</span>
                </p>

                <div
                    v-if="views.length > 1"
                    class="inline-flex rounded-lg bg-slate-100 p-1"
                    role="group"
                    :aria-label="`${title} metric`"
                >
                    <button
                        v-for="view in views"
                        :key="view.key"
                        type="button"
                        class="cursor-pointer rounded-md px-3 py-1.5 text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-brand"
                        :class="view.key === activeKey ? 'bg-white text-brand shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                        :aria-pressed="view.key === activeKey"
                        @click="activeKey = view.key"
                    >
                        {{ view.label }}
                    </button>
                </div>
            </div>

            <div v-if="chart.total === 0" class="flex flex-col items-center justify-center py-16 text-center">
                <span class="flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <svg class="h-7 w-7" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z" />
                        <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z" />
                    </svg>
                </span>
                <p class="mt-4 text-sm font-semibold text-slate-700">
                    No data
                </p>
                <p class="mt-1 max-w-sm text-sm text-slate-500">
                    {{ emptyMessage }}
                </p>
            </div>

            <div v-else class="grid grid-cols-1 items-center gap-8 pt-6 @3xl:grid-cols-[minmax(0,3fr)_minmax(0,2fr)] @3xl:gap-10">
                <div ref="pieFrameRef" class="relative mx-auto aspect-square w-full max-w-[34rem]">
                    <Pie
                        v-if="pieInView"
                        :key="renderKey"
                        ref="chartRef"
                        :data="chartData"
                        :options="chartOptions"
                        :plugins="[percentLabels]"
                        :aria-label="ariaLabel"
                    />
                </div>

                <div class="@3xl:self-start">
                    <div class="flex items-center justify-between px-2 pb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">
                        <span>Value</span>
                        <span>Count · Share</span>
                    </div>
                    <ul class="space-y-1">
                        <li v-for="(slice, index) in chart.slices" :key="slice.label">
                            <button
                                type="button"
                                class="w-full cursor-pointer rounded-lg px-2 py-2 text-left transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-brand"
                                :class="hidden.has(index) ? 'opacity-45' : ''"
                                :aria-pressed="!hidden.has(index)"
                                :title="hidden.has(index) ? 'Click to show this slice' : 'Click to hide this slice'"
                                @click="toggleSlice(index)"
                            >
                                <span class="flex items-center gap-3 text-sm">
                                    <span
                                        class="h-3 w-3 shrink-0 rounded-full"
                                        :style="{ backgroundColor: colors[index] }"
                                        aria-hidden="true"
                                    />
                                    <span
                                        class="min-w-0 flex-1 text-slate-700"
                                        :class="hidden.has(index) ? 'line-through' : ''"
                                    >
                                        {{ slice.label }}
                                        <span v-if="slice.kind === 'others'" class="text-xs text-slate-400">
                                            ({{ slice.grouped }} values)
                                        </span>
                                    </span>
                                    <span class="shrink-0 font-semibold tabular-nums text-slate-900">
                                        {{ formatNumber(slice.count) }}
                                    </span>
                                    <span class="w-14 shrink-0 text-right tabular-nums text-slate-500">
                                        {{ formatPercent(slice.count, chart.total) }}
                                    </span>
                                </span>
                                <span class="mt-1.5 ml-6 block h-1.5 overflow-hidden rounded-full bg-slate-100" aria-hidden="true">
                                    <span
                                        class="block h-full rounded-full"
                                        :style="{ width: barWidth(slice.count, chart.total), backgroundColor: colors[index] }"
                                    />
                                </span>
                            </button>
                        </li>
                    </ul>
                    <p class="mt-3 px-2 text-xs text-slate-400">
                        Click a value to hide or show it in the chart.
                    </p>
                </div>
            </div>
        </template>
    </article>
</template>

<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { ArcElement, Chart as ChartJS, Tooltip } from 'chart.js';
import { Pie } from 'vue-chartjs';
import { useTheme } from '@/composables/useTheme';
import { sliceColors } from '@/constants/dashboardCharts';

ChartJS.register(ArcElement, Tooltip);

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    subtitle: {
        type: String,
        default: '',
    },
    views: {
        type: Array,
        required: true,
    },
    charts: {
        type: Object,
        default: null,
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const numberFormat = new Intl.NumberFormat('en-PH');
const { theme } = useTheme();

const chartRef = ref(null);
const pieFrameRef = ref(null);
const pieInView = ref(false);
let pieObserver = null;

/*
 * The pie usually sits below the fold, so mounting it on load would play the
 * opening sweep before anyone scrolls to it. Mount it once it is on screen.
 */
watch(pieFrameRef, (frame) => {
    pieObserver?.disconnect();
    pieObserver = null;

    if (!frame || pieInView.value) {
        return;
    }

    if (typeof IntersectionObserver === 'undefined') {
        pieInView.value = true;

        return;
    }

    pieObserver = new IntersectionObserver((entries) => {
        if (entries.some((entry) => entry.isIntersecting)) {
            pieInView.value = true;
            pieObserver?.disconnect();
            pieObserver = null;
        }
    }, { threshold: 0.3 });
    pieObserver.observe(frame);
});

onBeforeUnmount(() => pieObserver?.disconnect());
const activeKey = ref(props.views[0]?.key ?? null);
const hidden = ref(new Set());
const renderKey = ref(0);

const chart = computed(() => props.charts?.[activeKey.value] ?? null);
const colors = computed(() => sliceColors(chart.value?.slices ?? []));

const chartData = computed(() => ({
    labels: chart.value.slices.map((slice) => slice.label),
    datasets: [{
        data: chart.value.slices.map((slice) => slice.count),
        backgroundColor: colors.value,
        borderColor: theme.value === 'dark' ? '#18201c' : '#ffffff',
        borderWidth: 2,
        hoverOffset: 10,
    }],
}));

const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    layout: { padding: 10 },
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#1e293b',
            padding: 12,
            cornerRadius: 8,
            boxPadding: 4,
            titleFont: { weight: '600' },
            callbacks: {
                label: (context) => ` ${formatNumber(context.parsed)} (${formatPercent(context.parsed, chart.value.total)})`,
                footer: (items) => {
                    const slice = chart.value.slices[items[0]?.dataIndex];

                    return slice?.kind === 'others' ? `${slice.grouped} smaller values combined` : '';
                },
            },
        },
    },
}));

/*
 * Slices under this share are too thin to hold a label; the tooltip and the
 * legend still show their percentage.
 */
const MIN_LABEL_SHARE = 0.04;

const percentLabels = {
    id: 'percentLabels',
    afterDatasetsDraw(instance) {
        const meta = instance.getDatasetMeta(0);
        const total = chart.value?.total ?? 0;
        const { ctx } = instance;

        if (!total) {
            return;
        }

        const fontSize = Math.round(Math.min(Math.max(instance.width / 30, 11), 16));

        ctx.save();
        ctx.font = `600 ${fontSize}px 'Instrument Sans', ui-sans-serif, system-ui, sans-serif`;
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';

        meta.data.forEach((arc, index) => {
            const count = chart.value.slices[index]?.count ?? 0;

            if (!instance.getDataVisibility(index) || count / total < MIN_LABEL_SHARE) {
                return;
            }

            const { x, y } = arc.tooltipPosition();
            ctx.fillStyle = labelColor(colors.value[index]);
            ctx.fillText(formatPercent(count, total), x, y);
        });

        ctx.restore();
    },
};

function labelColor(hex) {
    const value = Number.parseInt(String(hex).replace('#', ''), 16);
    const red = (value >> 16) & 255;
    const green = (value >> 8) & 255;
    const blue = value & 255;
    const luminance = (0.299 * red) + (0.587 * green) + (0.114 * blue);

    return luminance > 160 ? '#1e293b' : '#ffffff';
}

const ariaLabel = computed(() => {
    const parts = chart.value.slices.map((slice) => `${slice.label} ${formatNumber(slice.count)}`);

    return `${props.title} pie chart, ${chart.value.metric} ${formatNumber(chart.value.total)}: ${parts.join(', ')}`;
});

const EMPTY_MESSAGES = {
    'Total Household Records': 'No households are registered yet. This chart fills in once households are added.',
    'Total Resident Records': 'No residents are registered yet. This chart fills in once residents are added.',
    'Total Diseases Recorded': 'No household questionnaire has listed a common disease yet.',
    'Total Primary Needs Recorded': 'No household questionnaire has listed a primary need yet.',
};

const emptyMessage = computed(() => (
    EMPTY_MESSAGES[chart.value?.metric] ?? 'Nothing has been recorded for this category yet.'
));

watch(() => props.views, (views) => {
    activeKey.value = views[0]?.key ?? null;
});

/*
 * Chart.js keeps hidden slices on the instance across data updates, so a new
 * dataset gets a fresh canvas to match the cleared legend.
 */
watch(chart, () => {
    hidden.value = new Set();
    renderKey.value += 1;
});

function toggleSlice(index) {
    const instance = chartRef.value?.chart;

    if (!instance) {
        return;
    }

    instance.toggleDataVisibility(index);
    instance.update();

    const next = new Set(hidden.value);

    if (next.has(index)) {
        next.delete(index);
    } else {
        next.add(index);
    }

    hidden.value = next;
}

function formatNumber(value) {
    return numberFormat.format(value ?? 0);
}

function formatPercent(count, total) {
    if (!total) {
        return '0%';
    }

    const percent = (count / total) * 100;

    if (percent > 0 && percent < 0.1) {
        return '<0.1%';
    }

    return `${percent.toFixed(1)}%`;
}

function barWidth(count, total) {
    return total ? `${Math.max((count / total) * 100, 1)}%` : '0%';
}
</script>

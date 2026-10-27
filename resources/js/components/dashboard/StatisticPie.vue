<template>
    <div class="grid grid-cols-1 items-center gap-8 lg:grid-cols-2">
        <div class="mx-auto w-full max-w-md">
            <svg viewBox="0 0 200 200" class="h-auto w-full" role="img" :aria-label="chart.title">
                <circle v-if="singleSlice" cx="100" cy="100" r="78" :fill="slices[0].color" />
                <path
                    v-for="slice in arcs"
                    v-else
                    :key="slice.id + slice.label"
                    :d="slice.path"
                    :fill="slice.color"
                    class="cursor-pointer transition hover:opacity-80 focus:outline focus:outline-2 focus:outline-offset-2 focus:outline-[#344d3f]"
                    tabindex="0"
                    role="button"
                    :aria-pressed="isActive(slice)"
                    :aria-label="`${slice.label}, ${slice.count}, ${slice.percent} percent`"
                    @click="choose(slice)"
                    @keydown.enter.prevent="choose(slice)"
                    @keydown.space.prevent="choose(slice)"
                />
                <text
                    v-for="slice in labelled"
                    :key="`label-${slice.id}`"
                    :x="slice.labelX"
                    :y="slice.labelY"
                    text-anchor="middle"
                    class="fill-slate-900 text-[11px] font-semibold"
                >
                    {{ slice.label }} {{ slice.percent }}%
                </text>
            </svg>
        </div>

        <div>
            <h3 class="text-lg font-bold uppercase tracking-wide text-slate-900">
                {{ chart.title }}
            </h3>
            <div class="mt-5 grid grid-cols-1 gap-x-8 gap-y-3 sm:grid-cols-2" role="list">
                <button
                    v-for="slice in slices"
                    :key="`legend-${slice.id}-${slice.label}`"
                    type="button"
                    class="flex cursor-pointer items-center gap-3 rounded-lg px-1 py-1 text-left focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#344d3f]"
                    :class="isActive(slice) ? 'bg-[#c6cbbb]/50' : ''"
                    :aria-pressed="isActive(slice)"
                    @click="choose(slice)"
                >
                    <span class="h-4 w-8 shrink-0 rounded-sm" :style="{ backgroundColor: slice.color }" aria-hidden="true" />
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-medium text-slate-800">{{ slice.label }}</span>
                        <span class="block text-xs tabular-nums text-slate-500">{{ slice.count.toLocaleString('en-US') }} · {{ slice.percent }}%</span>
                    </span>
                </button>
            </div>
        </div>

        <table class="sr-only">
            <caption>{{ chart.title }} values</caption>
            <thead>
                <tr>
                    <th>Group</th>
                    <th>Count</th>
                    <th>Percent</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="slice in slices" :key="`text-${slice.id}-${slice.label}`">
                    <td>{{ slice.label }}</td>
                    <td>{{ slice.count }}</td>
                    <td>{{ slice.percent }}%</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    chart: {
        type: Object,
        required: true,
    },
    activeValue: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['select']);

const slices = computed(() => props.chart?.slices ?? []);
const singleSlice = computed(() => slices.value.length === 1);

const arcs = computed(() => {
    const total = slices.value.reduce((sum, slice) => sum + slice.count, 0);

    if (total <= 0 || slices.value.length < 2) {
        return [];
    }

    let angle = -Math.PI / 2;

    return slices.value.map((slice) => {
        const sweep = (slice.count / total) * Math.PI * 2;
        const start = angle;
        const end = angle + sweep;
        angle = end;
        const mid = start + sweep / 2;

        return {
            ...slice,
            path: wedge(100, 100, 78, start, end),
            labelX: 100 + Math.cos(mid) * 96,
            labelY: 100 + Math.sin(mid) * 96,
        };
    });
});

const labelled = computed(() => arcs.value.filter((slice) => slice.percent >= 8));

function wedge(cx, cy, radius, start, end) {
    const x1 = cx + radius * Math.cos(start);
    const y1 = cy + radius * Math.sin(start);
    const x2 = cx + radius * Math.cos(end);
    const y2 = cy + radius * Math.sin(end);
    const large = end - start > Math.PI ? 1 : 0;

    return `M ${cx} ${cy} L ${x1} ${y1} A ${radius} ${radius} 0 ${large} 1 ${x2} ${y2} Z`;
}

function isActive(slice) {
    return props.activeValue !== '' && String(slice.id) === String(props.activeValue);
}

function choose(slice) {
    if (!slice.filter) {
        return;
    }

    emit('select', {
        key: slice.filter,
        value: isActive(slice) ? '' : String(slice.id),
    });
}
</script>

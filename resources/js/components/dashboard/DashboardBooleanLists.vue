<template>
    <div>
        <p class="inline-flex items-center gap-1.5 rounded-full bg-brand-muted px-3 py-1.5 text-sm font-medium text-brand">
            {{ chart.metric }}:
            <span class="font-semibold tabular-nums">{{ formatNumber(chart.total) }}</span>
        </p>

        <div v-if="chart.total === 0" class="flex flex-col items-center justify-center py-16 text-center">
            <p class="text-sm font-semibold text-slate-700">No data</p>
            <p class="mt-1 max-w-sm text-sm text-slate-500">Nothing has been recorded for this category yet.</p>
        </div>

        <div v-else class="mt-6 grid grid-cols-1 gap-4 @2xl:grid-cols-2">
            <section
                v-for="group in chart.groups"
                :key="group.key"
                class="rounded-xl border border-slate-200 bg-slate-50/60 p-4"
            >
                <button
                    type="button"
                    class="flex w-full items-center justify-between gap-3 text-left"
                    @click="$emit('select-group', group)"
                >
                    <h3 class="text-sm font-semibold text-slate-900">{{ group.label }}</h3>
                    <span class="inline-flex min-w-[2.5rem] items-center justify-center rounded-full bg-brand px-2.5 py-1 text-xs font-semibold text-white">
                        {{ formatNumber(group.count) }}
                    </span>
                </button>
                <ul v-if="group.items.length" class="mt-3 max-h-64 space-y-1 overflow-y-auto">
                    <li v-for="item in group.items" :key="item.id">
                        <button
                            type="button"
                            class="w-full rounded-lg px-2 py-1.5 text-left text-sm text-slate-700 hover:bg-white"
                            @click="$emit('select-group', group)"
                        >
                            {{ item.name }}
                        </button>
                    </li>
                </ul>
                <p v-else class="mt-3 text-sm text-slate-500">No matching records.</p>
            </section>
        </div>
    </div>
</template>

<script setup>
defineProps({
    chart: {
        type: Object,
        required: true,
    },
});

defineEmits(['select-group']);

const numberFormat = new Intl.NumberFormat('en-PH');

function formatNumber(value) {
    return numberFormat.format(value ?? 0);
}
</script>

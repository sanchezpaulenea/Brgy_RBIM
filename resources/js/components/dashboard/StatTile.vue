<template>
    <RouterLink
        :to="{ name: 'dashboard-statistic', params: { statId: tile.id } }"
        class="flex min-h-40 items-center gap-5 rounded-2xl bg-white px-6 py-6 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:shadow-md hover:ring-[#247a3f] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#247a3f]"
    >
        <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-[#c6cbbb] text-[#344d3f]" aria-hidden="true">
            <StatTileIcon :name="tile.icon" />
        </span>
        <span class="min-w-0 flex-1 text-center">
            <span class="block text-[11px] font-semibold uppercase leading-tight tracking-wide text-slate-600 sm:text-xs">
                {{ tile.label }}
            </span>
            <span class="mt-1 block text-2xl font-bold tabular-nums leading-none text-[#344d3f]">
                {{ formatted }}
            </span>
            <span v-if="isEmpty" class="mt-1 block text-xs font-medium text-slate-500">
                No records yet
            </span>
        </span>
    </RouterLink>
</template>

<script setup>
import { computed } from 'vue';
import StatTileIcon from '@/components/dashboard/StatTileIcon.vue';

const props = defineProps({
    tile: {
        type: Object,
        required: true,
    },
});

const formatted = computed(() => Number(props.tile.count ?? 0).toLocaleString('en-US'));
const isEmpty = computed(() => Number(props.tile.count ?? 0) === 0);
</script>

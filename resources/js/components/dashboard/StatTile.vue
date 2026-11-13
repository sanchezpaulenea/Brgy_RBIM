<template>
    <RouterLink
        :to="{ name: 'dashboard-statistic', params: { statId: tile.id } }"
        class="flex h-full min-h-[6.5rem] w-full items-center gap-3 rounded-[1.75rem] border border-[#2f3e33] bg-white px-4 py-3.5 transition hover:-translate-y-0.5 hover:border-[#247a3f] hover:shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#247a3f]"
    >
        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#c6cbbb] text-[#344d3f]" aria-hidden="true">
            <StatTileIcon :name="tile.icon" />
        </span>
        <span class="min-w-0 flex-1 text-center">
            <span class="block text-xs font-semibold uppercase leading-tight tracking-wide text-slate-600 sm:text-sm">
                {{ tile.label }}
            </span>
            <span class="mt-0.5 block text-xl font-bold tabular-nums leading-none text-[#344d3f]">
                {{ formatted }}
            </span>
            <span v-if="isEmpty" class="mt-0.5 block text-[11px] font-medium text-slate-500">
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

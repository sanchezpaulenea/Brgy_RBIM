<template>
    <RouterLink
        :to="{ name: 'dashboard-category', params: { chartKey: card.key } }"
        class="flex h-full w-full items-center gap-3 border border-[#2f3e33] bg-white px-4 py-3.5 transition hover:-translate-y-0.5 hover:border-[#247a3f] hover:shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#247a3f]"
        :class="layout === 'tile'
            ? 'min-h-[6.5rem] rounded-[1.75rem]'
            : 'min-h-[5.25rem] rounded-[1.75rem]'"
    >
        <span
            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#c6cbbb] text-[#344d3f]"
            aria-hidden="true"
        >
            <StatTileIcon :name="card.icon" />
        </span>
        <span
            class="min-w-0 flex-1 text-center font-semibold uppercase leading-tight tracking-wide"
            :class="layout === 'tile' ? 'text-xs text-slate-600 sm:text-sm' : 'text-xs text-slate-800 sm:text-sm'"
        >
            {{ card.label }}
        </span>
    </RouterLink>
</template>

<script setup>
import StatTileIcon from '@/components/dashboard/StatTileIcon.vue';

defineProps({
    card: {
        type: Object,
        required: true,
    },
    layout: {
        type: String,
        default: 'row',
    },
});
</script>

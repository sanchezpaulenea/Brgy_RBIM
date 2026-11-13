<template>
    <RouterLink
        :to="{ name: 'dashboard-category', params: { chartKey: card.key } }"
        class="border border-[#2f3e33] bg-white transition hover:-translate-y-0.5 hover:border-[#247a3f] hover:shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#247a3f]"
        :class="layout === 'tile'
            ? 'flex min-h-[6.5rem] items-center gap-3 rounded-[1.75rem] px-4 py-3.5'
            : 'flex min-h-[4.75rem] items-center gap-3 rounded-2xl px-4 py-3'"
    >
        <span
            class="flex shrink-0 items-center justify-center rounded-xl bg-[#c6cbbb] text-[#344d3f]"
            :class="layout === 'tile' ? 'h-12 w-12' : 'h-11 w-11'"
            aria-hidden="true"
        >
            <StatTileIcon :name="card.icon" />
        </span>
        <span
            class="min-w-0 font-semibold uppercase leading-tight tracking-wide text-slate-800"
            :class="layout === 'tile' ? 'flex-1 text-center text-[11px] text-slate-600' : 'text-left text-xs'"
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

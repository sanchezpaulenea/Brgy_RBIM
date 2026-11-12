<template>
    <RouterLink
        :to="{ name: 'dashboard-category', params: { chartKey: card.key } }"
        class="bg-white shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:shadow-md hover:ring-[#247a3f] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#247a3f]"
        :class="layout === 'tile'
            ? 'flex min-h-40 items-center gap-5 rounded-2xl px-6 py-6'
            : 'flex min-h-[5.75rem] items-center gap-4 rounded-2xl px-5 py-4'"
    >
        <span
            class="flex shrink-0 items-center justify-center rounded-2xl bg-[#c6cbbb] text-[#344d3f]"
            :class="layout === 'tile' ? 'h-16 w-16' : 'h-14 w-14 rounded-xl'"
            aria-hidden="true"
        >
            <StatTileIcon :name="card.icon" />
        </span>
        <span
            class="min-w-0 font-semibold uppercase leading-tight tracking-wide text-slate-800"
            :class="layout === 'tile' ? 'flex-1 text-center text-[11px] text-slate-600 sm:text-xs' : 'text-left text-xs'"
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

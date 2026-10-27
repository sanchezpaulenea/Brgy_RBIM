<template>
    <RouterLink
        :to="{ name: 'dashboard-category', params: { chartKey: card.key } }"
        class="group flex min-h-[5.5rem] flex-col justify-between rounded-2xl bg-white px-4 py-3 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:shadow-md hover:ring-[#247a3f] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#247a3f]"
    >
        <span class="text-sm font-semibold leading-snug text-slate-900">
            {{ card.label }}
        </span>
        <span class="mt-3 text-xs font-semibold text-[#247a3f] group-hover:underline">
            View
        </span>
    </RouterLink>
</template>

<script setup>
defineProps({
    card: {
        type: Object,
        required: true,
    },
});
</script>

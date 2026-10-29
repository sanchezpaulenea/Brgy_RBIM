<template>
    <RouterLink
        :to="{ name: 'dashboard-category', params: { chartKey: card.key } }"
        class="flex min-h-32 items-center justify-center rounded-2xl bg-white px-6 py-6 text-center text-lg font-semibold leading-snug text-slate-900 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:shadow-md hover:ring-[#247a3f] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#247a3f]"
    >
        {{ card.label }}
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

<template>
    <nav v-if="tabs.length > 1" class="flex flex-wrap gap-2">
        <RouterLink
            v-for="tab in tabs"
            :key="tab.name"
            :to="{ name: tab.name }"
            class="rounded-lg px-3 py-2 text-sm font-medium transition"
            :class="route.name === tab.name
                ? 'bg-brand text-white'
                : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50'"
        >
            {{ tab.label }}
        </RouterLink>
    </nav>
</template>

<script setup>
import { RouterLink, useRoute } from 'vue-router';

defineProps({
    tabs: {
        type: Array,
        default: () => [],
    },
});

const route = useRoute();
</script>

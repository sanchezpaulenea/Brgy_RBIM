<template>
    <nav v-if="tabs.length > 1" class="flex flex-wrap gap-2">
        <RouterLink
            v-for="tab in tabs"
            :key="tab.name"
            :to="{ name: tab.name }"
            class="rounded-lg px-3 py-2 text-sm font-medium transition"
            :class="isActive(tab)
                ? 'bg-brand text-white'
                : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50'"
        >
            {{ tab.label }}
        </RouterLink>
    </nav>
</template>

<script setup>
import { RouterLink, useRoute } from 'vue-router';

const props = defineProps({
    tabs: {
        type: Array,
        default: () => [],
    },
    activeName: {
        type: String,
        default: '',
    },
});

const route = useRoute();

function isActive(tab) {
    const current = props.activeName || route.name;

    return tab.name === current;
}
</script>

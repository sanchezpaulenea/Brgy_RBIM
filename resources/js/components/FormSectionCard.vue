<template>
    <article class="rbim-card overflow-hidden">
        <header class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-3 sm:px-5">
            <div class="flex min-w-0 items-center gap-2">
                <h3 class="truncate text-base font-semibold text-brand">{{ title }}</h3>
                <HelpTip v-if="help" :text="help" />
            </div>
            <button
                type="button"
                class="rbim-btn-collapse"
                :aria-expanded="!collapsed"
                @click="collapsed = !collapsed"
            >
                {{ collapsed ? 'Expand' : 'Collapse' }}
            </button>
        </header>
        <div v-show="!collapsed" class="p-4 sm:p-5">
            <slot />
        </div>
    </article>
</template>

<script setup>
import { ref } from 'vue';
import HelpTip from '@/components/HelpTip.vue';

defineProps({
    title: {
        type: String,
        required: true,
    },
    help: {
        type: String,
        default: '',
    },
});

const collapsed = ref(false);
</script>

<template>
    <span class="relative inline-flex">
        <button
            type="button"
            class="inline-flex h-6 w-6 items-center justify-center rounded-full border border-slate-300 bg-white text-xs font-semibold text-slate-600 hover:bg-slate-50"
            :aria-label="label"
            :aria-expanded="open"
            @click.stop="open = !open"
            @blur="open = false"
        >
            ?
        </button>
        <span
            v-if="open"
            class="absolute left-0 top-8 z-30 w-64 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs leading-5 text-slate-600 shadow-lg"
            role="tooltip"
        >
            {{ text }}
        </span>
    </span>
</template>

<script setup>
import { ref } from 'vue';

defineProps({
    text: {
        type: String,
        required: true,
    },
    label: {
        type: String,
        default: 'More information',
    },
});

const open = ref(false);
</script>

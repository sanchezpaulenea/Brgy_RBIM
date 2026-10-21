<template>
    <article class="rbim-card flex items-center gap-4 p-5">
        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-brand-muted text-brand">
            <svg class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path v-if="icon === 'home'" d="M10.707 2.293a1 1 0 00-1.414 0l-7 7A1 1 0 003 11h1v6a1 1 0 001 1h4v-4h2v4h4a1 1 0 001-1v-6h1a1 1 0 00.707-1.707l-7-7z" />
                <path v-else-if="icon === 'users'" d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                <path v-else-if="icon === 'clan'" d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18h4v-3a3 3 0 00-4.56-2.56A5.99 5.99 0 0116 15v3zM4.56 12.44A3 3 0 000 15v3h4v-3c0-.91.2-1.78.56-2.56z" />
                <path v-else fill-rule="evenodd" d="M12 1.586l-4 4v12.828l4-4V1.586zM3.707 3.293A1 1 0 002 4v10a1 1 0 00.293.707L6 18.414V5.586L3.707 3.293zM17.707 5.293L14 1.586v12.828l2.293 2.293A1 1 0 0018 16V6a1 1 0 00-.293-.707z" clip-rule="evenodd" />
            </svg>
        </span>

        <div class="min-w-0">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                {{ label }}
            </p>
            <div v-if="loading" class="mt-2 h-7 w-20 animate-pulse rounded bg-slate-100" aria-hidden="true" />
            <p v-else class="mt-0.5 text-2xl font-bold tabular-nums text-slate-900 sm:text-3xl">
                {{ formatted }}
            </p>
        </div>
    </article>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    label: {
        type: String,
        required: true,
    },
    value: {
        type: Number,
        default: null,
    },
    icon: {
        type: String,
        default: 'home',
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const formatted = computed(() => (
    props.value === null ? '—' : new Intl.NumberFormat('en-PH').format(props.value)
));
</script>

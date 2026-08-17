<template>
    <div
        v-if="open"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4"
        @click.self="emit('cancel')"
    >
        <div class="w-full max-w-md rounded-xl bg-white shadow-xl" role="dialog" aria-modal="true">
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-4">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">{{ title }}</h2>
                    <p class="mt-1 text-sm text-slate-600">{{ message }}</p>
                </div>
                <button
                    type="button"
                    class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                    aria-label="Close"
                    @click="emit('cancel')"
                >
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 8.586 15.293 3.293a1 1 0 111.414 1.414L11.414 10l5.293 5.293a1 1 0 01-1.414 1.414L10 11.414l-5.293 5.293a1 1 0 01-1.414-1.414L8.586 10 3.293 4.707a1 1 0 011.414-1.414L10 8.586z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
            <div class="flex justify-end gap-2 px-5 py-4">
                <button type="button" class="rbim-btn-outline" @click="emit('cancel')">
                    {{ cancelLabel }}
                </button>
                <button
                    type="button"
                    :class="variant === 'danger' ? 'rbim-btn-danger px-4 py-2.5 text-sm' : 'rbim-btn'"
                    @click="emit('confirm')"
                >
                    {{ confirmLabel }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    open: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Please confirm',
    },
    message: {
        type: String,
        default: '',
    },
    confirmLabel: {
        type: String,
        default: 'Confirm',
    },
    cancelLabel: {
        type: String,
        default: 'Cancel',
    },
    variant: {
        type: String,
        default: 'primary',
    },
});

const emit = defineEmits(['confirm', 'cancel']);
</script>

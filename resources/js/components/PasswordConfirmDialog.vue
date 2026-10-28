<template>
    <div
        v-if="open"
        class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/40 p-4"
        @click.self="cancel"
    >
        <form class="w-full max-w-md rounded-xl bg-white shadow-xl" role="dialog" aria-modal="true" @submit.prevent="submit">
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-4">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">{{ title }}</h2>
                    <p class="mt-1 text-sm text-slate-600">{{ message }}</p>
                </div>
                <button
                    type="button"
                    class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                    aria-label="Close"
                    :disabled="submitting"
                    @click="cancel"
                >
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 8.586 15.293 3.293a1 1 0 111.414 1.414L11.414 10l5.293 5.293a1 1 0 01-1.414 1.414L10 11.414l-5.293 5.293a1 1 0 01-1.414-1.414L8.586 10 3.293 4.707a1 1 0 011.414-1.414L10 8.586z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
            <div class="space-y-4 px-5 py-4">
                <div>
                    <label for="administrator-password" class="rbim-label">
                        Admin or Super Admin password<span class="rbim-required" aria-hidden="true">*</span>
                    </label>
                    <input
                        id="administrator-password"
                        ref="passwordInput"
                        v-model="password"
                        type="password"
                        name="admin_password"
                        autocomplete="current-password"
                        spellcheck="false"
                        maxlength="255"
                        class="rbim-input"
                        :class="{ 'rbim-input-error': error }"
                        :disabled="submitting"
                    >
                    <p v-if="error" class="rbim-error">{{ error }}</p>
                    <p v-else class="rbim-hint">Use the password for the Admin or Super Admin account you are signed in with.</p>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" class="rbim-btn-outline" :disabled="submitting" @click="cancel">
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :class="variant === 'danger' ? 'rbim-btn-danger px-4 py-2.5 text-sm' : 'rbim-btn'"
                        :disabled="submitting"
                    >
                        {{ submitting ? 'Checking...' : confirmLabel }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>

<script setup>
import { nextTick, ref, watch } from 'vue';

const props = defineProps({
    open: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Confirm with your password',
    },
    message: {
        type: String,
        default: '',
    },
    confirmLabel: {
        type: String,
        default: 'Confirm',
    },
    error: {
        type: String,
        default: '',
    },
    submitting: {
        type: Boolean,
        default: false,
    },
    variant: {
        type: String,
        default: 'primary',
    },
});

const emit = defineEmits(['confirm', 'cancel']);

const password = ref('');
const passwordInput = ref(null);

watch(() => props.open, async (isOpen) => {
    if (!isOpen) {
        password.value = '';

        return;
    }

    password.value = '';
    await nextTick();
    passwordInput.value?.focus();
});

function cancel() {
    if (props.submitting) {
        return;
    }

    emit('cancel');
}

function submit() {
    emit('confirm', password.value);
}
</script>

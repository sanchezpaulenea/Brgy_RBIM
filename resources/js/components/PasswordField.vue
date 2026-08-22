<template>
    <div>
        <label :for="inputId" class="rbim-label" :class="{ 'text-center': centerLabel }">
            {{ label }}
            <span v-if="required" class="rbim-required" aria-hidden="true">*</span>
        </label>
        <div class="relative">
            <input
                :id="inputId"
                ref="input"
                :value="modelValue"
                :type="visible ? 'text' : 'password'"
                :autocomplete="autocomplete"
                :disabled="disabled"
                :required="required"
                class="rbim-input pr-11"
                :class="{ 'rbim-input-error': error }"
                @input="emit('update:modelValue', $event.target.value)"
                @blur="emit('blur')"
            >
            <button
                type="button"
                class="absolute inset-y-0 right-0 flex w-11 items-center justify-center rounded-r-lg text-slate-500 transition hover:text-brand disabled:cursor-not-allowed disabled:opacity-60"
                :disabled="disabled"
                :aria-label="visible ? 'Hide password' : 'Show password'"
                :aria-pressed="visible"
                :title="visible ? 'Hide password' : 'Show password'"
                @click="toggle"
            >
                <svg v-if="visible" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M3.28 2.22a.75.75 0 10-1.06 1.06l2.2 2.2A9.7 9.7 0 001.17 9.4a1.1 1.1 0 000 1.2C2.4 12.66 5.7 15.5 10 15.5c1.4 0 2.68-.3 3.8-.8l2.92 2.92a.75.75 0 101.06-1.06l-14.5-14.5zM10 13.5c-3.2 0-5.94-2.06-7.16-3.9A8.3 8.3 0 015.5 6.56l1.7 1.7a3.5 3.5 0 004.54 4.54l1.02 1.02c-.85.31-1.78.48-2.76.48z" />
                    <path d="M9.1 5.06A5.4 5.4 0 0110 5c4.3 0 7.6 2.84 8.83 4.9a1.1 1.1 0 010 1.2 12.2 12.2 0 01-2.2 2.6l-1.08-1.08c.62-.55 1.16-1.15 1.6-1.72-1.21-1.84-3.95-3.9-7.15-3.9-.1 0-.2 0-.3.01L9.1 5.06z" />
                </svg>
                <svg v-else class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M10 4.5c-4.3 0-7.6 2.84-8.83 4.9a1.1 1.1 0 000 1.2C2.4 12.66 5.7 15.5 10 15.5s7.6-2.84 8.83-4.9a1.1 1.1 0 000-1.2C17.6 7.34 14.3 4.5 10 4.5zm0 9c-3.2 0-5.94-2.06-7.16-3.9C4.06 7.76 6.8 5.7 10 5.7s5.94 2.06 7.16 3.9C15.94 11.44 13.2 13.5 10 13.5z" />
                    <path d="M10 7.2a2.8 2.8 0 100 5.6 2.8 2.8 0 000-5.6z" />
                </svg>
            </button>
        </div>
        <p v-if="error" class="rbim-error text-left">{{ error }}</p>
        <p v-else-if="hint" class="rbim-hint text-left">{{ hint }}</p>
    </div>
</template>

<script setup>
import { ref } from 'vue';

defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    label: {
        type: String,
        default: 'Password',
    },
    inputId: {
        type: String,
        required: true,
    },
    autocomplete: {
        type: String,
        default: 'current-password',
    },
    error: {
        type: String,
        default: '',
    },
    hint: {
        type: String,
        default: '',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    required: {
        type: Boolean,
        default: false,
    },
    centerLabel: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue', 'blur']);

const visible = ref(false);
const input = ref(null);

function toggle() {
    visible.value = !visible.value;
    input.value?.focus();
}
</script>

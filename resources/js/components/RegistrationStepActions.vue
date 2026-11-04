<template>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <button
            v-if="showBack"
            type="button"
            class="rbim-btn-outline"
            :disabled="backDisabled"
            @click="$emit('back')"
        >
            Back
        </button>
        <div class="ml-auto flex items-center gap-4">
            <button
                v-if="showSkip"
                type="button"
                class="cursor-pointer px-1 py-2.5 text-sm font-semibold text-slate-700 transition hover:text-brand focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand disabled:cursor-not-allowed disabled:opacity-60"
                :disabled="skipDisabled"
                @click="$emit('skip')"
            >
                Skip
            </button>
            <button
                :type="submit ? 'submit' : 'button'"
                class="rbim-btn"
                :disabled="continueDisabled"
                @click="onContinue"
            >
                {{ continueLabel }}
            </button>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    showBack: {
        type: Boolean,
        default: true,
    },
    showSkip: {
        type: Boolean,
        default: false,
    },
    backDisabled: {
        type: Boolean,
        default: false,
    },
    skipDisabled: {
        type: Boolean,
        default: false,
    },
    continueDisabled: {
        type: Boolean,
        default: false,
    },
    continueLabel: {
        type: String,
        default: 'Continue',
    },
    submit: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['back', 'skip', 'continue']);

function onContinue(event) {
    if (props.submit) {
        return;
    }

    event.preventDefault();
    emit('continue');
}
</script>

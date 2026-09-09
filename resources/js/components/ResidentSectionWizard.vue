<template>
    <article class="rbim-card p-6">
        <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">
            {{ title }}
        </h2>
        <p class="mt-1 text-sm font-medium text-slate-900">
            {{ currentLabel }}
            <span v-if="progressLabel" class="ml-2 text-xs font-normal text-slate-500">{{ progressLabel }}</span>
        </p>
        <p class="mt-1 text-xs text-slate-500">
            Continue saves this section. Skip leaves it empty and flags it as missing.
            Fields marked with <span class="rbim-required">*</span> are required when you continue.
        </p>
        <p v-if="residentName" class="mt-2 text-sm text-slate-700">
            {{ residentName }}
        </p>

        <div v-if="error" class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ error }}
        </div>

        <form class="mt-4 space-y-6" novalidate @submit.prevent="onContinue">
            <ResidentProfilingSectionFields
                v-if="currentKey"
                :key="currentKey"
                :section="currentKey"
                :form="form"
                :errors="errors"
                :lookups="lookups"
                :resident="resident"
                :location="location"
                :id-prefix="idPrefix"
            />

            <div class="flex flex-wrap items-center gap-2">
                <button type="submit" class="rbim-btn" :disabled="saving">
                    {{ saving ? 'Saving...' : 'Continue' }}
                </button>
                <button type="button" class="rbim-btn-outline" :disabled="saving" @click="onSkip">
                    Skip
                </button>
                <button
                    type="button"
                    class="rbim-btn-outline"
                    :disabled="saving || isFirstStep"
                    @click="onBack"
                >
                    Back
                </button>
            </div>
        </form>
    </article>
</template>

<script setup>
import { computed, onMounted, watch } from 'vue';
import ResidentProfilingSectionFields from '@/components/ResidentProfilingSectionFields.vue';
import { useResidentSectionWizard } from '@/composables/useResidentSectionWizard';
import { profilingResidentReady } from '@/utils/residentProfiling';

const props = defineProps({
    resident: {
        type: Object,
        required: true,
    },
    lookups: {
        type: Object,
        default: () => ({}),
    },
    location: {
        type: Object,
        default: () => ({ barangay: '', city: '' }),
    },
    title: {
        type: String,
        default: 'Resident profile',
    },
    idPrefix: {
        type: String,
        default: 'profile-section',
    },
});

const emit = defineEmits(['finished']);

const {
    form,
    errors,
    saving,
    error,
    currentKey,
    currentLabel,
    isFirstStep,
    progressLabel,
    start,
    finish,
    handleSkip,
    handleBack,
    handleContinue,
} = useResidentSectionWizard({
    getResident: () => props.resident,
    getLookups: () => props.lookups,
    getLocation: () => props.location,
});

const residentName = computed(() => props.resident?.full_name || '');

function completeIfFinished(result) {
    if (result === 'finished') {
        finish();
        emit('finished');
    }
}

async function onContinue() {
    completeIfFinished(await handleContinue());
}

function onSkip() {
    completeIfFinished(handleSkip());
}

function onBack() {
    handleBack();
}

function beginWizard() {
    if (!profilingResidentReady(props.resident)) {
        return;
    }

    if (!start()) {
        emit('finished');
    }
}

onMounted(beginWizard);

watch(() => props.resident?.resident_id, (id, previousId) => {
    if (id && id !== previousId) {
        beginWizard();
    }
});

watch(() => profilingResidentReady(props.resident), (ready, wasReady) => {
    if (ready && !wasReady) {
        beginWizard();
    }
});
</script>

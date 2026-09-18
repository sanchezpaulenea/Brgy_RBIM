<template>
    <article class="rbim-card p-6">
        <div class="flex items-start justify-between gap-4">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">
                {{ title }}
            </h2>
            <p v-if="progressLabel" class="shrink-0 text-sm font-semibold uppercase tracking-wider text-slate-500">
                {{ progressLabel }}
            </p>
        </div>
        <p class="mt-1 text-xs text-slate-500">
            Continue saves the information in this section. Skip leaves the section incomplete and marks it as missing.
            Fields marked with <span class="rbim-required">*</span> are required to continue.
        </p>

        <div v-if="error" class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ error }}
        </div>

        <form class="mt-4 space-y-6" novalidate @submit.prevent="onContinue">
            <div class="space-y-4">
                <div class="space-y-1">
                    <p v-if="residentName" class="text-sm font-semibold uppercase tracking-wide text-brand">
                        {{ residentName }}
                    </p>
                    <h3 v-if="currentLabel" class="text-sm font-semibold text-slate-900">
                        {{ currentLabel }}
                    </h3>
                </div>
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
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        class="rbim-btn-outline"
                        :disabled="saving || isFirstStep"
                        @click="onBack"
                    >
                        Back
                    </button>
                    <button type="button" class="rbim-btn-outline" :disabled="saving" @click="onSkip">
                        Skip
                    </button>
                </div>
                <button type="submit" class="rbim-btn" :disabled="saving">
                    {{ saving ? 'Saving...' : 'Continue' }}
                </button>
            </div>
        </form>
    </article>
</template>

<script setup>
import { computed, onMounted, watch } from 'vue';
import ResidentProfilingSectionFields from '@/components/ResidentProfilingSectionFields.vue';
import { useResidentSectionWizard } from '@/composables/useResidentSectionWizard';
import { personDisplayName } from '@/utils/format';
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

const residentName = computed(() => {
    const name = props.resident?.full_name || personDisplayName(props.resident);

    return name ? name.toUpperCase() : '';
});

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

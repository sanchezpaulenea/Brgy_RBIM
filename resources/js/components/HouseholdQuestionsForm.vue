<template>
    <article class="rbim-card p-6">
        <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">
            Household questions
        </h2>
        <p class="mt-1 text-xs text-slate-500">
            Continue saves these household questions. Skip leaves them incomplete.
            Fields marked with <span class="rbim-required">*</span> are required to continue.
        </p>

        <div v-if="error" class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ error }}
        </div>

        <form class="mt-4 space-y-6" novalidate @submit.prevent="onContinue">
            <HouseholdQuestionsFields
                :form="form"
                :errors="errors"
                :lookups="lookups"
                id-prefix="register-questions"
            />

            <div class="flex flex-wrap items-center justify-between gap-2">
                <button type="button" class="rbim-btn-outline" :disabled="saving" @click="$emit('skip')">
                    Skip
                </button>
                <button type="submit" class="rbim-btn" :disabled="saving">
                    {{ saving ? 'Saving...' : 'Continue' }}
                </button>
            </div>
        </form>
    </article>
</template>

<script setup>
import { reactive, ref } from 'vue';
import HouseholdQuestionsFields from '@/components/HouseholdQuestionsFields.vue';
import { extractErrorMessage, extractValidationErrors } from '@/services/http';
import * as householdService from '@/services/householdService';
import {
    emptyHouseholdQuestionsForm,
    householdQuestionsPayload,
    validateHouseholdQuestions,
} from '@/utils/householdQuestions';
import { applyValidationErrors } from '@/utils/residentForm';

const props = defineProps({
    household: {
        type: Object,
        required: true,
    },
    lookups: {
        type: Object,
        default: () => ({}),
    },
    location: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits(['finished', 'skip']);

const saving = ref(false);
const error = ref('');
const form = reactive(emptyHouseholdQuestionsForm(props.location));
const errors = reactive({});

async function onContinue() {
    error.value = '';

    if (!validateHouseholdQuestions(form, errors)) {
        return;
    }

    saving.value = true;

    try {
        await householdService.createHouseholdQuestions(
            props.household.household_id,
            householdQuestionsPayload(form),
        );
        emit('finished');
    } catch (err) {
        const validationErrors = extractValidationErrors(err);

        if (Object.keys(validationErrors).length) {
            applyValidationErrors(errors, validationErrors);
            error.value = '';
        } else {
            error.value = extractErrorMessage(err, 'Unable to save household questions.');
        }
    } finally {
        saving.value = false;
    }
}
</script>

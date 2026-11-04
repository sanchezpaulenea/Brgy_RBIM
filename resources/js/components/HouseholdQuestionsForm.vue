<template>
    <article class="rbim-card p-6">
        <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">
            Household questions
        </h2>
        <p class="mt-1 text-xs text-slate-500">
            Continue saves these household questions and any pets listed below Q14.
            Skip leaves them incomplete. Fields marked with <span class="rbim-required">*</span> are required to continue.
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

            <HouseholdPetCensusForm
                ref="petForm"
                mode="register"
                embedded
                :household-id="household.household_id"
                :can-create="true"
                id-prefix="register-questions-pets"
            />

            <RegistrationStepActions
                :show-back="showBack"
                show-skip
                :back-disabled="saving"
                :skip-disabled="saving"
                :continue-disabled="saving"
                :continue-label="saving ? 'Saving...' : 'Continue'"
                @back="$emit('back')"
                @skip="$emit('skip')"
            />
        </form>
    </article>
</template>

<script setup>
import { reactive, ref } from 'vue';
import HouseholdPetCensusForm from '@/components/HouseholdPetCensusForm.vue';
import RegistrationStepActions from '@/components/RegistrationStepActions.vue';
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
    showBack: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['finished', 'skip', 'back']);

const saving = ref(false);
const error = ref('');
const petForm = ref(null);
const form = reactive(emptyHouseholdQuestionsForm(props.location));
const errors = reactive({});

async function onContinue() {
    error.value = '';

    if (!validateHouseholdQuestions(form, errors)) {
        return;
    }

    if (petForm.value?.validateAll?.() === false) {
        return;
    }

    saving.value = true;

    try {
        await householdService.createHouseholdQuestions(
            props.household.household_id,
            householdQuestionsPayload(form),
        );
        await petForm.value?.save?.();
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

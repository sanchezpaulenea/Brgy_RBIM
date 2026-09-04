<template>
    <article class="rbim-card p-6">
        <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">
            Encode assessment
        </h2>
        <p class="mt-1 text-xs text-slate-500">
            Fields marked with <span class="rbim-required">*</span> are required.
            Encoder, visit times, and the previous assessment are recorded automatically.
        </p>
        <p class="mt-4 text-sm text-slate-900">
            {{ householdLabel }}
        </p>

        <div v-if="error" class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ error }}
        </div>

        <div v-if="loading" class="mt-4 text-sm text-slate-500">
            Loading assessment form...
        </div>

        <form v-else class="mt-4 space-y-6" novalidate @submit.prevent="handleSave">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label for="assessment_census_status_id" class="rbim-label">
                        Census Status<span class="rbim-required" aria-hidden="true">*</span>
                    </label>
                    <select
                        id="assessment_census_status_id"
                        v-model="form.census_status_id"
                        class="rbim-input"
                        :class="{ 'rbim-input-error': errors.census_status_id }"
                    >
                        <option value="">Select census status</option>
                        <option v-for="status in censusStatuses" :key="status.id" :value="status.id">
                            {{ censusStatusLabel(status) }}
                        </option>
                    </select>
                    <p v-if="errors.census_status_id" class="rbim-error">{{ errors.census_status_id }}</p>
                </div>
                <BirthDateField
                    v-if="callbackSelected"
                    v-model="form.next_visit_date"
                    input-id="assessment_next_visit_date"
                    label="Next Visit Date"
                    placeholder="Select next visit date"
                    :min="today"
                    required
                    :show-age="false"
                    :error="errors.next_visit_date"
                />
                <div>
                    <label for="assessment_interviewer_id" class="rbim-label">
                        Interviewer<span class="rbim-required" aria-hidden="true">*</span>
                    </label>
                    <select
                        id="assessment_interviewer_id"
                        v-model="form.interviewer_id"
                        class="rbim-input"
                        :class="{ 'rbim-input-error': errors.interviewer_id }"
                    >
                        <option value="">Select interviewer</option>
                        <option v-for="person in personnel" :key="person.id" :value="person.id">
                            {{ person.label }}
                        </option>
                    </select>
                    <p v-if="errors.interviewer_id" class="rbim-error">{{ errors.interviewer_id }}</p>
                </div>
                <div>
                    <label for="assessment_supervisor_id" class="rbim-label">
                        Supervisor<span class="rbim-required" aria-hidden="true">*</span>
                    </label>
                    <select
                        id="assessment_supervisor_id"
                        v-model="form.supervisor_id"
                        class="rbim-input"
                        :class="{ 'rbim-input-error': errors.supervisor_id }"
                    >
                        <option value="">Select supervisor</option>
                        <option v-for="person in personnel" :key="person.id" :value="person.id">
                            {{ person.label }}
                        </option>
                    </select>
                    <p v-if="errors.supervisor_id" class="rbim-error">{{ errors.supervisor_id }}</p>
                </div>
                <div>
                    <p class="rbim-label">Encoder</p>
                    <p class="mt-1 text-sm text-slate-900">{{ encoderName }}</p>
                </div>
                <div>
                    <p class="rbim-label">Previous Assessment</p>
                    <p class="mt-1 text-sm text-slate-900">{{ previousAssessmentLabel }}</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button type="submit" class="rbim-btn" :disabled="saving">
                    {{ saving ? 'Saving...' : 'Save assessment' }}
                </button>
            </div>
        </form>
    </article>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import BirthDateField from '@/components/BirthDateField.vue';
import { useAuth } from '@/composables/useAuth';
import { extractErrorMessage, extractValidationErrors } from '@/services/http';
import * as householdService from '@/services/householdService';
import * as lookupService from '@/services/lookupService';
import {
    censusStatusLabel,
    formatDateTime,
    householdDisplayLabel,
    isCallbackCensusStatus,
    todayDate,
} from '@/utils/format';
import { applyValidationErrors, toId } from '@/utils/residentForm';

const props = defineProps({
    household: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['saved']);

const { user } = useAuth();

const loading = ref(true);
const saving = ref(false);
const error = ref('');
const censusStatuses = ref([]);
const personnel = ref([]);
const form = reactive({
    census_status_id: '',
    interviewer_id: '',
    supervisor_id: '',
    next_visit_date: '',
});
const errors = reactive({});
const today = todayDate();

const householdLabel = computed(() => householdDisplayLabel(props.household));
const encoderName = computed(() => {
    const last = user.value?.last_name?.trim();
    const first = user.value?.first_name?.trim();

    if (last && first) {
        return `${last}, ${first}`;
    }

    return user.value?.full_name || '—';
});
const selectedStatus = computed(() => (
    censusStatuses.value.find((status) => Number(status.id) === Number(form.census_status_id))
    ?? null
));
const callbackSelected = computed(() => isCallbackCensusStatus(selectedStatus.value));
const previousAssessmentLabel = computed(() => {
    const previous = props.household?.latest_assessment;

    if (!previous) {
        return 'None (first visit)';
    }

    const status = censusStatusLabel(previous);
    const when = formatDateTime(previous.visit_end || previous.visit_start);

    return `Assessment ${previous.assessment_id} — ${status}${when !== '—' ? `, ${when}` : ''}`;
});

function clearErrors() {
    Object.keys(errors).forEach((key) => {
        delete errors[key];
    });
}

watch(callbackSelected, (isCallback) => {
    if (!isCallback) {
        form.next_visit_date = '';
        delete errors.next_visit_date;
    }
});

async function loadOptions() {
    loading.value = true;
    error.value = '';

    try {
        const [statusItems, personnelItems] = await Promise.all([
            lookupService.fetchLookup('census-status'),
            householdService.fetchHouseholdAssessmentOptions(),
        ]);
        censusStatuses.value = statusItems;
        personnel.value = personnelItems;
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to load the assessment form.');
    } finally {
        loading.value = false;
    }
}

async function handleSave() {
    clearErrors();
    error.value = '';

    if (!toId(form.census_status_id)) {
        errors.census_status_id = 'Census status is required.';
    }

    if (!toId(form.interviewer_id)) {
        errors.interviewer_id = 'Interviewer is required.';
    }

    if (!toId(form.supervisor_id)) {
        errors.supervisor_id = 'Supervisor is required.';
    }

    if (callbackSelected.value && !form.next_visit_date) {
        errors.next_visit_date = 'Next visit date is required when census status is CB (Callback).';
    } else if (callbackSelected.value && form.next_visit_date < today) {
        errors.next_visit_date = 'Next visit date cannot be in the past.';
    }

    if (Object.keys(errors).length) {
        return;
    }

    saving.value = true;

    try {
        const item = await householdService.createHouseholdAssessment(props.household.household_id, {
            census_status_id: toId(form.census_status_id),
            interviewer_id: toId(form.interviewer_id),
            supervisor_id: toId(form.supervisor_id),
            next_visit_date: callbackSelected.value ? form.next_visit_date : null,
        });

        emit('saved', item);
    } catch (err) {
        const validationErrors = extractValidationErrors(err);

        if (Object.keys(validationErrors).length) {
            applyValidationErrors(errors, validationErrors);
        } else {
            error.value = extractErrorMessage(err, 'Unable to encode this assessment.');
        }
    } finally {
        saving.value = false;
    }
}

onMounted(loadOptions);
</script>

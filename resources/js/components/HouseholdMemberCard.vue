<template>
    <FormSectionCard :title="title" :help="help">
        <div class="grid gap-4 sm:grid-cols-4">
            <div>
                <label class="rbim-label" :for="`${idPrefix}-last_name`">
                    Last Name<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <input
                    :id="`${idPrefix}-last_name`"
                    :value="form.last_name"
                    type="text"
                    maxlength="45"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.last_name }"
                    :disabled="nameLocked"
                    @input="patch('last_name', $event.target.value)"
                >
                <p v-if="errors.last_name" class="rbim-error">{{ errors.last_name }}</p>
            </div>
            <div>
                <label class="rbim-label" :for="`${idPrefix}-first_name`">
                    First Name<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <input
                    :id="`${idPrefix}-first_name`"
                    :value="form.first_name"
                    type="text"
                    maxlength="45"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.first_name }"
                    :disabled="nameLocked"
                    @input="patch('first_name', $event.target.value)"
                >
                <p v-if="errors.first_name" class="rbim-error">{{ errors.first_name }}</p>
            </div>
            <div>
                <label class="rbim-label" :for="`${idPrefix}-middle_name`">Middle Name</label>
                <input
                    :id="`${idPrefix}-middle_name`"
                    :value="form.middle_name"
                    type="text"
                    maxlength="45"
                    class="rbim-input"
                    :disabled="nameLocked"
                    @input="patch('middle_name', $event.target.value)"
                >
            </div>
            <div>
                <label class="rbim-label" :for="`${idPrefix}-suffix`">Suffix (optional)</label>
                <input
                    :id="`${idPrefix}-suffix`"
                    :value="form.suffix"
                    type="text"
                    maxlength="45"
                    placeholder="Jr., Sr., III"
                    class="rbim-input"
                    :disabled="nameLocked"
                    @input="patch('suffix', $event.target.value)"
                >
            </div>
        </div>

        <div class="mt-4 grid gap-4 sm:grid-cols-3">
            <div>
                <label class="rbim-label" :for="`${idPrefix}-relationship`">
                    Relationship to the Household Head<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <select
                    :id="`${idPrefix}-relationship`"
                    :value="form.relationship_to_hh_id"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.relationship_to_hh_id }"
                    :disabled="form.isHead"
                    @change="patch('relationship_to_hh_id', $event.target.value)"
                >
                    <option value="">Select relationship</option>
                    <option
                        v-for="option in relationshipOptions"
                        :key="option.id"
                        :value="option.id"
                    >
                        {{ option.label }}
                    </option>
                </select>
                <p v-if="errors.relationship_to_hh_id" class="rbim-error">{{ errors.relationship_to_hh_id }}</p>
            </div>
            <div>
                <label class="rbim-label" :for="`${idPrefix}-sex`">
                    Sex<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <select
                    :id="`${idPrefix}-sex`"
                    :value="form.sex_id"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.sex_id }"
                    @change="patch('sex_id', $event.target.value)"
                >
                    <option value="">Select sex</option>
                    <option v-for="option in sexes" :key="option.id" :value="option.id">{{ option.label }}</option>
                </select>
                <p v-if="errors.sex_id" class="rbim-error">{{ errors.sex_id }}</p>
            </div>
            <div>
                <label class="rbim-label" :for="`${idPrefix}-age`">Age</label>
                <input
                    :id="`${idPrefix}-age`"
                    :value="ageLabel"
                    type="text"
                    readonly
                    class="rbim-input"
                >
                <p class="rbim-hint">Computed from date of birth.</p>
            </div>
        </div>

        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <BirthDateField
                :model-value="form.date_of_birth"
                :input-id="`${idPrefix}-date_of_birth`"
                :max="maxBirthDate"
                required
                :error="errors.date_of_birth"
                @update:model-value="patch('date_of_birth', $event)"
            />
            <LookupCombobox
                v-model="form.nationality_id"
                v-model:query="form.nationality_name"
                :options="nationalities"
                :input-id="`${idPrefix}-nationality`"
                label="Nationality"
                placeholder="Search or type a nationality"
                required
                :can-create="canCreateNationality"
                :error="errors.nationality_id"
                :hint="canCreateNationality ? 'Choose from the list, or type a new name and press Enter to add it.' : ''"
                @create="createNationality"
            />
            <div>
                <label class="rbim-label" :for="`${idPrefix}-marital`">
                    Marital status<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <select
                    :id="`${idPrefix}-marital`"
                    :value="form.marital_status_id"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.marital_status_id }"
                    @change="patch('marital_status_id', $event.target.value)"
                >
                    <option value="">Select marital status</option>
                    <option v-for="option in maritalStatuses" :key="option.id" :value="option.id">{{ option.label }}</option>
                </select>
                <p v-if="errors.marital_status_id" class="rbim-error">{{ errors.marital_status_id }}</p>
            </div>
            <LookupCombobox
                v-model="form.religion_id"
                v-model:query="form.religion_name"
                :options="religions"
                :input-id="`${idPrefix}-religion`"
                label="Religion"
                placeholder="Search or type a religion"
                required
                :can-create="canCreateReligion"
                :error="errors.religion_id"
                :hint="canCreateReligion ? 'Choose from the list, or type a new name and press Enter to add it.' : ''"
                @create="createReligion"
            />
            <LookupCombobox
                v-model="form.ethnicity_id"
                v-model:query="form.ethnicity_name"
                :options="ethnicities"
                :input-id="`${idPrefix}-ethnicity`"
                label="Ethnicity"
                placeholder="Search or type an ethnicity"
                required
                :can-create="canCreateEthnicity"
                :error="errors.ethnicity_id"
                :hint="canCreateEthnicity ? 'Choose from the list, or type a new name and press Enter to add it.' : ''"
                @create="createEthnicity"
            />
        </div>

        <fieldset class="mt-4">
            <legend class="mb-3 text-sm font-semibold text-slate-700">Place of birth</legend>
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="rbim-label" :for="`${idPrefix}-birth_city`">
                        City / municipality<span class="rbim-required" aria-hidden="true">*</span>
                    </label>
                    <input
                        :id="`${idPrefix}-birth_city`"
                        :value="form.birth_city_municipality"
                        type="text"
                        maxlength="45"
                        class="rbim-input"
                        :class="{ 'rbim-input-error': errors.birth_city_municipality }"
                        @input="patch('birth_city_municipality', $event.target.value)"
                    >
                    <p v-if="errors.birth_city_municipality" class="rbim-error">{{ errors.birth_city_municipality }}</p>
                </div>
                <div>
                    <label class="rbim-label" :for="`${idPrefix}-birth_province`">
                        Province<span class="rbim-required" aria-hidden="true">*</span>
                    </label>
                    <input
                        :id="`${idPrefix}-birth_province`"
                        :value="form.birth_province"
                        type="text"
                        maxlength="45"
                        class="rbim-input"
                        :class="{ 'rbim-input-error': errors.birth_province }"
                        @input="patch('birth_province', $event.target.value)"
                    >
                    <p v-if="errors.birth_province" class="rbim-error">{{ errors.birth_province }}</p>
                </div>
                <div>
                    <label class="rbim-label" :for="`${idPrefix}-birth_country`">
                        Country<span class="rbim-required" aria-hidden="true">*</span>
                    </label>
                    <input
                        :id="`${idPrefix}-birth_country`"
                        :value="form.birth_country"
                        type="text"
                        maxlength="45"
                        class="rbim-input"
                        :class="{ 'rbim-input-error': errors.birth_country }"
                        @input="patch('birth_country', $event.target.value)"
                    >
                    <p v-if="errors.birth_country" class="rbim-error">{{ errors.birth_country }}</p>
                </div>
            </div>
        </fieldset>

        <HouseholdEducationForm
            v-if="showEducation"
            :model-value="form"
            :id-prefix="`${idPrefix}-education`"
            :errors="errors"
            @update:model-value="assignForm"
        />

        <div v-if="!form.isHead" class="mt-4 flex justify-end">
            <button type="button" class="rbim-btn-danger" @click="emit('remove')">
                Remove member
            </button>
        </div>
    </FormSectionCard>
</template>

<script setup>
import { computed } from 'vue';
import BirthDateField from '@/components/BirthDateField.vue';
import FormSectionCard from '@/components/FormSectionCard.vue';
import HouseholdEducationForm from '@/components/HouseholdEducationForm.vue';
import LookupCombobox from '@/components/LookupCombobox.vue';
import { useAuth } from '@/composables/useAuth';
import { extractErrorMessage } from '@/services/http';
import * as lookupService from '@/services/lookupService';
import { ageFromDateOfBirth, dateYearsAgo, todayDate } from '@/utils/format';
import { HOUSEHOLD_HEAD_MIN_AGE } from '@/utils/residentForm';

const HEAD_RELATIONSHIP_ID = 1;

const form = defineModel({ type: Object, required: true });

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    help: {
        type: String,
        default: '',
    },
    idPrefix: {
        type: String,
        required: true,
    },
    nameLocked: {
        type: Boolean,
        default: false,
    },
    sexes: {
        type: Array,
        default: () => [],
    },
    relationships: {
        type: Array,
        default: () => [],
    },
    nationalities: {
        type: Array,
        default: () => [],
    },
    maritalStatuses: {
        type: Array,
        default: () => [],
    },
    religions: {
        type: Array,
        default: () => [],
    },
    ethnicities: {
        type: Array,
        default: () => [],
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits(['remove', 'lookup-created', 'lookup-error']);

const maxBirthDate = computed(() => (
    form.value.isHead ? dateYearsAgo(HOUSEHOLD_HEAD_MIN_AGE) : todayDate()
));
const { hasPermission } = useAuth();

const canCreateNationality = computed(() => hasPermission('nationality.create'));
const canCreateReligion = computed(() => hasPermission('religion.create'));
const canCreateEthnicity = computed(() => hasPermission('ethnicity.create'));

const memberAge = computed(() => ageFromDateOfBirth(form.value.date_of_birth));

const ageLabel = computed(() => (memberAge.value === null ? '' : String(memberAge.value)));

const showEducation = computed(() => memberAge.value !== null && memberAge.value >= 3);

const relationshipOptions = computed(() => {
    if (form.value.isHead) {
        return props.relationships;
    }

    return props.relationships.filter((option) => Number(option.id) !== HEAD_RELATIONSHIP_ID);
});

function patch(field, value) {
    form.value[field] = value;

    if (field !== 'date_of_birth') {
        return;
    }

    const age = ageFromDateOfBirth(value);

    if (age === null || age < 5) {
        form.value.highest_education_attained = '';
    }

    if (age === null || age < 3 || age > 24) {
        form.value.enrollment_status = '';
        form.value.school_level = '';
        form.value.school_location = '';
    }
}

function assignForm(next) {
    Object.assign(form.value, next);
}

async function createNationality(name) {
    try {
        const item = await lookupService.createNationality({ nationality: name });
        emit('lookup-created', { kind: 'nationality', item });
        form.value.nationality_id = item.id;
        form.value.nationality_name = item.label;
    } catch (err) {
        emit('lookup-error', {
            field: 'nationality_id',
            message: extractErrorMessage(err, 'Unable to add this nationality.'),
        });
    }
}

async function createReligion(name) {
    try {
        const item = await lookupService.createReligion({ religion: name });
        emit('lookup-created', { kind: 'religion', item });
        form.value.religion_id = item.id;
        form.value.religion_name = item.label;
    } catch (err) {
        emit('lookup-error', {
            field: 'religion_id',
            message: extractErrorMessage(err, 'Unable to add this religion.'),
        });
    }
}

async function createEthnicity(name) {
    try {
        const item = await lookupService.createEthnicity({ ethnicity: name });
        emit('lookup-created', { kind: 'ethnicity', item });
        form.value.ethnicity_id = item.id;
        form.value.ethnicity_name = item.label;
    } catch (err) {
        emit('lookup-error', {
            field: 'ethnicity_id',
            message: extractErrorMessage(err, 'Unable to add this ethnicity.'),
        });
    }
}
</script>

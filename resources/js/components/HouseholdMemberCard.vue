<template>
    <FormSectionCard :title="title" :help="help">
        <div class="grid gap-4 sm:grid-cols-4">
            <div>
                <label class="rbim-label" :for="`${idPrefix}-last_name`">
                    Last Name<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <input
                    :id="`${idPrefix}-last_name`"
                    :value="modelValue.last_name"
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
                    :value="modelValue.first_name"
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
                    :value="modelValue.middle_name"
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
                    :value="modelValue.suffix"
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
                    :value="modelValue.relationship_to_hh_id"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.relationship_to_hh_id }"
                    :disabled="modelValue.isHead"
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
                    :value="modelValue.sex_id"
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
                :model-value="modelValue.date_of_birth"
                :input-id="`${idPrefix}-date_of_birth`"
                :max="maxBirthDate"
                required
                :error="errors.date_of_birth"
                @update:model-value="patch('date_of_birth', $event)"
            />
            <LookupCombobox
                :model-value="modelValue.nationality_id"
                :options="nationalities"
                :input-id="`${idPrefix}-nationality`"
                label="Nationality"
                placeholder="Search nationality"
                required
                :error="errors.nationality_id"
                @update:model-value="patch('nationality_id', $event)"
            />
            <div>
                <label class="rbim-label" :for="`${idPrefix}-marital`">
                    Marital status<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <select
                    :id="`${idPrefix}-marital`"
                    :value="modelValue.marital_status_id"
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
                :model-value="modelValue.religion_id"
                :options="religions"
                :input-id="`${idPrefix}-religion`"
                label="Religion"
                placeholder="Search religion"
                required
                :error="errors.religion_id"
                @update:model-value="patch('religion_id', $event)"
            />
            <LookupCombobox
                :model-value="modelValue.ethnicity_id"
                :options="ethnicities"
                :input-id="`${idPrefix}-ethnicity`"
                label="Ethnicity"
                placeholder="Search ethnicity"
                required
                :error="errors.ethnicity_id"
                @update:model-value="patch('ethnicity_id', $event)"
            />
            <div>
                <label class="rbim-label" :for="`${idPrefix}-resident_type`">
                    Resident type<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <select
                    :id="`${idPrefix}-resident_type`"
                    :value="modelValue.resident_type_id"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.resident_type_id }"
                    @change="patch('resident_type_id', $event.target.value)"
                >
                    <option value="">Select resident type</option>
                    <option v-for="option in residentTypes" :key="option.id" :value="option.id">{{ option.label }}</option>
                </select>
                <p v-if="errors.resident_type_id" class="rbim-error">{{ errors.resident_type_id }}</p>
            </div>
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
                        :value="modelValue.birth_city_municipality"
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
                        :value="modelValue.birth_province"
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
                        :value="modelValue.birth_country"
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
            :model-value="modelValue"
            :id-prefix="`${idPrefix}-education`"
            :errors="errors"
            @update:model-value="emit('update:modelValue', $event)"
        />

        <div v-if="!modelValue.isHead" class="mt-4 flex justify-end">
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
import { ageFromDateOfBirth, todayDate } from '@/utils/format';

const HEAD_RELATIONSHIP_ID = 1;

const props = defineProps({
    modelValue: {
        type: Object,
        required: true,
    },
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
    residentTypes: {
        type: Array,
        default: () => [],
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits(['update:modelValue', 'remove']);

const maxBirthDate = todayDate();

const memberAge = computed(() => ageFromDateOfBirth(props.modelValue.date_of_birth));

const ageLabel = computed(() => (memberAge.value === null ? '' : String(memberAge.value)));

const showEducation = computed(() => memberAge.value !== null && memberAge.value >= 3);

const relationshipOptions = computed(() => {
    if (props.modelValue.isHead) {
        return props.relationships;
    }

    return props.relationships.filter((option) => Number(option.id) !== HEAD_RELATIONSHIP_ID);
});

function patch(field, value) {
    const next = {
        ...props.modelValue,
        [field]: value,
    };

    if (field === 'date_of_birth') {
        const age = ageFromDateOfBirth(value);

        if (age === null || age < 5) {
            next.highest_education_attained = '';
        }

        if (age === null || age < 3 || age > 24) {
            next.enrollment_status = '';
            next.school_level = '';
            next.school_location = '';
        }
    }

    emit('update:modelValue', next);
}
</script>

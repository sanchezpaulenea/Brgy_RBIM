<template>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label :for="`${idPrefix}-last_name`" class="rbim-label">
                Last Name<span class="rbim-required" aria-hidden="true">*</span>
            </label>
            <input
                :id="`${idPrefix}-last_name`"
                :value="modelValue.last_name"
                type="text"
                maxlength="45"
                class="rbim-input"
                :class="{ 'rbim-input-error': errors.last_name }"
                @input="onNameInput('last_name', $event.target.value, 'Last Name', true)"
                @blur="emit('validate-name', 'last_name', 'Last Name', true)"
            >
            <p v-if="errors.last_name" class="rbim-error">{{ errors.last_name }}</p>
        </div>
        <div>
            <label :for="`${idPrefix}-first_name`" class="rbim-label">
                First Name<span class="rbim-required" aria-hidden="true">*</span>
            </label>
            <input
                :id="`${idPrefix}-first_name`"
                :value="modelValue.first_name"
                type="text"
                maxlength="45"
                class="rbim-input"
                :class="{ 'rbim-input-error': errors.first_name }"
                @input="onNameInput('first_name', $event.target.value, 'First Name', true)"
                @blur="emit('validate-name', 'first_name', 'First Name', true)"
            >
            <p v-if="errors.first_name" class="rbim-error">{{ errors.first_name }}</p>
        </div>
        <div>
            <label :for="`${idPrefix}-middle_name`" class="rbim-label">Middle Name</label>
            <input
                :id="`${idPrefix}-middle_name`"
                :value="modelValue.middle_name"
                type="text"
                maxlength="45"
                class="rbim-input"
                :class="{ 'rbim-input-error': errors.middle_name }"
                @input="onNameInput('middle_name', $event.target.value, 'Middle Name', false)"
                @blur="emit('validate-name', 'middle_name', 'Middle Name', false)"
            >
            <p v-if="errors.middle_name" class="rbim-error">{{ errors.middle_name }}</p>
        </div>
        <div>
            <label :for="`${idPrefix}-suffix`" class="rbim-label">Suffix</label>
            <input
                :id="`${idPrefix}-suffix`"
                :value="modelValue.suffix"
                type="text"
                maxlength="45"
                class="rbim-input"
                :class="{ 'rbim-input-error': errors.suffix }"
                @input="onNameInput('suffix', $event.target.value, 'Suffix', false)"
                @blur="emit('validate-name', 'suffix', 'Suffix', false)"
            >
            <p v-if="errors.suffix" class="rbim-error">{{ errors.suffix }}</p>
        </div>
        <div>
            <label :for="`${idPrefix}-relationship`" class="rbim-label">
                Relationship to Household Head<span class="rbim-required" aria-hidden="true">*</span>
            </label>
            <select
                :id="`${idPrefix}-relationship`"
                :value="modelValue.relationship_to_hh_id"
                class="rbim-input"
                :class="{ 'rbim-input-error': errors.relationship_to_hh_id }"
                :disabled="relationshipLocked"
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
            <label :for="`${idPrefix}-sex`" class="rbim-label">
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
                <option v-for="option in sexes" :key="option.id" :value="option.id">
                    {{ option.label }}
                </option>
            </select>
            <p v-if="errors.sex_id" class="rbim-error">{{ errors.sex_id }}</p>
        </div>
        <BirthDateField
            :model-value="modelValue.date_of_birth"
            :input-id="`${idPrefix}-date_of_birth`"
            label="Date of Birth"
            :max="maxBirthDate"
            required
            :error="errors.date_of_birth"
            @update:model-value="patch('date_of_birth', $event)"
        />
        <div>
            <label :for="`${idPrefix}-birth_city`" class="rbim-label">
                Birth City/Municipality<span class="rbim-required" aria-hidden="true">*</span>
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
            <label :for="`${idPrefix}-birth_province`" class="rbim-label">
                Birth Province<span class="rbim-required" aria-hidden="true">*</span>
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
            <label :for="`${idPrefix}-birth_country`" class="rbim-label">
                Birth Country<span class="rbim-required" aria-hidden="true">*</span>
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
        <div>
            <label :for="`${idPrefix}-nationality`" class="rbim-label">
                Nationality<span class="rbim-required" aria-hidden="true">*</span>
            </label>
            <select
                :id="`${idPrefix}-nationality`"
                :value="modelValue.nationality_id"
                class="rbim-input"
                :class="{ 'rbim-input-error': errors.nationality_id }"
                @change="patch('nationality_id', $event.target.value)"
            >
                <option value="">Select nationality</option>
                <option v-for="option in nationalities" :key="option.id" :value="option.id">
                    {{ option.label }}
                </option>
            </select>
            <p v-if="errors.nationality_id" class="rbim-error">{{ errors.nationality_id }}</p>
        </div>
        <div>
            <label :for="`${idPrefix}-religion`" class="rbim-label">
                Religion<span class="rbim-required" aria-hidden="true">*</span>
            </label>
            <select
                :id="`${idPrefix}-religion`"
                :value="modelValue.religion_id"
                class="rbim-input"
                :class="{ 'rbim-input-error': errors.religion_id }"
                @change="patch('religion_id', $event.target.value)"
            >
                <option value="">Select religion</option>
                <option v-for="option in religions" :key="option.id" :value="option.id">
                    {{ option.label }}
                </option>
            </select>
            <p v-if="errors.religion_id" class="rbim-error">{{ errors.religion_id }}</p>
        </div>
        <div>
            <label :for="`${idPrefix}-ethnicity`" class="rbim-label">
                Ethnicity<span class="rbim-required" aria-hidden="true">*</span>
            </label>
            <select
                :id="`${idPrefix}-ethnicity`"
                :value="modelValue.ethnicity_id"
                class="rbim-input"
                :class="{ 'rbim-input-error': errors.ethnicity_id }"
                @change="patch('ethnicity_id', $event.target.value)"
            >
                <option value="">Select ethnicity</option>
                <option v-for="option in ethnicities" :key="option.id" :value="option.id">
                    {{ option.label }}
                </option>
            </select>
            <p v-if="errors.ethnicity_id" class="rbim-error">{{ errors.ethnicity_id }}</p>
        </div>
        <div>
            <label :for="`${idPrefix}-marital_status`" class="rbim-label">
                Marital Status<span class="rbim-required" aria-hidden="true">*</span>
            </label>
            <select
                :id="`${idPrefix}-marital_status`"
                :value="modelValue.marital_status_id"
                class="rbim-input"
                :class="{ 'rbim-input-error': errors.marital_status_id }"
                @change="patch('marital_status_id', $event.target.value)"
            >
                <option value="">Select marital status</option>
                <option v-for="option in maritalStatuses" :key="option.id" :value="option.id">
                    {{ option.label }}
                </option>
            </select>
            <p v-if="errors.marital_status_id" class="rbim-error">{{ errors.marital_status_id }}</p>
        </div>
        <div>
            <label :for="`${idPrefix}-resident_type`" class="rbim-label">
                Resident Type<span class="rbim-required" aria-hidden="true">*</span>
            </label>
            <select
                :id="`${idPrefix}-resident_type`"
                :value="modelValue.resident_type_id"
                class="rbim-input"
                :class="{ 'rbim-input-error': errors.resident_type_id }"
                @change="patch('resident_type_id', $event.target.value)"
            >
                <option value="">Select resident type</option>
                <option v-for="option in residentTypes" :key="option.id" :value="option.id">
                    {{ option.label }}
                </option>
            </select>
            <p v-if="errors.resident_type_id" class="rbim-error">{{ errors.resident_type_id }}</p>
        </div>
    </div>
</template>

<script setup>
import BirthDateField from '@/components/BirthDateField.vue';
import { todayDate } from '@/utils/format';

const props = defineProps({
    modelValue: {
        type: Object,
        required: true,
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
    sexes: {
        type: Array,
        default: () => [],
    },
    relationships: {
        type: Array,
        default: () => [],
    },
    relationshipOptions: {
        type: Array,
        default: () => [],
    },
    nationalities: {
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
    maritalStatuses: {
        type: Array,
        default: () => [],
    },
    residentTypes: {
        type: Array,
        default: () => [],
    },
    relationshipLocked: {
        type: Boolean,
        default: false,
    },
    idPrefix: {
        type: String,
        default: 'resident',
    },
});

const emit = defineEmits(['update:modelValue', 'validate-name']);
const maxBirthDate = todayDate();

function patch(field, value) {
    emit('update:modelValue', { ...props.modelValue, [field]: value });
}

function onNameInput(field, value, label, required) {
    patch(field, value);
    emit('validate-name', field, label, required);
}
</script>

<template>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label :for="`${idPrefix}-last_name`" class="rbim-label">
                Last Name<span class="rbim-required" aria-hidden="true">*</span>
            </label>
            <input
                :id="`${idPrefix}-last_name`"
                :value="form.last_name"
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
                :value="form.first_name"
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
                :value="form.middle_name"
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
                :value="form.suffix"
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
                :value="form.relationship_to_hh_id"
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
                :value="form.sex_id"
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
            :model-value="form.date_of_birth"
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
            <label :for="`${idPrefix}-birth_province`" class="rbim-label">
                Birth Province<span class="rbim-required" aria-hidden="true">*</span>
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
            <label :for="`${idPrefix}-birth_country`" class="rbim-label">
                Birth Country<span class="rbim-required" aria-hidden="true">*</span>
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
        <LookupCombobox
            v-model="form.nationality_id"
            v-model:query="form.nationality_name"
            :options="nationalities"
            :input-id="`${idPrefix}-nationality`"
            label="Nationality"
            placeholder="Search or type a nationality"
            required
            :can-create="canCreateNationality"
            :limit="lookupLimit"
            :preferred-ids="preferredLookupIds"
            :error="errors.nationality_id"
            :hint="canCreateNationality ? 'Choose from the list, or type a new name and press Enter to add it.' : ''"
            @create="createNationality"
        />
        <LookupCombobox
            v-model="form.religion_id"
            v-model:query="form.religion_name"
            :options="religions"
            :input-id="`${idPrefix}-religion`"
            label="Religion"
            placeholder="Search or type a religion"
            required
            :can-create="canCreateReligion"
            :limit="lookupLimit"
            :preferred-ids="preferredLookupIds"
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
            :limit="lookupLimit"
            :preferred-ids="preferredLookupIds"
            :error="errors.ethnicity_id"
            :hint="canCreateEthnicity ? 'Choose from the list, or type a new name and press Enter to add it.' : ''"
            @create="createEthnicity"
        />
        <div>
            <label :for="`${idPrefix}-marital_status`" class="rbim-label">
                Marital Status<span class="rbim-required" aria-hidden="true">*</span>
            </label>
            <select
                :id="`${idPrefix}-marital_status`"
                :value="form.marital_status_id"
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
                :value="form.resident_type_id"
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
import { computed } from 'vue';
import BirthDateField from '@/components/BirthDateField.vue';
import LookupCombobox from '@/components/LookupCombobox.vue';
import { useAuth } from '@/composables/useAuth';
import { extractErrorMessage } from '@/services/http';
import * as lookupService from '@/services/lookupService';
import { dateYearsAgo, todayDate } from '@/utils/format';

const form = defineModel({ type: Object, required: true });

const props = defineProps({
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
    lookupLimit: {
        type: Number,
        default: 12,
    },
    preferredLookupIds: {
        type: Array,
        default: () => [],
    },
    minAge: {
        type: Number,
        default: 0,
    },
});

const emit = defineEmits(['validate-name', 'lookup-created', 'lookup-error']);
const maxBirthDate = computed(() => (
    props.minAge > 0 ? dateYearsAgo(props.minAge) : todayDate()
));
const { hasPermission } = useAuth();

const canCreateNationality = computed(() => hasPermission('nationality.create'));
const canCreateReligion = computed(() => hasPermission('religion.create'));
const canCreateEthnicity = computed(() => hasPermission('ethnicity.create'));

function patch(field, value) {
    form.value[field] = value;
}

function onNameInput(field, value, label, required) {
    patch(field, value);
    emit('validate-name', field, label, required);
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

<template>
    <div class="grid gap-4 sm:grid-cols-2">
        <LookupCombobox
            v-model="pet.specie_id"
            v-model:query="pet.specie_name"
            :options="lookups.species"
            :input-id="`${idPrefix}-specie`"
            label="Specie"
            placeholder="Search or type a specie"
            required
            :can-create="canCreate"
            :error="errors.specie || errors.specie_id"
            :hint="PET_LOOKUP_HINT"
            @create="(name) => acceptTypedLookup('species', 'specie_id', 'specie_name', name)"
        />
        <LookupCombobox
            v-model="pet.breed_id"
            v-model:query="pet.breed_name"
            :options="lookups.breeds"
            :input-id="`${idPrefix}-breed`"
            label="Breed"
            placeholder="Search or type a breed"
            required
            :can-create="canCreate"
            :error="errors.breed || errors.breed_id"
            :hint="PET_LOOKUP_HINT"
            @create="(name) => acceptTypedLookup('breeds', 'breed_id', 'breed_name', name)"
        />
        <div>
            <label :for="`${idPrefix}-sex`" class="rbim-label">
                Sex<span class="rbim-required" aria-hidden="true">*</span>
            </label>
            <select
                :id="`${idPrefix}-sex`"
                v-model="pet.sex_id"
                class="rbim-input"
                :class="{ 'rbim-input-error': errors.sex_id }"
            >
                <option value="">Select</option>
                <option v-for="sex in lookups.sexes" :key="sex.id" :value="sex.id">
                    {{ sex.label }}
                </option>
            </select>
            <p v-if="errors.sex_id" class="rbim-error">{{ errors.sex_id }}</p>
        </div>
        <BirthDateField
            v-model="pet.pet_date_of_birth"
            :input-id="`${idPrefix}-dob`"
            label="Pet date of birth"
            required
            :show-age="false"
            :error="errors.pet_date_of_birth"
        />
        <div>
            <label :for="`${idPrefix}-spay`" class="rbim-label">
                Is spay/neuter<span class="rbim-required" aria-hidden="true">*</span>
            </label>
            <select
                :id="`${idPrefix}-spay`"
                v-model="pet.is_spay_neuter"
                class="rbim-input"
                :class="{ 'rbim-input-error': errors.is_spay_neuter }"
            >
                <option value="">Select</option>
                <option value="true">Yes</option>
                <option value="false">No</option>
            </select>
            <p v-if="errors.is_spay_neuter" class="rbim-error">{{ errors.is_spay_neuter }}</p>
        </div>
        <div>
            <BirthDateField
                v-model="pet.rabies_vaccination_date"
                :input-id="`${idPrefix}-rabies`"
                label="Rabies vaccination date"
                :show-age="false"
                :error="errors.rabies_vaccination_date"
            />
            <p class="rbim-hint">{{ PET_RABIES_HINT }}</p>
        </div>
        <div v-if="showStatus">
            <label :for="`${idPrefix}-status`" class="rbim-label">
                Pet Status<span class="rbim-required" aria-hidden="true">*</span>
            </label>
            <select
                :id="`${idPrefix}-status`"
                v-model="pet.pet_status_id"
                class="rbim-input"
                :class="{ 'rbim-input-error': errors.pet_status_id }"
            >
                <option value="">Select</option>
                <option v-for="status in lookups.petStatuses" :key="status.id" :value="status.id">
                    {{ status.label }}
                </option>
            </select>
            <p v-if="errors.pet_status_id" class="rbim-error">{{ errors.pet_status_id }}</p>
        </div>
    </div>
</template>

<script setup>
import BirthDateField from '@/components/BirthDateField.vue';
import LookupCombobox from '@/components/LookupCombobox.vue';
import { PET_LOOKUP_HINT, PET_RABIES_HINT } from '@/utils/petCensus';

const props = defineProps({
    pet: { type: Object, required: true },
    errors: { type: Object, required: true },
    lookups: { type: Object, required: true },
    idPrefix: { type: String, required: true },
    canCreate: { type: Boolean, default: false },
    showStatus: { type: Boolean, default: false },
});

function acceptTypedLookup(listKey, idField, nameField, name) {
    const trimmed = String(name ?? '').trim();
    const existing = (props.lookups[listKey] ?? []).find((option) => (
        String(option.label ?? '').toLowerCase() === trimmed.toLowerCase()
    ));

    if (existing) {
        props.pet[idField] = existing.id;
        props.pet[nameField] = existing.label;
    } else {
        props.pet[idField] = null;
        props.pet[nameField] = trimmed;
    }

    delete props.errors[idField];
    delete props.errors[nameField];
}
</script>

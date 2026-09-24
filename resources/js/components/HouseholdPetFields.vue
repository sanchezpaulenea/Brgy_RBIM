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
            :error="errors.specie_id"
            :hint="PET_LOOKUP_HINT"
            @create="(name) => createLookup('species', () => lookupService.createSpecie({ specie: name }), 'specie_id', 'specie_name')"
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
            :error="errors.breed_id"
            :hint="PET_LOOKUP_HINT"
            @create="(name) => createLookup('breeds', () => lookupService.createBreed({ breed: name }), 'breed_id', 'breed_name')"
        />
        <LookupCombobox
            v-model="pet.sex_id"
            v-model:query="pet.sex_name"
            :options="lookups.sexes"
            :input-id="`${idPrefix}-sex`"
            label="Sex"
            placeholder="Search or type a sex"
            required
            :can-create="canCreate"
            :max-length="10"
            :error="errors.sex_id"
            :hint="PET_LOOKUP_HINT"
            @create="(name) => createLookup('sexes', () => lookupService.createSex({ sex: name }), 'sex_id', 'sex_name')"
        />
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
        <BirthDateField
            v-model="pet.rabies_vaccination_date"
            :input-id="`${idPrefix}-rabies`"
            label="Rabies vaccination date"
            :show-age="false"
            :error="errors.rabies_vaccination_date"
        />
    </div>
</template>

<script setup>
import BirthDateField from '@/components/BirthDateField.vue';
import LookupCombobox from '@/components/LookupCombobox.vue';
import { extractErrorMessage } from '@/services/http';
import * as lookupService from '@/services/lookupService';
import { PET_LOOKUP_HINT } from '@/utils/petCensus';

const props = defineProps({
    pet: { type: Object, required: true },
    errors: { type: Object, required: true },
    lookups: { type: Object, required: true },
    idPrefix: { type: String, required: true },
    canCreate: { type: Boolean, default: false },
});

async function createLookup(listKey, createFn, idField, nameField) {
    try {
        const item = await createFn();

        if (!props.lookups[listKey].some((option) => Number(option.id) === Number(item.id))) {
            props.lookups[listKey].push(item);
        }

        props.pet[idField] = item.id;
        props.pet[nameField] = item.label;
        delete props.errors[idField];
    } catch (err) {
        props.errors[idField] = extractErrorMessage(err, 'Unable to add this value.');
    }
}
</script>

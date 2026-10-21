<template>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label :for="`${idPrefix}-specie`" class="rbim-label">
                Specie<span class="rbim-required" aria-hidden="true">*</span>
            </label>
            <select
                :id="`${idPrefix}-specie`"
                :value="pet.specie_name"
                class="rbim-input"
                :class="{ 'rbim-input-error': errors.specie || errors.specie_id }"
                @change="onSpecieSelected($event.target.value)"
            >
                <option value="">Select specie</option>
                <option v-for="option in allowedSpecies" :key="option.label" :value="option.label">
                    {{ option.label }}
                </option>
            </select>
            <p v-if="errors.specie || errors.specie_id" class="rbim-error">{{ errors.specie || errors.specie_id }}</p>
        </div>
        <LookupCombobox
            v-model="pet.breed_id"
            v-model:query="pet.breed_name"
            :options="breedOptions"
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
import { computed } from 'vue';
import BirthDateField from '@/components/BirthDateField.vue';
import LookupCombobox from '@/components/LookupCombobox.vue';
import { allowedSpeciesOptions, breedsForSpecie, PET_LOOKUP_HINT, PET_RABIES_HINT } from '@/utils/petCensus';

const props = defineProps({
    pet: { type: Object, required: true },
    errors: { type: Object, required: true },
    lookups: { type: Object, required: true },
    idPrefix: { type: String, required: true },
    canCreate: { type: Boolean, default: false },
    showStatus: { type: Boolean, default: false },
});

const allowedSpecies = computed(() => allowedSpeciesOptions(props.lookups.species));

const selectedSpecieId = computed(() => (
    allowedSpecies.value.find((item) => item.label === props.pet.specie_name)?.id ?? props.pet.specie_id ?? null
));

const breedOptions = computed(() => breedsForSpecie(props.lookups.breeds, selectedSpecieId.value));

function onSpecieSelected(name) {
    const option = allowedSpecies.value.find((item) => item.label === name);

    props.pet.specie_name = name;
    props.pet.specie_id = option?.id ?? null;
    delete props.errors.specie;
    delete props.errors.specie_id;

    const chosenBreed = (props.lookups.breeds ?? []).find((breed) => breed.id === props.pet.breed_id);
    if (chosenBreed && !breedOptions.value.includes(chosenBreed)) {
        props.pet.breed_id = null;
        props.pet.breed_name = '';
    }
}

function acceptTypedLookup(listKey, idField, nameField, name) {
    const trimmed = String(name ?? '').trim();
    const pool = listKey === 'breeds' ? breedOptions.value : (props.lookups[listKey] ?? []);
    const existing = pool.find((option) => (
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

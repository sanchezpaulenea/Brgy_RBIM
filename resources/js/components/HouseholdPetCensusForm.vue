<template>
    <article v-if="mode === 'register'" class="rbim-card p-6">
        <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">
            Pet census
        </h2>
        <p class="mt-1 text-xs text-slate-500">
            Household is filled in from this registration.
            Fields marked with <span class="rbim-required">*</span> are required when the household has pets.
        </p>

        <div v-if="error" class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ error }}
        </div>

        <form class="mt-4 space-y-6" novalidate @submit.prevent="onContinue">
            <div>
                <label for="has_pets" class="rbim-label">
                    Does the household have any pet/s?<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <select id="has_pets" v-model="hasPets" class="rbim-input" :class="{ 'rbim-input-error': hasPetsError }">
                    <option value="">Select</option>
                    <option value="true">Yes</option>
                    <option value="false">No</option>
                </select>
                <p v-if="hasPetsError" class="rbim-error">{{ hasPetsError }}</p>
            </div>

            <template v-if="hasPets === 'true'">
                <div class="max-w-xs">
                    <label for="pet_count" class="rbim-label">
                        Number of pets<span class="rbim-required" aria-hidden="true">*</span>
                    </label>
                    <input
                        id="pet_count"
                        v-model.number="petCount"
                        type="number"
                        min="1"
                        max="30"
                        step="1"
                        class="rbim-input"
                        :class="{ 'rbim-input-error': petCountError }"
                        @change="resizePets"
                    >
                    <p v-if="petCountError" class="rbim-error">{{ petCountError }}</p>
                </div>

                <section
                    v-for="(pet, index) in pets"
                    :key="index"
                    class="space-y-4 rounded-lg border border-slate-200 p-4"
                >
                    <h3 class="text-sm font-semibold text-slate-900">Pet {{ index + 1 }}</h3>
                    <HouseholdPetFields
                        :pet="pet"
                        :errors="petErrors[index]"
                        :lookups="lookups"
                        :id-prefix="`register-pet-${index}`"
                        :can-create="canCreate"
                    />
                </section>
            </template>

            <div class="flex justify-end">
                <button type="submit" class="rbim-btn" :disabled="saving">
                    {{ saving ? 'Saving...' : 'Continue' }}
                </button>
            </div>
        </form>
    </article>

    <section v-else class="space-y-4">
        <h3 class="text-sm font-semibold text-slate-900">Pet Census</h3>
        <p v-if="formError" class="text-sm text-red-700">{{ formError }}</p>
        <div class="max-w-xs">
            <label class="rbim-label" for="detail_pet_total">Number of pets</label>
            <input
                id="detail_pet_total"
                :value="pets.length"
                type="text"
                readonly
                class="rbim-input max-w-24"
            >
            <p class="rbim-hint">Updates when pet rows are added or removed below.</p>
        </div>
        <section
            v-for="(pet, index) in pets"
            :key="pet.pet_census_id || `new-${index}`"
            class="space-y-4 rounded-lg border border-slate-200 p-4"
        >
            <div class="flex items-center justify-between gap-2">
                <h4 class="text-sm font-semibold text-slate-900">Pet {{ index + 1 }}</h4>
                <button
                    v-if="!pet.pet_census_id"
                    type="button"
                    class="text-sm font-medium text-red-700 hover:underline"
                    @click="removeUnsavedPet(index)"
                >
                    Remove
                </button>
            </div>
            <HouseholdPetFields
                :pet="pet"
                :errors="petErrors[index]"
                :lookups="lookups"
                :id-prefix="`detail-pet-${index}`"
                :can-create="canCreate"
            />
        </section>
        <button type="button" class="rbim-btn-outline" :disabled="pets.length >= 30" @click="addPet">
            Add pet
        </button>
    </section>
</template>

<script setup>
import { onMounted, reactive, ref, watch } from 'vue';
import HouseholdPetFields from '@/components/HouseholdPetFields.vue';
import { extractErrorMessage, extractValidationErrors } from '@/services/http';
import * as householdService from '@/services/householdService';
import { applyValidationErrors } from '@/utils/residentForm';
import {
    emptyPetForm,
    emptyPetLookups,
    fetchPetLookups,
    petPayload,
    validatePetForm,
} from '@/utils/petCensus';

const props = defineProps({
    mode: {
        type: String,
        default: 'register',
    },
    householdId: {
        type: [Number, String],
        default: null,
    },
    existingPets: {
        type: Array,
        default: () => [],
    },
    canCreate: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['finished']);

const lookups = reactive(emptyPetLookups());
const hasPets = ref('');
const hasPetsError = ref('');
const petCount = ref(1);
const petCountError = ref('');
const pets = ref([]);
const petErrors = ref([{}]);
const error = ref('');
const formError = ref('');
const saving = ref(false);
const savedCount = ref(0);

function blankErrors(count) {
    return Array.from({ length: count }, () => ({}));
}

function resizePets() {
    let count = Number(petCount.value);

    if (!Number.isInteger(count) || count < 1) {
        petCountError.value = 'Enter a number of pets from 1 to 30.';
        return;
    }

    if (props.mode === 'edit') {
        count = Math.max(count, savedCount.value);
    }

    count = Math.min(count, 30);
    const next = pets.value.slice(0, count);

    while (next.length < count) {
        next.push(emptyPetForm());
    }

    pets.value = next;
    petErrors.value = blankErrors(next.length);
    petCount.value = next.length;
    petCountError.value = '';
}

function loadExisting(records) {
    const items = records ?? [];
    savedCount.value = items.length;
    hasPets.value = items.length ? 'true' : 'false';
    pets.value = items.map((record) => emptyPetForm(record));
    petCount.value = Math.max(items.length, 1);
    petErrors.value = blankErrors(pets.value.length);
}

function addPet() {
    if (pets.value.length >= 30) {
        return;
    }

    hasPets.value = 'true';
    pets.value.push(emptyPetForm());
    petErrors.value.push({});
}

function removeUnsavedPet(index) {
    if (pets.value[index]?.pet_census_id) {
        return;
    }

    pets.value.splice(index, 1);
    petErrors.value.splice(index, 1);
    hasPets.value = pets.value.length ? 'true' : 'false';
}

function validateAll() {
    hasPetsError.value = '';
    petCountError.value = '';
    formError.value = '';
    let valid = true;

    if (props.mode === 'register' && hasPets.value !== 'true' && hasPets.value !== 'false') {
        hasPetsError.value = 'Select whether the household has any pet/s.';
        valid = false;
    }

    if (hasPets.value !== 'true') {
        return valid;
    }

    if (props.mode === 'edit') {
        pets.value.forEach((pet, index) => {
            if (!petErrors.value[index]) {
                petErrors.value[index] = {};
            }

            if (!validatePetForm(pet, petErrors.value[index])) {
                valid = false;
            }
        });

        if (!valid) {
            formError.value = 'Complete every pet before saving.';
        }

        return valid;
    }

    resizePets();

    const count = Number(petCount.value);

    if (!Number.isInteger(count) || count < 1 || count > 30 || pets.value.length !== count) {
        petCountError.value = 'Enter a number of pets from 1 to 30.';
        formError.value = petCountError.value;
        return false;
    }

    pets.value.forEach((pet, index) => {
        if (!petErrors.value[index]) {
            petErrors.value[index] = {};
        }

        if (!validatePetForm(pet, petErrors.value[index])) {
            valid = false;
        }
    });

    if (!valid) {
        formError.value = 'Complete every pet before saving.';
    }

    return valid;
}

async function save() {
    if (hasPets.value !== 'true') {
        return [];
    }

    const created = pets.value.filter((pet) => !pet.pet_census_id);
    const existing = pets.value.filter((pet) => pet.pet_census_id);
    const updated = [];

    for (const pet of existing) {
        updated.push(await householdService.updateHouseholdPet(pet.pet_census_id, petPayload(pet)));
    }

    if (created.length) {
        updated.push(...await householdService.createHouseholdPets(props.householdId, created.map(petPayload)));
    }

    return updated;
}

async function onContinue() {
    error.value = '';

    if (!validateAll()) {
        return;
    }

    if (hasPets.value === 'false') {
        emit('finished');
        return;
    }

    saving.value = true;

    try {
        await householdService.createHouseholdPets(props.householdId, pets.value.map(petPayload));
        emit('finished');
    } catch (err) {
        const validationErrors = extractValidationErrors(err);

        if (Object.keys(validationErrors).length) {
            pets.value.forEach((_, index) => {
                applyValidationErrors(petErrors.value[index], validationErrors, `pets.${index}`);
            });
        } else {
            error.value = extractErrorMessage(err, 'Unable to save pet census.');
        }
    } finally {
        saving.value = false;
    }
}

watch(() => props.existingPets, (records) => {
    if (props.mode === 'edit') {
        loadExisting(records);
    }
});

onMounted(async () => {
    try {
        Object.assign(lookups, await fetchPetLookups());
    } catch (err) {
        const message = extractErrorMessage(err, 'Unable to load pet lookups.');
        error.value = message;
        formError.value = message;
    }

    if (props.mode === 'register') {
        pets.value = [emptyPetForm()];
        petErrors.value = [{}];
    } else {
        loadExisting(props.existingPets);
    }
});

defineExpose({ validateAll, save });
</script>

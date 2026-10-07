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
                        :max="SEQUENTIAL_RESIDENT_MAX"
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

    <article v-else-if="mode === 'add'" class="rbim-card p-6">
        <h2 class="text-sm font-semibold text-slate-900">Add Pet</h2>
        <p class="mt-1 text-xs text-slate-500">
            Fields marked with <span class="rbim-required">*</span> are required. This adds pets to an existing household.
        </p>

        <div v-if="error" class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ error }}
        </div>

        <form v-if="step === 'count'" class="mt-4 max-w-xl space-y-4" novalidate @submit.prevent="confirmCount">
            <HouseholdSearch
                v-model="selectedHouseholdId"
                :options="households"
                required
                input-id="add-pet-household"
                placeholder="Search household head, household ID, street, or house/lot number"
                hint="Search by household head, household ID, street, or house/lot number."
                :error="householdError"
            />
            <div class="max-w-md">
                <label for="add_pet_count" class="rbim-label">
                    How many pets would you like to add?<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <input
                    id="add_pet_count"
                    v-model="petCount"
                    type="number"
                    min="1"
                    :max="SEQUENTIAL_RESIDENT_MAX"
                    step="1"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': petCountError }"
                >
                <p v-if="petCountError" class="rbim-error">{{ petCountError }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button type="submit" class="rbim-btn">Continue</button>
                <button type="button" class="rbim-btn-outline" @click="emit('cancel')">Cancel</button>
            </div>
        </form>

        <form v-else class="mt-4 space-y-6" novalidate @submit.prevent="submitAdd">
            <div class="max-w-xl">
                <label class="rbim-label" for="add-pet-household-locked">
                    Household<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <input
                    id="add-pet-household-locked"
                    type="text"
                    class="rbim-input"
                    :value="selectedHouseholdLabel"
                    disabled
                >
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
                    :id-prefix="`add-pet-${index}`"
                    can-create
                />
            </section>

            <div class="flex flex-wrap justify-end gap-2">
                <button type="button" class="rbim-btn-outline" :disabled="saving" @click="step = 'count'">
                    Back
                </button>
                <button type="button" class="rbim-btn-outline" :disabled="saving" @click="emit('cancel')">
                    Cancel
                </button>
                <button type="submit" class="rbim-btn" :disabled="saving">
                    {{ saving ? 'Saving...' : 'Save' }}
                </button>
            </div>
        </form>
    </article>

    <section v-else class="space-y-4">
        <h3 class="text-sm font-semibold text-slate-900">Pet Census</h3>
        <p v-if="formError" class="text-sm text-red-700">{{ formError }}</p>
        <p v-if="!pets.length" class="text-sm text-slate-500">
            No pets recorded for this household. Add pets from the Pet Census tab.
        </p>
        <section
            v-for="(pet, index) in pets"
            :key="pet.pet_census_id || `pet-${index}`"
            class="space-y-4 rounded-lg border border-slate-200 p-4"
        >
            <h4 class="text-sm font-semibold text-slate-900">Pet {{ index + 1 }}</h4>
            <HouseholdPetFields
                :pet="pet"
                :errors="petErrors[index]"
                :lookups="lookups"
                :id-prefix="`detail-pet-${index}`"
                :can-create="canCreate"
                show-status
            />
        </section>
    </section>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import HouseholdPetFields from '@/components/HouseholdPetFields.vue';
import HouseholdSearch from '@/components/HouseholdSearch.vue';
import { SEQUENTIAL_RESIDENT_MAX, parseResidentCount } from '@/composables/useSequentialResidentRegistration';
import { extractErrorMessage, extractValidationErrors } from '@/services/http';
import * as householdService from '@/services/householdService';
import { householdDisplayLabel } from '@/utils/format';
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

const emit = defineEmits(['finished', 'cancel']);

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
const step = ref('count');
const households = ref([]);
const selectedHouseholdId = ref(null);
const householdError = ref('');

const selectedHousehold = computed(() => (
    households.value.find((household) => Number(household.household_id) === Number(selectedHouseholdId.value)) ?? null
));

const selectedHouseholdLabel = computed(() => (
    selectedHousehold.value ? householdDisplayLabel(selectedHousehold.value) : ''
));

function blankErrors(count) {
    return Array.from({ length: count }, () => ({}));
}

function resizePets() {
    const parsed = parseResidentCount(petCount.value, { noun: 'pets' });

    if (parsed.error) {
        petCountError.value = parsed.error;
        return false;
    }

    const count = parsed.count;
    const next = pets.value.slice(0, count);

    while (next.length < count) {
        next.push(emptyPetForm());
    }

    pets.value = next;
    petErrors.value = blankErrors(next.length);
    petCount.value = next.length;
    petCountError.value = '';

    return true;
}

function loadExisting(records) {
    const items = (records ?? []).filter((record) => record?.pet_census_id);
    hasPets.value = items.length ? 'true' : 'false';
    pets.value = items.map((record) => emptyPetForm(record));
    petErrors.value = blankErrors(pets.value.length);
}

function validatePetRows(requireStatus) {
    let valid = true;

    pets.value.forEach((pet, index) => {
        if (!petErrors.value[index]) {
            petErrors.value[index] = {};
        }

        if (!validatePetForm(pet, petErrors.value[index], { requireStatus })) {
            valid = false;
        }
    });

    return valid;
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

    if (props.mode === 'edit') {
        if (!validatePetRows(true)) {
            formError.value = 'Complete every pet before saving.';
            valid = false;
        }

        return valid;
    }

    if (hasPets.value !== 'true') {
        return valid;
    }

    if (!resizePets()) {
        formError.value = petCountError.value;
        return false;
    }

    if (!validatePetRows(false)) {
        formError.value = 'Complete every pet before saving.';
        valid = false;
    }

    return valid;
}

function confirmCount() {
    error.value = '';
    householdError.value = '';

    if (!selectedHouseholdId.value) {
        householdError.value = 'Household is required.';
    }

    const sized = resizePets();

    if (!selectedHouseholdId.value || !sized) {
        return;
    }

    step.value = 'pets';
}

async function save() {
    const existing = pets.value.filter((pet) => pet.pet_census_id);

    if (existing.length !== pets.value.length) {
        formError.value = 'Add pets from the Pet Census tab.';
        return [];
    }

    const updated = [];

    for (const pet of existing) {
        updated.push(await householdService.updateHouseholdPet(pet.pet_census_id, petPayload(pet)));
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
        applyPetErrors(err, 'Unable to save pet census.');
    } finally {
        saving.value = false;
    }
}

async function submitAdd() {
    error.value = '';

    if (!selectedHouseholdId.value) {
        householdError.value = 'Household is required.';
        step.value = 'count';
        return;
    }

    if (!validatePetRows(false)) {
        error.value = 'Complete every pet before saving.';
        return;
    }

    saving.value = true;

    try {
        await householdService.createHouseholdPets(selectedHouseholdId.value, pets.value.map(petPayload));
        emit('finished');
    } catch (err) {
        applyPetErrors(err, 'Unable to add these pets.');
    } finally {
        saving.value = false;
    }
}

function applyPetErrors(err, fallback) {
    const validationErrors = extractValidationErrors(err);

    if (Object.keys(validationErrors).length) {
        pets.value.forEach((_, index) => {
            applyValidationErrors(petErrors.value[index], validationErrors, `pets.${index}`);
        });
        error.value = fallback;
        return;
    }

    error.value = extractErrorMessage(err, fallback);
}

watch(() => props.existingPets, (records) => {
    if (props.mode === 'edit') {
        loadExisting(records);
    }
});

onMounted(async () => {
    try {
        const [petLookups, householdItems] = await Promise.all([
            fetchPetLookups(),
            props.mode === 'add' ? householdService.fetchHouseholds() : Promise.resolve([]),
        ]);
        Object.assign(lookups, petLookups);
        households.value = householdItems;
    } catch (err) {
        const message = extractErrorMessage(err, 'Unable to load pet lookups.');
        error.value = message;
        formError.value = message;
    }

    if (props.mode === 'register') {
        pets.value = [emptyPetForm()];
        petErrors.value = [{}];
    } else if (props.mode === 'add') {
        pets.value = [];
        petErrors.value = [];
    } else {
        loadExisting(props.existingPets);
    }
});

defineExpose({ validateAll, save });
</script>

<template>
    <article v-if="mode === 'register' && !embedded" class="rbim-card p-6">
        <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">
            Household Pet
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

    <section v-else-if="mode === 'register'" class="space-y-4">
        <h3 class="text-sm font-semibold text-slate-900">Household Pet</h3>
        <p class="text-xs text-slate-500">
            Fields marked with <span class="rbim-required">*</span> are required when the household has pets.
        </p>
        <p v-if="error || formError" class="text-sm text-red-700">{{ error || formError }}</p>

        <div>
            <label :for="`${idPrefix}-has_pets`" class="rbim-label">
                Does the household have any pet/s?<span class="rbim-required" aria-hidden="true">*</span>
            </label>
            <select
                :id="`${idPrefix}-has_pets`"
                v-model="hasPets"
                class="rbim-input"
                :class="{ 'rbim-input-error': hasPetsError }"
            >
                <option value="">Select</option>
                <option value="true">Yes</option>
                <option value="false">No</option>
            </select>
            <p v-if="hasPetsError" class="rbim-error">{{ hasPetsError }}</p>
        </div>

        <template v-if="hasPets === 'true'">
            <div class="max-w-xs">
                <label :for="`${idPrefix}-pet_count`" class="rbim-label">
                    Number of pets<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <input
                    :id="`${idPrefix}-pet_count`"
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
                <h4 class="text-sm font-semibold text-slate-900">Pet {{ index + 1 }}</h4>
                <HouseholdPetFields
                    :pet="pet"
                    :errors="petErrors[index]"
                    :lookups="lookups"
                    :id-prefix="`${idPrefix}-pet-${index}`"
                    :can-create="canCreate"
                />
            </section>
        </template>
    </section>

    <article v-else-if="mode === 'add'" class="rbim-card p-6">
        <h2 class="text-sm font-semibold text-slate-900">Add Pet</h2>
        <p class="mt-1 text-xs text-slate-500">
            Fields marked with <span class="rbim-required">*</span> are required. This adds pets to an existing household.
        </p>

        <div v-if="error" class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ error }}
        </div>

        <form v-if="step === 'count'" class="mt-4 space-y-4" novalidate @submit.prevent="confirmCount">
            <div class="max-w-xl space-y-4">
                <HouseholdSearch
                    v-model="selectedHouseholdId"
                    :options="households"
                    required
                    input-id="add-pet-household"
                    placeholder="Search household head, household ID, street, or house/lot number"
                    hint="Search by household head, household ID, street, or house/lot number."
                    :error="householdError"
                />
                <div v-if="selectedHouseholdId" class="max-w-md">
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
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" class="rbim-btn-outline" @click="cancelAdd">Cancel</button>
                <button type="submit" class="rbim-btn">Continue</button>
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

            <div class="flex flex-wrap items-center justify-between gap-2">
                <button type="button" class="rbim-btn-outline" :disabled="saving" @click="step = 'count'">
                    Back
                </button>
                <button type="submit" class="rbim-btn" :disabled="saving">
                    {{ saving ? 'Saving...' : 'Save' }}
                </button>
            </div>
        </form>
    </article>

    <section v-else class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h3 class="text-sm font-semibold text-slate-900">Household Pet</h3>
            <button
                v-if="canAdd"
                type="button"
                class="rbim-btn-outline px-3 py-1.5 text-xs"
                :disabled="pets.length >= SEQUENTIAL_RESIDENT_MAX"
                @click="addPet"
            >
                Add Pet
            </button>
        </div>
        <p v-if="!canUpdate" class="text-sm text-slate-500">
            You do not have permission to update existing pet records.
        </p>
        <p v-if="formError" class="text-sm text-red-700">{{ formError }}</p>
        <p v-if="!pets.length" class="text-sm text-slate-500">
            No pets recorded for this household.{{ canAdd ? ' Use Add Pet to record one.' : '' }}
        </p>
        <section
            v-for="(pet, index) in pets"
            :key="pet.pet_census_id || pet.localKey"
            class="space-y-4 rounded-lg border border-slate-200 p-4"
        >
            <div class="flex items-center justify-between gap-2">
                <h4 class="text-sm font-semibold text-slate-900">
                    Pet {{ index + 1 }}
                    <span v-if="!pet.pet_census_id" class="ml-1 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700">New</span>
                </h4>
                <button
                    v-if="!pet.pet_census_id"
                    type="button"
                    class="rbim-btn-outline px-3 py-1.5 text-xs"
                    @click="removePet(index)"
                >
                    Remove
                </button>
            </div>
            <fieldset :disabled="Boolean(pet.pet_census_id) && !canUpdate" class="min-w-0 border-0 p-0">
                <HouseholdPetFields
                    :pet="pet"
                    :errors="petErrors[index]"
                    :lookups="lookups"
                    :id-prefix="`detail-pet-${index}`"
                    :can-create="canCreate"
                    :show-status="Boolean(pet.pet_census_id)"
                />
            </fieldset>
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
    canAdd: {
        type: Boolean,
        default: false,
    },
    canUpdate: {
        type: Boolean,
        default: true,
    },
    embedded: {
        type: Boolean,
        default: false,
    },
    idPrefix: {
        type: String,
        default: 'household-pet',
    },
});

let nextLocalKey = 0;

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

function addPet() {
    if (pets.value.length >= SEQUENTIAL_RESIDENT_MAX) {
        return;
    }

    nextLocalKey += 1;
    pets.value.push({ ...emptyPetForm(), localKey: `new-pet-${nextLocalKey}` });
    petErrors.value.push({});
    formError.value = '';
}

function removePet(index) {
    if (pets.value[index]?.pet_census_id) {
        return;
    }

    pets.value.splice(index, 1);
    petErrors.value.splice(index, 1);
}

/*
 * Existing records carry a status; pets added in edit mode get the default
 * status from the server, exactly as they do on the Household Pet tab.
 */
function validatePetRows(requireStatus) {
    let valid = true;

    pets.value.forEach((pet, index) => {
        if (!petErrors.value[index]) {
            petErrors.value[index] = {};
        }

        if (props.mode === 'edit' && pet.pet_census_id && !props.canUpdate) {
            return;
        }

        const needsStatus = props.mode === 'edit' ? Boolean(pet.pet_census_id) : requireStatus;

        if (!validatePetForm(pet, petErrors.value[index], { requireStatus: needsStatus })) {
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

function resetAdd() {
    step.value = 'count';
    selectedHouseholdId.value = null;
    petCount.value = 1;
    petCountError.value = '';
    householdError.value = '';
    error.value = '';
    pets.value = [];
    petErrors.value = [];
    saving.value = false;
}

function cancelAdd() {
    resetAdd();
    emit('cancel');
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
    formError.value = '';

    if (props.mode === 'register') {
        if (hasPets.value !== 'true') {
            return [];
        }

        return householdService.createHouseholdPets(props.householdId, pets.value.map(petPayload));
    }

    const saved = [];

    if (props.canUpdate) {
        for (const pet of pets.value.filter((item) => item.pet_census_id)) {
            saved.push(await householdService.updateHouseholdPet(pet.pet_census_id, petPayload(pet)));
        }
    }

    const added = pets.value
        .map((pet, index) => ({ pet, index }))
        .filter(({ pet }) => !pet.pet_census_id);

    if (props.canAdd && added.length) {
        try {
            const created = await householdService.createHouseholdPets(
                props.householdId,
                added.map(({ pet }) => petPayload(pet)),
            );
            saved.push(...(Array.isArray(created) ? created : []));
        } catch (err) {
            const validationErrors = extractValidationErrors(err);

            added.forEach(({ index }, position) => {
                applyValidationErrors(petErrors.value[index], validationErrors, `pets.${position}`);
            });
            formError.value = extractErrorMessage(err, 'Unable to add the new pets.');
            throw err;
        }
    }

    return saved;
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
        applyPetErrors(err, 'Unable to save household pets.');
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
        resetAdd();
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

watch(selectedHouseholdId, (householdId) => {
    if (props.mode !== 'add' || step.value !== 'count') {
        return;
    }

    householdError.value = '';

    if (!householdId) {
        petCount.value = 1;
        petCountError.value = '';
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

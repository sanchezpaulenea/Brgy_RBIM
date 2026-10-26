<template>
    <AppLayout title="Household Management">
        <div class="space-y-6">
            <PageTabs :tabs="householdTabs" />

            <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ error }}
            </div>

            <HouseholdPetCensusForm
                v-if="canCreate"
                mode="add"
                @finished="finishAdd"
            />

            <form class="rbim-card grid gap-3 p-4 sm:grid-cols-2 xl:grid-cols-5" @submit.prevent="applyFilters">
                <div>
                    <label for="pet-search" class="rbim-label">Search</label>
                    <input
                        id="pet-search"
                        v-model="filters.search"
                        type="search"
                        name="pet-census-search"
                        autocomplete="off"
                        autocapitalize="off"
                        spellcheck="false"
                        class="rbim-input py-2"
                        placeholder="Search pets or households"
                        @keydown.enter.prevent="applyFilters"
                    >
                </div>
                <div>
                    <label for="pet-status" class="rbim-label">Pet Status</label>
                    <select id="pet-status" v-model="filters.pet_status_id" class="rbim-input py-2">
                        <option value="">All pet statuses</option>
                        <option v-for="status in options.petStatuses" :key="status.id" :value="String(status.id)">
                            {{ status.label }}
                        </option>
                    </select>
                </div>
                <div>
                    <label for="pet-species" class="rbim-label">Species</label>
                    <select id="pet-species" v-model="filters.specie_id" class="rbim-input py-2">
                        <option value="">All species</option>
                        <option v-for="specie in options.species" :key="specie.id" :value="String(specie.id)">
                            {{ specie.label }}
                        </option>
                    </select>
                </div>
                <div>
                    <label for="pet-breed" class="rbim-label">Breed</label>
                    <select id="pet-breed" v-model="filters.breed_id" class="rbim-input py-2">
                        <option value="">All breeds</option>
                        <option v-for="breed in filterBreeds" :key="breed.id" :value="String(breed.id)">
                            {{ breed.label }}
                        </option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="button" class="rbim-btn py-2" :disabled="loading" @click="clearFilters">
                        Refresh
                    </button>
                </div>
            </form>

            <div v-if="loading" class="rbim-card p-8 text-center text-sm text-slate-500">
                Loading household pets...
            </div>
            <div v-else class="rbim-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Pet ID</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Household</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Species</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Breed</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Gender</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Date of Birth</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Age</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Spay/Neuter</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Rabies Vaccination</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Pet Status</th>
                                <th v-if="canUpdate" class="px-4 py-3 text-left font-semibold text-slate-600">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="pet in pets" :key="pet.pet_census_id">
                                <td class="px-4 py-3 font-medium text-slate-900">{{ pet.pet_census_id }}</td>
                                <td class="px-4 py-3">
                                    <RouterLink
                                        v-if="canViewHousehold"
                                        :to="{ name: 'household-detail', params: { id: pet.household_id } }"
                                        class="text-brand hover:underline"
                                    >
                                        {{ pet.household_label }}
                                    </RouterLink>
                                    <span v-else class="text-slate-900">{{ pet.household_label }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ pet.specie || '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ pet.breed || '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ pet.sex || '—' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600">{{ formatDate(pet.pet_date_of_birth) }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ pet.age ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ yesNoLabel(pet.is_spay_neuter) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600">
                                    {{ pet.rabies_vaccination_date ? formatDate(pet.rabies_vaccination_date) : 'Not vaccinated' }}
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ pet.pet_status || '—' }}</td>
                                <td v-if="canUpdate" class="px-4 py-3">
                                    <button type="button" class="rbim-btn-action" @click="startEdit(pet)">
                                        Update
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!pets.length">
                                <td :colspan="canUpdate ? 11 : 10" class="px-4 py-8 text-center text-slate-500">
                                    {{ filtersAreActive ? 'No pets match the filters.' : 'No pets recorded.' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div
            v-if="canUpdate && editing"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4"
            @click.self="cancelEdit"
        >
            <div class="max-h-[90vh] w-full max-w-3xl overflow-hidden rounded-xl bg-white shadow-xl" role="dialog" aria-modal="true">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="text-base font-semibold text-slate-900">Update household pet</h2>
                    <p class="mt-1 text-sm text-slate-600">
                        Fields marked with <span class="rbim-required">*</span> are required.
                    </p>
                </div>
                <form class="max-h-[calc(90vh-5rem)] space-y-4 overflow-y-auto px-5 py-4" novalidate @submit.prevent="saveEdit">
                    <div v-if="formError" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ formError }}
                    </div>
                    <HouseholdPetFields
                        :pet="editForm"
                        :errors="editErrors"
                        :lookups="editLookups"
                        id-prefix="pet-census-edit"
                        can-create
                        show-status
                    />
                    <div class="flex justify-end gap-2">
                        <button type="button" class="rbim-btn-outline" :disabled="saving" @click="cancelEdit">
                            Cancel
                        </button>
                        <button type="submit" class="rbim-btn" :disabled="saving">
                            {{ saving ? 'Saving...' : 'Save' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { RouterLink } from 'vue-router';
import HouseholdPetCensusForm from '@/components/HouseholdPetCensusForm.vue';
import HouseholdPetFields from '@/components/HouseholdPetFields.vue';
import PageTabs from '@/components/PageTabs.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { useAuth } from '@/composables/useAuth';
import { useSectionTabs } from '@/composables/useSectionTabs';
import { extractErrorMessage, extractValidationErrors } from '@/services/http';
import * as householdService from '@/services/householdService';
import { formatDate } from '@/utils/format';
import { breedsForSpecie, emptyPetForm, emptyPetLookups, isAllowedPetSpecies, petPayload, validatePetForm, yesNoLabel } from '@/utils/petCensus';
import { applyValidationErrors } from '@/utils/residentForm';

const { hasPermission } = useAuth();
const { householdTabs } = useSectionTabs();
const canCreate = computed(() => hasPermission('petcensus.create'));
const canUpdate = computed(() => hasPermission('petcensus.update'));
const canViewHousehold = computed(() => hasPermission('household.view'));

const loading = ref(false);
const saving = ref(false);
const error = ref('');
const formError = ref('');
const pets = ref([]);
const editing = ref(null);
const editForm = reactive(emptyPetForm());
const editErrors = reactive({});
const editLookups = reactive(emptyPetLookups());
const options = reactive({
    petStatuses: [],
    species: [],
    breeds: [],
    sexes: [],
});
const filters = reactive({
    search: '',
    pet_status_id: '',
    specie_id: '',
    breed_id: '',
});

const filterBreeds = computed(() => breedsForSpecie(options.breeds, filters.specie_id));

watch(
    () => filters.specie_id,
    () => {
        if (filters.breed_id && !filterBreeds.value.some((breed) => String(breed.id) === filters.breed_id)) {
            filters.breed_id = '';
        }
    },
);

const filtersAreActive = computed(() => (
    Boolean(filters.search || filters.pet_status_id || filters.specie_id || filters.breed_id)
));

function applyOptions(next) {
    options.petStatuses = next?.pet_statuses ?? [];
    options.species = (next?.species ?? []).filter((option) => isAllowedPetSpecies(option.label));
    options.breeds = next?.breeds ?? [];
    options.sexes = next?.sexes ?? [];
    editLookups.species = options.species;
    editLookups.breeds = options.breeds;
    editLookups.sexes = options.sexes;
    editLookups.petStatuses = options.petStatuses;
}

async function loadPets() {
    loading.value = true;
    error.value = '';

    try {
        const data = await householdService.fetchPetCensus({
            search: filters.search.trim(),
            pet_status_id: filters.pet_status_id,
            specie_id: filters.specie_id,
            breed_id: filters.breed_id,
        });
        pets.value = data.items ?? [];
        applyOptions(data.options);
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to load household pets.');
    } finally {
        loading.value = false;
    }
}

function applyFilters() {
    clearTimeout(filterTimer);
    loadPets();
}

function clearFilters() {
    filters.search = '';
    filters.pet_status_id = '';
    filters.specie_id = '';
    filters.breed_id = '';
    applyFilters();
}

async function finishAdd() {
    await loadPets();
}

function startEdit(pet) {
    editing.value = pet;
    formError.value = '';
    Object.assign(editForm, emptyPetForm(pet));
    Object.keys(editErrors).forEach((key) => {
        delete editErrors[key];
    });
}

function cancelEdit() {
    editing.value = null;
    formError.value = '';
}

async function saveEdit() {
    if (!editing.value || !validatePetForm(editForm, editErrors, { requireStatus: true })) {
        return;
    }

    saving.value = true;
    formError.value = '';

    try {
        await householdService.updateHouseholdPet(editing.value.pet_census_id, petPayload(editForm));
        editing.value = null;
        await loadPets();
    } catch (err) {
        const validationErrors = extractValidationErrors(err);
        if (Object.keys(validationErrors).length) {
            applyValidationErrors(editErrors, validationErrors);
        }
        formError.value = extractErrorMessage(err, 'Unable to update this pet.');
    } finally {
        saving.value = false;
    }
}

let filterTimer = null;

watch(
    () => [filters.search, filters.pet_status_id, filters.specie_id, filters.breed_id],
    () => {
        clearTimeout(filterTimer);
        filterTimer = setTimeout(() => {
            loadPets();
        }, 250);
    },
    { immediate: true },
);
</script>

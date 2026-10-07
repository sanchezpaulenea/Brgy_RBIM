<template>
    <AppLayout title="Household Management">
        <div class="space-y-6">
            <PageTabs :tabs="householdTabs" />

            <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ error }}
            </div>

            <div v-if="canCreate && !adding" class="flex justify-end">
                <button type="button" class="rbim-btn" @click="adding = true">
                    Add Pet
                </button>
            </div>

            <HouseholdPetCensusForm
                v-if="adding"
                mode="add"
                @finished="finishAdd"
                @cancel="adding = false"
            />

            <form v-else class="rbim-card grid gap-3 p-4 sm:grid-cols-2 xl:grid-cols-5">
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
                        <option v-for="breed in options.breeds" :key="breed.id" :value="String(breed.id)">
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

            <div v-if="!adding && loading" class="rbim-card p-8 text-center text-sm text-slate-500">
                Loading pet census...
            </div>
            <div v-else-if="!adding" class="rbim-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Pet ID</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Household</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Species</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Breed</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Sex</th>
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

            <article v-if="!adding && canUpdate && editing" class="rbim-card p-6">
                <h2 class="text-sm font-semibold text-slate-900">Update pet {{ editing.pet_census_id }}</h2>
                <p v-if="formError" class="mt-3 text-sm text-red-700">{{ formError }}</p>
                <form class="mt-4 space-y-4" novalidate @submit.prevent="saveEdit">
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
            </article>
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
import { emptyPetForm, emptyPetLookups, petPayload, validatePetForm, yesNoLabel } from '@/utils/petCensus';
import { applyValidationErrors } from '@/utils/residentForm';

const { hasPermission } = useAuth();
const { householdTabs } = useSectionTabs();
const canCreate = computed(() => hasPermission('petcensus.create'));
const canUpdate = computed(() => hasPermission('petcensus.update'));
const canViewHousehold = computed(() => hasPermission('household.view'));

const loading = ref(false);
const saving = ref(false);
const adding = ref(false);
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

const filtersAreActive = computed(() => (
    Boolean(filters.search || filters.pet_status_id || filters.specie_id || filters.breed_id)
));

function applyOptions(next) {
    options.petStatuses = next?.pet_statuses ?? [];
    options.species = next?.species ?? [];
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
        error.value = extractErrorMessage(err, 'Unable to load the pet census.');
    } finally {
        loading.value = false;
    }
}

function clearFilters() {
    filters.search = '';
    filters.pet_status_id = '';
    filters.specie_id = '';
    filters.breed_id = '';
    loadPets();
}

async function finishAdd() {
    adding.value = false;
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

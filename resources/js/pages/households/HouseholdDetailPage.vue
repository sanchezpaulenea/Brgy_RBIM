<template>
    <AppLayout title="Household Management">
        <div class="space-y-6">
            <PageTabs :tabs="householdTabs" active-name="households" />

            <div class="flex flex-wrap items-center justify-between gap-3">
                <RouterLink :to="{ name: 'households' }" class="text-sm font-medium text-brand hover:underline">
                    ← Back to households
                </RouterLink>
                <button
                    v-if="canUpdate && household"
                    type="button"
                    class="rbim-btn-action"
                    @click="startEdit"
                >
                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M13.586 2.586a2 2 0 112.828 2.828l-8.5 8.5a1 1 0 01-.44.253l-3 .857a.5.5 0 01-.618-.618l.857-3a1 1 0 01.253-.44l8.62-8.38z" />
                    </svg>
                    Update
                </button>
            </div>

            <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ error }}
            </div>
            <div v-if="successMessage" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ successMessage }}
            </div>

            <div v-if="loading" class="rbim-card p-8 text-center text-sm text-slate-500">
                Loading household...
            </div>

            <template v-else-if="household">
                <article class="rbim-card p-6">
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">
                        Household {{ household.household_id }}
                    </h2>
                    <dl class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Clan</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ household.clan_name || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Street</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ household.street_name || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">House/Lot Number</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ household.house_lot || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Number of House Story</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ household.number_of_house_story ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Number of Basement Level</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ basementLevelLabel(household) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Household Status</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ household.household_status || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Registration Date</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ formatDateTime(household.registration_date) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Head Resident Name</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ household.head?.full_name || '—' }}</dd>
                        </div>
                    </dl>

                    <h3 class="mt-8 text-sm font-semibold text-slate-900">Household Questions</h3>
                    <p v-if="!household.questions" class="mt-2 text-sm text-slate-500">
                        Household questions have not been encoded yet.
                    </p>
                    <dl v-else class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div v-for="field in questionDisplayFields(household.questions)" :key="field.label">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ field.label }}</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ field.value }}</dd>
                        </div>
                    </dl>

                    <h3 class="mt-8 text-sm font-semibold text-slate-900">Pet Census</h3>
                    <p v-if="!household.pets?.length" class="mt-2 text-sm text-slate-500">
                        No pets recorded for this household.
                    </p>
                    <div v-else class="mt-4 space-y-4">
                        <dl
                            v-for="(pet, index) in household.pets"
                            :key="pet.pet_census_id"
                            class="grid gap-4 rounded-lg border border-slate-200 p-4 sm:grid-cols-2 lg:grid-cols-3"
                        >
                            <div class="sm:col-span-2 lg:col-span-3">
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Pet {{ index + 1 }}</dt>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Specie</dt>
                                <dd class="mt-1 text-sm text-slate-900">{{ pet.specie || '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Breed</dt>
                                <dd class="mt-1 text-sm text-slate-900">{{ pet.breed || '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Sex</dt>
                                <dd class="mt-1 text-sm text-slate-900">{{ pet.sex || '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Pet date of birth</dt>
                                <dd class="mt-1 text-sm text-slate-900">{{ formatDate(pet.pet_date_of_birth) }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Is spay/neuter</dt>
                                <dd class="mt-1 text-sm text-slate-900">{{ yesNoLabel(pet.is_spay_neuter) }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Rabies vaccination date</dt>
                                <dd class="mt-1 text-sm text-slate-900">{{ pet.rabies_vaccination_date ? formatDate(pet.rabies_vaccination_date) : '—' }}</dd>
                            </div>
                        </dl>
                    </div>
                </article>

                <div class="rbim-card overflow-hidden">
                    <div class="border-b border-slate-200 px-4 py-3">
                        <h3 class="text-sm font-semibold text-slate-900">Household members</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Name</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Relationship to Household Head</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Sex</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Age</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="member in household.residents || []" :key="member.resident_id">
                                    <td class="px-4 py-3 font-medium text-slate-900">
                                        <RouterLink
                                            :to="{ name: 'resident-detail', params: { id: member.resident_id } }"
                                            class="text-brand hover:underline"
                                            @click.stop
                                        >
                                            {{ member.full_name }}
                                        </RouterLink>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600">{{ member.relationship_to_hh || '—' }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ member.sex || '—' }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ ageLabel(member.date_of_birth) }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ member.resident_status || '—' }}</td>
                                </tr>
                                <tr v-if="!(household.residents || []).length">
                                    <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                                        No residents belong to this household.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>
        </div>

        <div
            v-if="editing"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4"
            @click.self="cancelEdit"
        >
            <div class="w-full max-w-4xl overflow-hidden rounded-xl bg-white shadow-xl" role="dialog" aria-modal="true">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="text-base font-semibold text-slate-900">Update household</h2>
                    <p class="mt-1 text-sm text-slate-600">
                        Fields marked with <span class="rbim-required">*</span> are required.
                        The household head cannot be changed here.
                    </p>
                </div>
                <form class="max-h-[80vh] space-y-6 overflow-y-auto px-5 py-4" novalidate @submit.prevent="handleUpdate">
                    <section class="space-y-4">
                        <h3 class="text-sm font-semibold text-slate-900">Household Address and Status</h3>
                        <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="detail_street_id" class="rbim-label">
                                Street<span class="rbim-required" aria-hidden="true">*</span>
                            </label>
                            <select
                                id="detail_street_id"
                                v-model="editForm.street_id"
                                class="rbim-input"
                                :class="{ 'rbim-input-error': editErrors.street_id }"
                            >
                                <option value="">Select street</option>
                                <option v-for="street in streets" :key="street.id" :value="street.id">
                                    {{ street.label }}
                                </option>
                            </select>
                            <p v-if="editErrors.street_id" class="rbim-error">{{ editErrors.street_id }}</p>
                        </div>
                        <div>
                            <label for="detail_house_lot" class="rbim-label">House/Lot Number</label>
                            <input
                                id="detail_house_lot"
                                v-model="editForm.house_lot"
                                type="text"
                                maxlength="45"
                                class="rbim-input"
                                :class="{ 'rbim-input-error': editErrors.house_lot }"
                            >
                            <p v-if="editErrors.house_lot" class="rbim-error">{{ editErrors.house_lot }}</p>
                        </div>
                        <div>
                            <label for="detail_number_of_house_story" class="rbim-label">
                                Number of House Story<span class="rbim-required" aria-hidden="true">*</span>
                            </label>
                            <input
                                id="detail_number_of_house_story"
                                v-model="editForm.number_of_house_story"
                                type="number"
                                min="1"
                                max="50"
                                step="1"
                                class="rbim-input"
                                :class="{ 'rbim-input-error': editErrors.number_of_house_story }"
                            >
                            <p v-if="editErrors.number_of_house_story" class="rbim-error">{{ editErrors.number_of_house_story }}</p>
                        </div>
                        <div>
                            <label for="detail_has_basement" class="rbim-label">
                                Does the house have a basement?<span class="rbim-required" aria-hidden="true">*</span>
                            </label>
                            <select
                                id="detail_has_basement"
                                v-model="editForm.has_basement"
                                class="rbim-input"
                                :class="{ 'rbim-input-error': editErrors.has_basement }"
                            >
                                <option value="">Select</option>
                                <option value="true">Yes</option>
                                <option value="false">No</option>
                            </select>
                            <p v-if="editErrors.has_basement" class="rbim-error">{{ editErrors.has_basement }}</p>
                        </div>
                        <div v-if="editForm.has_basement === 'true'">
                            <label for="detail_number_of_basement_level" class="rbim-label">
                                Number of Basement Level<span class="rbim-required" aria-hidden="true">*</span>
                            </label>
                            <input
                                id="detail_number_of_basement_level"
                                v-model="editForm.number_of_basement_level"
                                type="number"
                                min="1"
                                max="20"
                                step="1"
                                class="rbim-input"
                                :class="{ 'rbim-input-error': editErrors.number_of_basement_level }"
                            >
                            <p v-if="editErrors.number_of_basement_level" class="rbim-error">{{ editErrors.number_of_basement_level }}</p>
                        </div>
                        <div>
                            <label for="detail_household_status_id" class="rbim-label">
                                Household Status<span class="rbim-required" aria-hidden="true">*</span>
                            </label>
                            <select
                                id="detail_household_status_id"
                                v-model="editForm.household_status_id"
                                class="rbim-input"
                                :class="{ 'rbim-input-error': editErrors.household_status_id }"
                            >
                                <option value="">Select household status</option>
                                <option v-for="status in householdStatuses" :key="status.id" :value="status.id">
                                    {{ status.label }}
                                </option>
                            </select>
                            <p v-if="editErrors.household_status_id" class="rbim-error">{{ editErrors.household_status_id }}</p>
                        </div>
                        </div>
                    </section>
                    <section class="space-y-4">
                        <h3 class="text-sm font-semibold text-slate-900">Household Questions</h3>
                        <HouseholdQuestionsFields
                            :form="questionForm"
                            :errors="questionErrors"
                            :lookups="questionLookups"
                            id-prefix="detail-questions"
                        />
                    </section>
                    <HouseholdPetCensusForm
                        ref="petForm"
                        mode="edit"
                        :household-id="household.household_id"
                        :existing-pets="household.pets || []"
                        :can-create="canUpdate"
                    />
                    <div class="flex justify-end gap-2">
                        <button type="button" class="rbim-btn-outline" @click="cancelEdit">
                            Cancel
                        </button>
                        <button type="submit" class="rbim-btn" :disabled="saving">
                            {{ saving ? 'Saving...' : 'Update household' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <ConfirmDialog
            :open="confirm.open"
            :title="confirm.title"
            :message="confirm.message"
            :confirm-label="confirm.confirmLabel"
            :variant="confirm.variant"
            @confirm="confirm.onConfirm?.()"
            @cancel="handleConfirmCancel"
        />
    </AppLayout>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import HouseholdPetCensusForm from '@/components/HouseholdPetCensusForm.vue';
import HouseholdQuestionsFields from '@/components/HouseholdQuestionsFields.vue';
import PageTabs from '@/components/PageTabs.vue';
import { useAuth } from '@/composables/useAuth';
import { useSectionTabs } from '@/composables/useSectionTabs';
import { extractErrorMessage, extractValidationErrors } from '@/services/http';
import * as householdService from '@/services/householdService';
import * as lookupService from '@/services/lookupService';
import { ageFromDateOfBirth, formatDate, formatDateTime } from '@/utils/format';
import { applyValidationErrors, householdStructureFromRecord, householdStructurePayload, optionalAddressText, toId, validateHouseholdStructure } from '@/utils/residentForm';
import {
    emptyHouseholdQuestionLookups,
    emptyHouseholdQuestionsForm,
    fetchHouseholdQuestionLookups,
    householdQuestionsFromRecord,
    householdQuestionsPayload,
    validateHouseholdQuestions,
    deathListLabel,
    yesNoLabel,
} from '@/utils/householdQuestions';

const route = useRoute();
const { hasPermission } = useAuth();
const { householdTabs } = useSectionTabs();

const household = ref(null);
const streets = ref([]);
const householdStatuses = ref([]);
const questionLookups = reactive(emptyHouseholdQuestionLookups());
const location = reactive({ barangay: '', city: '', province: '' });
const loading = ref(false);
const saving = ref(false);
const editing = ref(false);
const error = ref('');
const successMessage = ref('');
const editForm = reactive(emptyEditForm());
const editErrors = reactive({});
const questionForm = reactive(emptyHouseholdQuestionsForm());
const petForm = ref(null);
const questionErrors = reactive({});
const confirm = reactive({
    open: false,
    title: '',
    message: '',
    confirmLabel: 'Confirm',
    variant: 'primary',
    onConfirm: null,
    onCancel: null,
});

const canUpdate = computed(() => hasPermission('household.update'));

function emptyEditForm() {
    return {
        street_id: '',
        house_lot: '',
        number_of_house_story: 1,
        has_basement: '',
        number_of_basement_level: '',
        household_status_id: '',
    };
}

function ageLabel(dateOfBirth) {
    const age = ageFromDateOfBirth(dateOfBirth);

    return age === null ? '—' : String(age);
}

function basementLevelLabel(item) {
    const levels = Number(item?.number_of_basement_level ?? 0);

    return levels > 0 ? String(levels) : 'None';
}

function joinList(values) {
    const items = (values ?? []).filter(Boolean);

    return items.length ? items.join(', ') : '—';
}

function questionDisplayFields(questions) {
    return [
        { label: 'Q1 Ownership of Housing Unit', value: questions.ownership_of_housing_unit || '—' },
        { label: 'Q2 Ownership of Lot', value: questions.ownership_of_lot || '—' },
        { label: 'Q3 Fuel for Lighting', value: questions.fuel_type_for_lighting || '—' },
        { label: 'Q4 Fuel for Cooking', value: questions.fuel_type_for_cooking || '—' },
        { label: 'Q5 Main Source of Drinking Water', value: questions.main_source_drinking_water || '—' },
        { label: 'Q6a Kitchen Garbage Disposal', value: questions.kitchen_garbage_disposal || '—' },
        { label: 'Q6b Segregate Garbage', value: yesNoLabel(questions.perform_garbage_seggragation) },
        { label: 'Q7 Toilet Facility', value: questions.toilet_facility_type || '—' },
        { label: 'Q8 Type of Building/House', value: questions.type_of_building_house || '—' },
        { label: 'Q9 Construction Materials of the Outer Wall', value: questions.construction_material_outer_wall || '—' },
        { label: 'Q10 Female Household Member Died in the Past 12 Months', value: deathListLabel(questions.female_hhm_died_past_12mos, questions.female_deaths) },
        { label: 'Q11 Child Below 5 Died in the Past 12 Months', value: deathListLabel(questions.child_hhm_died_past_12mos, questions.child_deaths, { includeSex: true }) },
        { label: 'Q12 Common Diseases That Cause Death in this Barangay', value: joinList(questions.common_diseases) },
        { label: 'Q13 Primary Needs of this Barangay', value: joinList(questions.primary_needs) },
        {
            label: 'Q14 Intended Stay Five Years From Now',
            value: [questions.intend_to_stay_brgy, questions.intend_to_stay_municipality, questions.intend_to_stay_province]
                .filter(Boolean)
                .join(', ') || '—',
        },
    ];
}

function handleConfirmCancel() {
    confirm.open = false;
    confirm.onCancel?.();
}

function askConfirm({ title, message, confirmLabel = 'Continue', variant = 'primary' }) {
    return new Promise((resolve) => {
        confirm.open = true;
        confirm.title = title;
        confirm.message = message;
        confirm.confirmLabel = confirmLabel;
        confirm.variant = variant;
        confirm.onConfirm = () => {
            confirm.open = false;
            resolve(true);
        };
        confirm.onCancel = () => resolve(false);
    });
}

function clearEditErrors() {
    Object.keys(editErrors).forEach((key) => {
        delete editErrors[key];
    });
}

function clearQuestionErrors() {
    Object.keys(questionErrors).forEach((key) => {
        delete questionErrors[key];
    });
}

function startEdit() {
    if (!household.value) {
        return;
    }

    editing.value = true;
    Object.assign(editForm, {
        street_id: household.value.street_id ?? '',
        house_lot: household.value.house_lot ?? '',
        ...householdStructureFromRecord(household.value),
        household_status_id: household.value.household_status_id ?? '',
    });
    Object.assign(questionForm, householdQuestionsFromRecord(household.value.questions, location));
    clearEditErrors();
    clearQuestionErrors();
}

function cancelEdit() {
    editing.value = false;
    Object.assign(editForm, emptyEditForm());
    Object.assign(questionForm, emptyHouseholdQuestionsForm(location));
    clearEditErrors();
    clearQuestionErrors();
}

async function loadHousehold() {
    loading.value = true;
    error.value = '';

    try {
        const [item, streetItems, statusItems, householdQuestionLookups, locationProfile] = await Promise.all([
            householdService.fetchHousehold(route.params.id),
            lookupService.fetchStreets(),
            lookupService.fetchLookup('household-status'),
            fetchHouseholdQuestionLookups(),
            householdService.fetchLocationProfile(),
        ]);
        household.value = item;
        streets.value = streetItems;
        householdStatuses.value = statusItems;
        Object.assign(questionLookups, householdQuestionLookups);
        Object.assign(location, locationProfile ?? { barangay: '', city: '', province: '' });
    } catch (err) {
        household.value = null;
        error.value = extractErrorMessage(err, 'Unable to load this household.');
    } finally {
        loading.value = false;
    }
}

async function handleUpdate() {
    clearEditErrors();

    if (!toId(editForm.street_id)) {
        editErrors.street_id = 'Street is required.';
    }

    if (!toId(editForm.household_status_id)) {
        editErrors.household_status_id = 'Household status is required.';
    }

    validateHouseholdStructure(editForm, editErrors);
    validateHouseholdQuestions(questionForm, questionErrors);

    const petsValid = petForm.value?.validateAll?.() !== false;

    if (Object.keys(editErrors).length || Object.keys(questionErrors).length || !petsValid) {
        return;
    }

    const allowed = await askConfirm({
        title: 'Update household',
        message: 'Save these changes to this household record?',
        confirmLabel: 'Save changes',
    });

    if (!allowed) {
        return;
    }

    saving.value = true;
    error.value = '';
    successMessage.value = '';

    try {
        await householdService.updateHousehold(route.params.id, {
            street_id: toId(editForm.street_id),
            house_lot: optionalAddressText(editForm.house_lot),
            ...householdStructurePayload(editForm),
            household_status_id: toId(editForm.household_status_id),
        });

        const questionsPayload = householdQuestionsPayload(questionForm);
        const questionsId = household.value?.questions?.household_questions_id;

        if (questionsId) {
            await householdService.updateHouseholdQuestions(questionsId, questionsPayload);
        } else {
            await householdService.createHouseholdQuestions(route.params.id, questionsPayload);
        }

        await petForm.value?.save?.();
        successMessage.value = 'Household updated successfully.';
        cancelEdit();
        await loadHousehold();
    } catch (err) {
        const validationErrors = extractValidationErrors(err);

        if (Object.keys(validationErrors).length) {
            applyValidationErrors(editErrors, validationErrors);
            applyValidationErrors(questionErrors, validationErrors);
        } else {
            error.value = extractErrorMessage(err, 'Unable to update this household.');
        }
    } finally {
        saving.value = false;
    }
}

watch(() => route.params.id, loadHousehold);
onMounted(loadHousehold);
</script>

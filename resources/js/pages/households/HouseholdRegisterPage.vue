<template>
    <AppLayout title="Household Management">
        <div class="space-y-6">
            <PageTabs :tabs="householdTabs" />

            <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ error }}
            </div>
            <div v-if="successMessage" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ successMessage }}
            </div>

            <div v-if="loadingLookups" class="rbim-card p-8 text-center text-sm text-slate-500">
                Loading registration form...
            </div>

            <div v-else-if="loadingHeadProfile" class="rbim-card p-8 text-center text-sm text-slate-500">
                Loading household head profile...
            </div>

            <ResidentSectionWizard
                v-else-if="createdHousehold && !headSectionsComplete && headResident"
                :key="headResident.resident_id"
                :resident="headResident"
                :lookups="profilingLookups"
                :location="profilingLookups.location"
                title="Household head profile"
                id-prefix="head-section"
                @finished="headSectionsComplete = true"
            />

            <HouseholdQuestionsForm
                v-else-if="createdHousehold && !questionsComplete"
                :household="createdHousehold"
                :lookups="questionLookups"
                :location="profilingLookups.location"
                @finished="questionsComplete = true"
                @skip="questionsComplete = true"
            />

            <HouseholdPetCensusForm
                v-else-if="createdHousehold && !petsComplete"
                :household-id="createdHousehold.household_id"
                :can-create="hasPermission('household.create') || hasPermission('household.update')"
                @finished="petsComplete = true"
            />

            <HouseholdContinueMembersFlow
                v-else-if="createdHousehold"
                :household="createdHousehold"
                :sexes="sexes"
                :relationships="relationships"
                :nationalities="nationalities"
                :religions="religions"
                :ethnicities="ethnicities"
                :marital-statuses="maritalStatuses"
                :existing-residents="existingResidents"
                :ensure-lookups="ensureLookups"
                :profiling-lookups="profilingLookups"
                @member-added="refreshExistingResidents"
                @members-complete="goToHouseholdDetail"
                @lookup-created="onLookupCreated"
            />

            <article v-else class="rbim-card p-6">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">
                    Register household
                </h2>
                <p class="mt-1 text-xs text-slate-500">
                    Fields marked with <span class="rbim-required">*</span> are required.
                    Click Continue to save the household and head resident information.
                    You can complete the education and skills information in the next step or skip it.
                </p>

                <form class="mt-4 space-y-8" novalidate @submit.prevent="handleSave">
                    <section class="space-y-4">
                        <h3 class="text-sm font-semibold text-slate-900">Household Information</h3>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="clan_id" class="rbim-label">
                                    Clan<span class="rbim-required" aria-hidden="true">*</span>
                                </label>
                                <select
                                    id="clan_id"
                                    v-model="household.clan_id"
                                    class="rbim-input"
                                    :class="{ 'rbim-input-error': householdErrors.clan_id }"
                                >
                                    <option value="">Select clan</option>
                                    <option v-for="option in clans" :key="option.id" :value="option.id">
                                        {{ option.label }}
                                    </option>
                                </select>
                                <p v-if="householdErrors.clan_id" class="rbim-error">{{ householdErrors.clan_id }}</p>
                            </div>
                            <div>
                                <label for="street_id" class="rbim-label">
                                    Street<span class="rbim-required" aria-hidden="true">*</span>
                                </label>
                                <select
                                    id="street_id"
                                    v-model="household.street_id"
                                    class="rbim-input"
                                    :class="{ 'rbim-input-error': householdErrors.street_id }"
                                >
                                    <option value="">Select street</option>
                                    <option v-for="option in streets" :key="option.id" :value="option.id">
                                        {{ option.label }}
                                    </option>
                                </select>
                                <p v-if="householdErrors.street_id" class="rbim-error">{{ householdErrors.street_id }}</p>
                            </div>
                            <div>
                                <label for="house_lot" class="rbim-label">House/Lot Number</label>
                                <input
                                    id="house_lot"
                                    v-model="household.house_lot"
                                    type="text"
                                    maxlength="45"
                                    class="rbim-input"
                                    :class="{ 'rbim-input-error': householdErrors.house_lot }"
                                >
                                <p v-if="householdErrors.house_lot" class="rbim-error">{{ householdErrors.house_lot }}</p>
                            </div>
                            <div>
                                <label for="number_of_house_story" class="rbim-label">
                                    Number of House Story<span class="rbim-required" aria-hidden="true">*</span>
                                </label>
                                <input
                                    id="number_of_house_story"
                                    v-model="household.number_of_house_story"
                                    type="number"
                                    min="1"
                                    max="50"
                                    step="1"
                                    class="rbim-input"
                                    :class="{ 'rbim-input-error': householdErrors.number_of_house_story }"
                                >
                                <p v-if="householdErrors.number_of_house_story" class="rbim-error">{{ householdErrors.number_of_house_story }}</p>
                            </div>
                            <div>
                                <label for="has_basement" class="rbim-label">
                                    Does the house have a basement?<span class="rbim-required" aria-hidden="true">*</span>
                                </label>
                                <select
                                    id="has_basement"
                                    v-model="household.has_basement"
                                    class="rbim-input"
                                    :class="{ 'rbim-input-error': householdErrors.has_basement }"
                                >
                                    <option value="">Select</option>
                                    <option value="true">Yes</option>
                                    <option value="false">No</option>
                                </select>
                                <p v-if="householdErrors.has_basement" class="rbim-error">{{ householdErrors.has_basement }}</p>
                            </div>
                            <div v-if="household.has_basement === 'true'">
                                <label for="number_of_basement_level" class="rbim-label">
                                    Number of Basement Level<span class="rbim-required" aria-hidden="true">*</span>
                                </label>
                                <input
                                    id="number_of_basement_level"
                                    v-model="household.number_of_basement_level"
                                    type="number"
                                    min="1"
                                    max="20"
                                    step="1"
                                    class="rbim-input"
                                    :class="{ 'rbim-input-error': householdErrors.number_of_basement_level }"
                                >
                                <p v-if="householdErrors.number_of_basement_level" class="rbim-error">{{ householdErrors.number_of_basement_level }}</p>
                            </div>
                        </div>
                    </section>

                    <section class="space-y-4">
                        <h3 class="text-sm font-semibold text-slate-900">Household Head Information</h3>
                        <ResidentDemographicsFields
                            v-model="head"
                            :min-age="HOUSEHOLD_HEAD_MIN_AGE"
                            :errors="headErrors"
                            :sexes="sexes"
                            :relationship-options="headRelationshipOptions"
                            :nationalities="nationalities"
                            :religions="religions"
                            :ethnicities="ethnicities"
                            :marital-statuses="maritalStatuses"
                            :lookup-limit="REGISTER_HOUSEHOLD_LOOKUP_LIMIT"
                            :preferred-lookup-ids="REGISTER_HOUSEHOLD_LOOKUP_IDS"
                            relationship-locked
                            id-prefix="head"
                            @validate-name="validateHeadName"
                            @lookup-created="onLookupCreated"
                            @lookup-error="onLookupError"
                        />
                    </section>

                    <div class="flex flex-wrap items-center gap-2">
                        <button type="submit" class="rbim-btn" :disabled="saving">
                            {{ saving ? 'Saving...' : 'Continue' }}
                        </button>
                    </div>
                </form>
            </article>
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
import { useRouter } from 'vue-router';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import HouseholdContinueMembersFlow from '@/components/HouseholdContinueMembersFlow.vue';
import HouseholdPetCensusForm from '@/components/HouseholdPetCensusForm.vue';
import HouseholdQuestionsForm from '@/components/HouseholdQuestionsForm.vue';
import PageTabs from '@/components/PageTabs.vue';
import ResidentDemographicsFields from '@/components/ResidentDemographicsFields.vue';
import ResidentSectionWizard from '@/components/ResidentSectionWizard.vue';
import { useAuth } from '@/composables/useAuth';
import { useSectionTabs } from '@/composables/useSectionTabs';
import { extractErrorMessage, extractValidationErrors } from '@/services/http';
import * as householdService from '@/services/householdService';
import * as lookupService from '@/services/lookupService';
import * as residentService from '@/services/residentService';
import {
    REGISTER_HOUSEHOLD_LOOKUP_IDS,
    REGISTER_HOUSEHOLD_LOOKUP_LIMIT,
    applyLookupCreated,
    ensureResidentDemographicLookups,
} from '@/utils/demographicLookups';
import {
    emptyHouseholdStructure,
    householdStructurePayload,
    validateHouseholdStructure,
    HEAD_RELATIONSHIP_ID,
    HOUSEHOLD_HEAD_MIN_AGE,
    applyValidationErrors,
    assignResidentNameError,
    duplicateResidentMatch,
    emptyResidentForm,
    optionalAddressText,
    residentPayload,
    toId,
    validateResidentForm,
} from '@/utils/residentForm';
import {
    HOUSEHOLD_REGISTER_DRAFT_KEY,
    clearFormDraft,
    readFormDraft,
    writeFormDraft,
} from '@/utils/formDraft';
import {
    emptyProfilingLookups,
    fetchProfilingLookups,
} from '@/utils/profilingLookups';
import {
    emptyHouseholdQuestionLookups,
    fetchHouseholdQuestionLookups,
} from '@/utils/householdQuestions';
import { profilingResidentReady } from '@/utils/residentProfiling';

const router = useRouter();
const { householdTabs } = useSectionTabs();
const { hasPermission } = useAuth();

const loadingLookups = ref(true);
const saving = ref(false);
const error = ref('');
const successMessage = ref('');
const createdHousehold = ref(null);
const loadingHeadProfile = ref(false);
const headSectionsComplete = ref(false);
const questionsComplete = ref(false);
const petsComplete = ref(false);

const clans = ref([]);
const streets = ref([]);
const sexes = ref([]);
const relationships = ref([]);
const nationalities = ref([]);
const religions = ref([]);
const ethnicities = ref([]);
const maritalStatuses = ref([]);
const existingResidents = ref([]);
const profilingLookups = reactive(emptyProfilingLookups());
const questionLookups = reactive(emptyHouseholdQuestionLookups());

const household = reactive(emptyHousehold());
const head = ref(emptyHead());
const householdErrors = reactive({});
const headErrors = reactive({});
const confirm = reactive({
    open: false,
    title: '',
    message: '',
    confirmLabel: 'Confirm',
    variant: 'primary',
    onConfirm: null,
    onCancel: null,
});

const headRelationshipOptions = computed(() => (
    relationships.value.filter((option) => Number(option.id) === HEAD_RELATIONSHIP_ID)
));

const headResident = computed(() => {
    const household = createdHousehold.value;
    const head = household?.head;

    if (profilingResidentReady(head)) {
        return head;
    }

    return null;
});

function emptyHousehold() {
    return {
        clan_id: '',
        street_id: '',
        house_lot: '',
        ...emptyHouseholdStructure(),
    };
}

function emptyHead() {
    return emptyResidentForm({
        relationship_to_hh_id: HEAD_RELATIONSHIP_ID,
    });
}

function restoreHouseholdRegisterDraft() {
    const draft = readFormDraft(HOUSEHOLD_REGISTER_DRAFT_KEY);

    if (!draft) {
        return;
    }

    if (draft.household && typeof draft.household === 'object') {
        Object.assign(household, emptyHousehold(), draft.household);
    }

    if (draft.head && typeof draft.head === 'object') {
        head.value = {
            ...emptyHead(),
            ...draft.head,
            relationship_to_hh_id: HEAD_RELATIONSHIP_ID,
        };
    }
}

restoreHouseholdRegisterDraft();

watch(
    [household, head],
    () => {
        writeFormDraft(HOUSEHOLD_REGISTER_DRAFT_KEY, {
            household: { ...household },
            head: { ...head.value },
        });
    },
    { deep: true },
);

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

function clearHouseholdErrors() {
    Object.keys(householdErrors).forEach((key) => {
        delete householdErrors[key];
    });
}

function clearHeadErrors() {
    Object.keys(headErrors).forEach((key) => {
        delete headErrors[key];
    });
}

function onLookupCreated(payload) {
    applyLookupCreated({
        nationality: nationalities,
        religion: religions,
        ethnicity: ethnicities,
    }, payload);
}

function onLookupError({ field, message }) {
    if (field) {
        headErrors[field] = message;
    }
}

function ensureLookups(form) {
    return ensureResidentDemographicLookups(form, {
        nationalities,
        religions,
        ethnicities,
        canCreateNationality: hasPermission('nationality.create'),
        canCreateReligion: hasPermission('religion.create'),
        canCreateEthnicity: hasPermission('ethnicity.create'),
    });
}

function validateHeadName(field, label, required = false) {
    assignResidentNameError(head.value, headErrors, field, label, required);
}

function validateHousehold() {
    clearHouseholdErrors();

    if (!toId(household.clan_id)) {
        householdErrors.clan_id = 'Clan is required.';
    }

    if (!toId(household.street_id)) {
        householdErrors.street_id = 'Street is required.';
    }

    validateHouseholdStructure(household, householdErrors);

    return Object.keys(householdErrors).length === 0;
}

async function loadLookups() {
    loadingLookups.value = true;
    error.value = '';

    try {
        const [
            clanItems,
            streetItems,
            sexItems,
            relationshipItems,
            nationalityItems,
            religionItems,
            ethnicityItems,
            maritalItems,
            sectionLookups,
            householdQuestionLookups,
        ] = await Promise.all([
            lookupService.fetchLookup('clan'),
            lookupService.fetchStreets(),
            lookupService.fetchLookup('sex'),
            lookupService.fetchLookup('relationship-to-hh'),
            lookupService.fetchNationalities(),
            lookupService.fetchReligions(),
            lookupService.fetchEthnicities(),
            lookupService.fetchLookup('marital-status'),
            fetchProfilingLookups(),
            fetchHouseholdQuestionLookups(),
        ]);

        clans.value = clanItems;
        streets.value = streetItems;
        sexes.value = sexItems;
        relationships.value = relationshipItems;
        nationalities.value = nationalityItems;
        religions.value = religionItems;
        ethnicities.value = ethnicityItems;
        maritalStatuses.value = maritalItems;
        Object.assign(profilingLookups, sectionLookups);
        Object.assign(questionLookups, householdQuestionLookups);
        await refreshExistingResidents();
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to load the registration form.');
    } finally {
        loadingLookups.value = false;
    }
}

async function handleSave() {
    error.value = '';
    successMessage.value = '';
    clearHeadErrors();

    const lookupErrors = await ensureResidentDemographicLookups(head.value, {
        nationalities,
        religions,
        ethnicities,
        canCreateNationality: hasPermission('nationality.create'),
        canCreateReligion: hasPermission('religion.create'),
        canCreateEthnicity: hasPermission('ethnicity.create'),
    });

    const householdValid = validateHousehold();
    const headValid = validateResidentForm(head.value, headErrors, {
        requireRelationship: false,
        minAge: HOUSEHOLD_HEAD_MIN_AGE,
    });
    Object.assign(headErrors, lookupErrors);

    if (!householdValid || !headValid || Object.keys(lookupErrors).length) {
        return;
    }

    const duplicate = duplicateResidentMatch(existingResidents.value, head.value);

    if (duplicate) {
        const proceed = await askConfirm({
            title: 'Resident with the same name exists',
            message: `“${duplicate.full_name || `${duplicate.last_name}, ${duplicate.first_name}`}” is already recorded. Save this household anyway?`,
            confirmLabel: 'Save anyway',
            variant: 'danger',
        });

        if (!proceed) {
            return;
        }
    }

    saving.value = true;

    try {
        const headData = residentPayload({
            ...head.value,
            relationship_to_hh_id: HEAD_RELATIONSHIP_ID,
        });

        createdHousehold.value = await householdService.createHousehold({
            clan_id: toId(household.clan_id),
            street_id: toId(household.street_id),
            house_lot: optionalAddressText(household.house_lot),
            ...householdStructurePayload(household),
            head: headData,
        });

        const headId = createdHousehold.value?.head_resident_id
            || createdHousehold.value?.head?.resident_id;

        if (headId) {
            loadingHeadProfile.value = true;

            try {
                createdHousehold.value = {
                    ...createdHousehold.value,
                    head: await residentService.loadProfilingResident(
                        createdHousehold.value.head ?? headId,
                    ),
                };
            } finally {
                loadingHeadProfile.value = false;
            }
        }

        clearFormDraft(HOUSEHOLD_REGISTER_DRAFT_KEY);
        Object.assign(household, emptyHousehold());
        head.value = emptyHead();

        successMessage.value = '';
        await refreshExistingResidents();
    } catch (err) {
        const validationErrors = extractValidationErrors(err);

        if (Object.keys(validationErrors).length) {
            applyValidationErrors(householdErrors, validationErrors);
            applyValidationErrors(headErrors, validationErrors, 'head');
            error.value = '';
        } else {
            error.value = extractErrorMessage(err, 'Unable to register this household.');
        }
    } finally {
        saving.value = false;
    }
}

async function refreshExistingResidents() {
    try {
        existingResidents.value = await residentService.fetchResidents();
    } catch {
        existingResidents.value = [];
    }
}

function goToHouseholdDetail() {
    const householdId = createdHousehold.value?.household_id;

    if (!householdId) {
        router.push({ name: 'households' });

        return;
    }

    router.push({
        name: 'household-detail',
        params: { id: householdId },
        query: { encoded: '1' },
    });
}

onMounted(loadLookups);
</script>

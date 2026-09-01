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

            <HouseholdContinueMembersFlow
                v-else-if="createdHousehold && !membersComplete"
                :household="createdHousehold"
                :sexes="sexes"
                :relationships="relationships"
                :nationalities="nationalities"
                :religions="religions"
                :ethnicities="ethnicities"
                :marital-statuses="maritalStatuses"
                :resident-types="residentTypes"
                :existing-residents="existingResidents"
                :ensure-lookups="ensureLookups"
                @member-added="refreshExistingResidents"
                @members-complete="membersComplete = true"
                @lookup-created="onLookupCreated"
            />

            <HouseholdAssessmentForm
                v-else-if="createdHousehold"
                :household="createdHousehold"
                @saved="goToHouseholdDetail"
            />

            <article v-else class="rbim-card p-6">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">
                    Register household
                </h2>
                <p class="mt-1 text-xs text-slate-500">
                    Fields marked with <span class="rbim-required">*</span> are required.
                    The household and head resident are saved together.
                </p>

                <form class="mt-4 space-y-8" novalidate @submit.prevent="handleSave">
                    <section class="space-y-4">
                        <h3 class="text-sm font-semibold text-slate-900">Household</h3>
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
                                <label for="block_num" class="rbim-label">Block Number</label>
                                <input
                                    id="block_num"
                                    v-model="household.block_num"
                                    type="text"
                                    maxlength="45"
                                    class="rbim-input"
                                    :class="{ 'rbim-input-error': householdErrors.block_num }"
                                >
                                <p v-if="householdErrors.block_num" class="rbim-error">{{ householdErrors.block_num }}</p>
                            </div>
                            <div>
                                <label for="building_name" class="rbim-label">Building Name</label>
                                <input
                                    id="building_name"
                                    v-model="household.building_name"
                                    type="text"
                                    maxlength="45"
                                    class="rbim-input"
                                    :class="{ 'rbim-input-error': householdErrors.building_name }"
                                >
                                <p v-if="householdErrors.building_name" class="rbim-error">{{ householdErrors.building_name }}</p>
                            </div>
                            <div>
                                <label for="unit_num" class="rbim-label">Unit Number</label>
                                <input
                                    id="unit_num"
                                    v-model="household.unit_num"
                                    type="text"
                                    maxlength="45"
                                    class="rbim-input"
                                    :class="{ 'rbim-input-error': householdErrors.unit_num }"
                                >
                                <p v-if="householdErrors.unit_num" class="rbim-error">{{ householdErrors.unit_num }}</p>
                            </div>
                        </div>
                    </section>

                    <section class="space-y-4">
                        <h3 class="text-sm font-semibold text-slate-900">Head Resident</h3>
                        <ResidentDemographicsFields
                            v-model="head"
                            :errors="headErrors"
                            :sexes="sexes"
                            :relationship-options="headRelationshipOptions"
                            :nationalities="nationalities"
                            :religions="religions"
                            :ethnicities="ethnicities"
                            :marital-statuses="maritalStatuses"
                            :resident-types="residentTypes"
                            relationship-locked
                            id-prefix="head"
                            @validate-name="validateHeadName"
                            @lookup-created="onLookupCreated"
                            @lookup-error="onLookupError"
                        />
                    </section>

                    <div class="flex flex-wrap items-center gap-2">
                        <button type="submit" class="rbim-btn" :disabled="saving">
                            {{ saving ? 'Saving...' : 'Register household' }}
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
import { computed, onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import HouseholdAssessmentForm from '@/components/HouseholdAssessmentForm.vue';
import HouseholdContinueMembersFlow from '@/components/HouseholdContinueMembersFlow.vue';
import PageTabs from '@/components/PageTabs.vue';
import ResidentDemographicsFields from '@/components/ResidentDemographicsFields.vue';
import { useAuth } from '@/composables/useAuth';
import { useSectionTabs } from '@/composables/useSectionTabs';
import { extractErrorMessage, extractValidationErrors } from '@/services/http';
import * as householdService from '@/services/householdService';
import * as lookupService from '@/services/lookupService';
import * as residentService from '@/services/residentService';
import {
    applyLookupCreated,
    ensureResidentDemographicLookups,
} from '@/utils/demographicLookups';
import {
    HEAD_RELATIONSHIP_ID,
    applyValidationErrors,
    assignResidentNameError,
    duplicateResidentMatch,
    emptyResidentForm,
    optionalAddressText,
    residentPayload,
    toId,
    validateResidentForm,
} from '@/utils/residentForm';

const router = useRouter();
const { householdTabs } = useSectionTabs();
const { hasPermission } = useAuth();

const loadingLookups = ref(true);
const saving = ref(false);
const error = ref('');
const successMessage = ref('');
const createdHousehold = ref(null);
const membersComplete = ref(false);

const clans = ref([]);
const streets = ref([]);
const sexes = ref([]);
const relationships = ref([]);
const nationalities = ref([]);
const religions = ref([]);
const ethnicities = ref([]);
const maritalStatuses = ref([]);
const residentTypes = ref([]);
const existingResidents = ref([]);

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

function emptyHousehold() {
    return {
        clan_id: '',
        street_id: '',
        house_lot: '',
        block_num: '',
        building_name: '',
        unit_num: '',
    };
}

function emptyHead() {
    return emptyResidentForm({
        relationship_to_hh_id: HEAD_RELATIONSHIP_ID,
    });
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
            residentTypeItems,
        ] = await Promise.all([
            lookupService.fetchLookup('clan'),
            lookupService.fetchStreets(),
            lookupService.fetchLookup('sex'),
            lookupService.fetchLookup('relationship-to-hh'),
            lookupService.fetchNationalities(),
            lookupService.fetchReligions(),
            lookupService.fetchEthnicities(),
            lookupService.fetchLookup('marital-status'),
            lookupService.fetchLookup('resident-type'),
        ]);

        clans.value = clanItems;
        streets.value = streetItems;
        sexes.value = sexItems;
        relationships.value = relationshipItems;
        nationalities.value = nationalityItems;
        religions.value = religionItems;
        ethnicities.value = ethnicityItems;
        maritalStatuses.value = maritalItems;
        residentTypes.value = residentTypeItems;
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
    const headValid = validateResidentForm(head.value, headErrors, { requireRelationship: false });
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
            block_num: optionalAddressText(household.block_num),
            building_name: optionalAddressText(household.building_name),
            unit_num: optionalAddressText(household.unit_num),
            head: headData,
        });

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
        name: 'household-assessment-detail',
        params: { id: householdId },
        query: { encoded: '1' },
    });
}

onMounted(loadLookups);
</script>

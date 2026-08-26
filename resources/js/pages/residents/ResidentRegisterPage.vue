<template>
    <AppLayout title="Resident Management">
        <div class="space-y-6">
            <PageTabs :tabs="residentTabs" />

            <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ error }}
            </div>
            <div v-if="successMessage" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ successMessage }}
            </div>

            <div v-if="loadingLookups" class="rbim-card p-8 text-center text-sm text-slate-500">
                Loading registration form...
            </div>

            <article v-else class="rbim-card p-6">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">
                    Register resident
                </h2>
                <p class="mt-1 text-xs text-slate-500">
                    Fields marked with <span class="rbim-required">*</span> are required.
                    This adds a resident to an existing household. The household head is registered with a new household.
                </p>

                <form class="mt-6 space-y-8" novalidate @submit.prevent="handleSave">
                    <section class="space-y-4">
                        <h3 class="text-sm font-semibold text-slate-900">Household</h3>
                        <HouseholdSearch
                            v-model="form.household_id"
                            :options="households"
                            required
                            hint="Search by street or head resident name."
                            :error="formErrors.household_id"
                        />
                    </section>

                    <section class="space-y-4">
                        <h3 class="text-sm font-semibold text-slate-900">Demographics</h3>
                        <ResidentDemographicsFields
                            v-model="form"
                            :errors="formErrors"
                            :sexes="sexes"
                            :relationship-options="memberRelationships"
                            :nationalities="nationalities"
                            :religions="religions"
                            :ethnicities="ethnicities"
                            :marital-statuses="maritalStatuses"
                            :resident-types="residentTypes"
                            id-prefix="resident"
                            @validate-name="validateName"
                        />
                    </section>

                    <div>
                        <button type="submit" class="rbim-btn" :disabled="saving">
                            {{ saving ? 'Saving...' : 'Register resident' }}
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
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import HouseholdSearch from '@/components/HouseholdSearch.vue';
import PageTabs from '@/components/PageTabs.vue';
import ResidentDemographicsFields from '@/components/ResidentDemographicsFields.vue';
import { useSectionTabs } from '@/composables/useSectionTabs';
import { extractErrorMessage, extractValidationErrors } from '@/services/http';
import * as householdService from '@/services/householdService';
import * as lookupService from '@/services/lookupService';
import * as residentService from '@/services/residentService';
import {
    HEAD_RELATIONSHIP_ID,
    applyValidationErrors,
    assignResidentNameError,
    duplicateResidentMatch,
    emptyResidentForm,
    residentPayload,
    toId,
    validateResidentForm,
} from '@/utils/residentForm';

const { residentTabs } = useSectionTabs();

const loadingLookups = ref(true);
const saving = ref(false);
const error = ref('');
const successMessage = ref('');

const households = ref([]);
const sexes = ref([]);
const relationships = ref([]);
const nationalities = ref([]);
const religions = ref([]);
const ethnicities = ref([]);
const maritalStatuses = ref([]);
const residentTypes = ref([]);
const existingResidents = ref([]);

const form = ref(emptyForm());
const formErrors = reactive({});
const confirm = reactive({
    open: false,
    title: '',
    message: '',
    confirmLabel: 'Confirm',
    variant: 'primary',
    onConfirm: null,
    onCancel: null,
});

const memberRelationships = computed(() => (
    relationships.value.filter((option) => Number(option.id) !== HEAD_RELATIONSHIP_ID)
));

function emptyForm() {
    return {
        household_id: null,
        ...emptyResidentForm(),
    };
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

function clearFormErrors() {
    Object.keys(formErrors).forEach((key) => {
        delete formErrors[key];
    });
}

function validateName(field, label, required = false) {
    assignResidentNameError(form.value, formErrors, field, label, required);
}

async function loadLookups() {
    loadingLookups.value = true;
    error.value = '';

    try {
        const [
            householdItems,
            sexItems,
            relationshipItems,
            nationalityItems,
            religionItems,
            ethnicityItems,
            maritalItems,
            residentTypeItems,
        ] = await Promise.all([
            householdService.fetchHouseholds(),
            lookupService.fetchLookup('sex'),
            lookupService.fetchLookup('relationship-to-hh'),
            lookupService.fetchNationalities(),
            lookupService.fetchReligions(),
            lookupService.fetchEthnicities(),
            lookupService.fetchLookup('marital-status'),
            lookupService.fetchLookup('resident-type'),
        ]);

        households.value = householdItems;
        sexes.value = sexItems;
        relationships.value = relationshipItems;
        nationalities.value = nationalityItems;
        religions.value = religionItems;
        ethnicities.value = ethnicityItems;
        maritalStatuses.value = maritalItems;
        residentTypes.value = residentTypeItems;

        try {
            existingResidents.value = await residentService.fetchResidents();
        } catch {
            existingResidents.value = [];
        }
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to load the registration form.');
    } finally {
        loadingLookups.value = false;
    }
}

async function handleSave() {
    error.value = '';
    successMessage.value = '';
    clearFormErrors();

    if (!toId(form.value.household_id)) {
        formErrors.household_id = 'Household is required.';
    }

    const demographicsValid = validateResidentForm(form.value, formErrors, { requireRelationship: true });

    if (!demographicsValid || formErrors.household_id) {
        return;
    }

    const duplicate = duplicateResidentMatch(existingResidents.value, form.value);

    if (duplicate) {
        const proceed = await askConfirm({
            title: 'Resident with the same name exists',
            message: `“${duplicate.full_name || `${duplicate.last_name}, ${duplicate.first_name}`}” is already recorded. Save this resident anyway?`,
            confirmLabel: 'Save anyway',
            variant: 'danger',
        });

        if (!proceed) {
            return;
        }
    }

    saving.value = true;

    try {
        await residentService.createResident({
            ...residentPayload(form.value),
            household_id: toId(form.value.household_id),
        });

        successMessage.value = 'Resident registered successfully.';
        form.value = emptyForm();
        clearFormErrors();

        try {
            existingResidents.value = await residentService.fetchResidents();
        } catch {
            existingResidents.value = [];
        }
    } catch (err) {
        const validationErrors = extractValidationErrors(err);

        if (Object.keys(validationErrors).length) {
            applyValidationErrors(formErrors, validationErrors);
            error.value = '';
        } else {
            error.value = extractErrorMessage(err, 'Unable to register this resident.');
        }
    } finally {
        saving.value = false;
    }
}

onMounted(loadLookups);
</script>

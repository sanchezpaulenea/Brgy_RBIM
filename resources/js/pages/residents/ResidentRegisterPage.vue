<template>
    <AppLayout title="Resident Management">
        <div class="space-y-6">
            <PageTabs :tabs="residentTabs" />

            <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ error }}
            </div>

            <div v-if="loadingLookups" class="rbim-card p-8 text-center text-sm text-slate-500">
                Loading registration form...
            </div>

            <article v-else-if="registrationComplete" class="rbim-card p-6">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">
                    Resident registered
                </h2>
                <p class="mt-1 text-xs text-slate-500">
                    {{ completeMessage }}
                </p>
                <p class="mt-4 text-sm text-slate-900">
                    {{ householdLabel }}
                </p>
                <div class="mt-6 flex flex-wrap items-center justify-end gap-3">
                    <button type="button" class="rbim-btn-outline" @click="addMoreResidents">
                        Add More Residents
                    </button>
                    <button type="button" class="rbim-btn" @click="finishRegistration">
                        Register
                    </button>
                </div>
            </article>

            <SequentialResidentRegistration
                v-else
                :key="registrationKey"
                :household="selectedHousehold"
                :sexes="sexes"
                :relationships="relationships"
                :nationalities="nationalities"
                :religions="religions"
                :ethnicities="ethnicities"
                :marital-statuses="maritalStatuses"
                :existing-residents="existingResidents"
                :ensure-existing-residents="ensureExistingResidents"
                :ensure-lookups="ensureLookups"
                :profiling-lookups="profilingLookups"
                :prepare-profiling-lookups="prepareProfilingLookups"
                count-title="Register resident"
                count-label="How many residents would you like to add?"
                count-hint="This adds a resident to an existing household. The household head is registered with a new household."
                noun="residents"
                id-prefix="resident"
                count-input-id="resident_count"
                @member-added="rememberResident"
                @finished="showRegistrationComplete"
                @lookup-created="onLookupCreated"
            >
                <template #household="{ disabled }">
                    <HouseholdSearch
                        v-model="selectedHouseholdId"
                        :options="households"
                        required
                        placeholder="Search household head, household ID, street, or house/lot number"
                        hint="Search by household head, household ID, street, or house/lot number."
                        :disabled="disabled"
                    />
                </template>
            </SequentialResidentRegistration>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import AppLayout from '@/layouts/AppLayout.vue';
import HouseholdSearch from '@/components/HouseholdSearch.vue';
import PageTabs from '@/components/PageTabs.vue';
import SequentialResidentRegistration from '@/components/SequentialResidentRegistration.vue';
import { useAuth } from '@/composables/useAuth';
import { useSectionTabs } from '@/composables/useSectionTabs';
import { extractErrorMessage } from '@/services/http';
import * as householdService from '@/services/householdService';
import * as lookupService from '@/services/lookupService';
import * as residentService from '@/services/residentService';
import {
    applyLookupCreated,
    ensureResidentDemographicLookups,
} from '@/utils/demographicLookups';
import { householdDisplayLabel } from '@/utils/format';
import {
    emptyProfilingLookups,
    fetchProfilingLookups,
} from '@/utils/profilingLookups';

const router = useRouter();
const { residentTabs } = useSectionTabs();
const { hasPermission } = useAuth();

const loadingLookups = ref(true);
const error = ref('');
const selectedHouseholdId = ref(null);
const registrationComplete = ref(false);
const registrationKey = ref(0);
const savedTotal = ref(0);
const savedHouseholdId = ref(null);

const households = ref([]);
const sexes = ref([]);
const relationships = ref([]);
const nationalities = ref([]);
const religions = ref([]);
const ethnicities = ref([]);
const maritalStatuses = ref([]);
const existingResidents = ref([]);
const profilingLookups = reactive(emptyProfilingLookups());

const selectedHousehold = computed(() => (
    households.value.find((household) => (
        Number(household.household_id) === Number(selectedHouseholdId.value)
    )) ?? null
));

const householdLabel = computed(() => householdDisplayLabel(selectedHousehold.value));

const completeMessage = computed(() => (
    savedTotal.value > 1
        ? 'These residents are saved. You can add more residents now, or complete registration.'
        : 'The resident is saved. You can add more residents now, or complete registration.'
));

function onLookupCreated(payload) {
    applyLookupCreated({
        nationality: nationalities,
        religion: religions,
        ethnicity: ethnicities,
    }, payload);
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

async function refreshExistingResidents() {
    try {
        existingResidents.value = await residentService.fetchResidents({ slim: 1 });
    } catch {
        existingResidents.value = [];
    }
}

function rememberResident(resident) {
    if (resident == null || resident.resident_id == null || resident.resident_id === '') {
        return;
    }

    const id = Number(resident.resident_id);

    if (existingResidents.value.some((item) => Number(item.resident_id) === id)) {
        return;
    }

    existingResidents.value = [
        ...existingResidents.value,
        {
            resident_id: resident.resident_id,
            last_name: resident.last_name,
            first_name: resident.first_name,
            middle_name: resident.middle_name,
            suffix: resident.suffix,
            full_name: resident.full_name,
        },
    ];
}

function showRegistrationComplete({ total, householdId } = {}) {
    const count = Number(total);

    savedTotal.value += Number.isInteger(count) && count > 0 ? count : 1;
    savedHouseholdId.value = householdId ?? selectedHousehold.value?.household_id ?? null;
    registrationComplete.value = true;
}

function addMoreResidents() {
    registrationComplete.value = false;
    registrationKey.value += 1;
}

function finishRegistration() {
    goToResidentsList({
        total: savedTotal.value,
        householdId: savedHouseholdId.value,
    });
}

function goToResidentsList({ total, householdId } = {}) {
    const query = {};

    if (total) {
        query.registered = String(total);
    }

    if (householdId) {
        query.household_id = String(householdId);
    }

    router.push({
        name: 'residents',
        query,
    });
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
        ] = await Promise.all([
            householdService.fetchHouseholds(),
            lookupService.fetchLookup('sex'),
            lookupService.fetchLookup('relationship-to-hh'),
            lookupService.fetchNationalities(),
            lookupService.fetchReligions(),
            lookupService.fetchEthnicities(),
            lookupService.fetchLookup('marital-status'),
        ]);

        households.value = householdItems;
        sexes.value = sexItems;
        relationships.value = relationshipItems;
        nationalities.value = nationalityItems;
        religions.value = religionItems;
        ethnicities.value = ethnicityItems;
        maritalStatuses.value = maritalItems;
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to load the registration form.');
    } finally {
        loadingLookups.value = false;
    }

    ensureExistingResidents();
    prepareProfilingLookups();
}

let existingResidentsRequest = null;
let profilingRequest = null;

function ensureExistingResidents() {
    if (!existingResidentsRequest) {
        existingResidentsRequest = refreshExistingResidents().then(() => existingResidents.value);
    }

    return existingResidentsRequest;
}

function prepareProfilingLookups() {
    if (!profilingRequest) {
        profilingRequest = fetchProfilingLookups()
            .then((sectionLookups) => {
                Object.assign(profilingLookups, sectionLookups);
            })
            .catch((err) => {
                profilingRequest = null;
                throw err;
            });
    }

    return profilingRequest;
}

onMounted(loadLookups);
</script>

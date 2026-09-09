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

            <SequentialResidentRegistration
                v-else
                :household="selectedHousehold"
                :sexes="sexes"
                :relationships="relationships"
                :nationalities="nationalities"
                :religions="religions"
                :ethnicities="ethnicities"
                :marital-statuses="maritalStatuses"
                :existing-residents="existingResidents"
                :ensure-lookups="ensureLookups"
                :profiling-lookups="profilingLookups"
                count-title="Register resident"
                count-label="How many residents would you like to add?"
                count-hint="This adds a resident to an existing household. The household head is registered with a new household."
                noun="residents"
                id-prefix="resident"
                count-input-id="resident_count"
                @member-added="refreshExistingResidents"
                @finished="goToResidentsList"
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
        existingResidents.value = await residentService.fetchResidents();
    } catch {
        existingResidents.value = [];
    }
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
            sectionLookups,
        ] = await Promise.all([
            householdService.fetchHouseholds(),
            lookupService.fetchLookup('sex'),
            lookupService.fetchLookup('relationship-to-hh'),
            lookupService.fetchNationalities(),
            lookupService.fetchReligions(),
            lookupService.fetchEthnicities(),
            lookupService.fetchLookup('marital-status'),
            fetchProfilingLookups(),
        ]);

        households.value = householdItems;
        sexes.value = sexItems;
        relationships.value = relationshipItems;
        nationalities.value = nationalityItems;
        religions.value = religionItems;
        ethnicities.value = ethnicityItems;
        maritalStatuses.value = maritalItems;
        Object.assign(profilingLookups, sectionLookups);

        await refreshExistingResidents();
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to load the registration form.');
    } finally {
        loadingLookups.value = false;
    }
}

onMounted(loadLookups);
</script>

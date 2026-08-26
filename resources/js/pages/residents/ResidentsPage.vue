<template>
    <AppLayout title="Resident Management">
        <div class="space-y-6">
            <PageTabs :tabs="residentTabs" />

            <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ error }}
            </div>

            <form class="rbim-card grid gap-3 p-4 sm:grid-cols-5" @submit.prevent="applyFilters">
                <div>
                    <label for="resident-search" class="rbim-label">Search</label>
                    <input
                        id="resident-search"
                        v-model="filters.search"
                        type="search"
                        name="resident-search"
                        autocomplete="off"
                        autocapitalize="off"
                        spellcheck="false"
                        class="rbim-input py-2"
                        placeholder="Search resident name"
                    >
                </div>
                <div>
                    <label for="resident-filter-household" class="rbim-label">Household</label>
                    <select id="resident-filter-household" v-model="filters.household_id" class="rbim-input py-2">
                        <option value="">All households</option>
                        <option v-for="household in households" :key="household.household_id" :value="household.household_id">
                            {{ householdDisplayLabel(household) }}
                        </option>
                    </select>
                </div>
                <div>
                    <label for="resident-filter-status" class="rbim-label">Resident Status</label>
                    <select id="resident-filter-status" v-model="filters.resident_status_id" class="rbim-input py-2">
                        <option value="">All statuses</option>
                        <option v-for="status in residentStatuses" :key="status.id" :value="status.id">
                            {{ status.label }}
                        </option>
                    </select>
                </div>
                <div class="flex items-end gap-2 sm:col-span-2">
                    <button type="submit" class="rbim-btn flex-1" :disabled="loading">
                        Filter
                    </button>
                    <button type="button" class="rbim-btn-outline flex-1" :disabled="loading" @click="clearFilters">
                        Refresh
                    </button>
                </div>
            </form>

            <div v-if="loading" class="rbim-card p-8 text-center text-sm text-slate-500">
                Loading residents...
            </div>
            <div v-else class="rbim-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Name</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Household</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Relationship to Household Head</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Sex</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Age</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="resident in filteredItems" :key="resident.resident_id">
                                <td class="px-4 py-3 font-medium text-slate-900">{{ resident.full_name || personDisplayName(resident) }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ householdLabelFor(resident) }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ resident.relationship_to_hh || '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ resident.sex || '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ ageLabel(resident.date_of_birth) }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ resident.resident_status || '—' }}</td>
                            </tr>
                            <tr v-if="!filteredItems.length">
                                <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                                    {{ items.length ? 'No residents match the current filters.' : 'No resident records found.' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import PageTabs from '@/components/PageTabs.vue';
import { useSectionTabs } from '@/composables/useSectionTabs';
import { extractErrorMessage } from '@/services/http';
import * as householdService from '@/services/householdService';
import * as lookupService from '@/services/lookupService';
import * as residentService from '@/services/residentService';
import { ageFromDateOfBirth, householdDisplayLabel, personDisplayName } from '@/utils/format';
import { toId } from '@/utils/residentForm';

const { residentTabs } = useSectionTabs();

const items = ref([]);
const households = ref([]);
const residentStatuses = ref([]);
const loading = ref(false);
const error = ref('');
const filters = reactive({
    search: '',
    household_id: '',
    resident_status_id: '',
});
const appliedSearch = ref('');

const householdById = computed(() => {
    const map = new Map();

    households.value.forEach((household) => {
        map.set(Number(household.household_id), household);
    });

    return map;
});

const filteredItems = computed(() => {
    const term = appliedSearch.value.trim().toLowerCase();

    if (!term) {
        return items.value;
    }

    return items.value.filter((resident) => {
        const name = String(resident.full_name || personDisplayName(resident) || '').toLowerCase();

        return name.includes(term);
    });
});

function ageLabel(dateOfBirth) {
    const age = ageFromDateOfBirth(dateOfBirth);

    return age === null ? '—' : String(age);
}

function householdLabelFor(resident) {
    const household = householdById.value.get(Number(resident.household_id));

    if (household) {
        return householdDisplayLabel(household);
    }

    return resident.household_id ? `Household ${resident.household_id}` : '—';
}

async function loadLookups() {
    const [householdItems, statusItems] = await Promise.all([
        householdService.fetchHouseholds(),
        lookupService.fetchLookup('resident-status'),
    ]);

    households.value = householdItems;
    residentStatuses.value = statusItems;
}

async function loadResidents() {
    loading.value = true;
    error.value = '';

    try {
        items.value = await residentService.fetchResidents({
            household_id: toId(filters.household_id) ?? undefined,
            resident_status_id: toId(filters.resident_status_id) ?? undefined,
        });
        appliedSearch.value = filters.search;
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to load residents.');
    } finally {
        loading.value = false;
    }
}

function applyFilters() {
    loadResidents();
}

function clearFilters() {
    filters.search = '';
    filters.household_id = '';
    filters.resident_status_id = '';
    appliedSearch.value = '';
    loadResidents();
}

onMounted(async () => {
    try {
        await loadLookups();
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to load resident filters.');
    }

    await loadResidents();
});
</script>

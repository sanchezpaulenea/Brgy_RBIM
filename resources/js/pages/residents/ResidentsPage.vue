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

            <form class="rbim-card grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5" @submit.prevent="applyFilters">
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
                        @keydown.enter.prevent="applyFilters"
                    >
                </div>
                <div>
                    <label for="resident-filter-type" class="rbim-label">Resident Type</label>
                    <select id="resident-filter-type" v-model="filters.resident_type_id" class="rbim-input py-2">
                        <option value="">All types</option>
                        <option v-for="type in residentTypes" :key="type.id" :value="type.id">
                            {{ type.label }}
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
                <div>
                    <label for="resident-filter-age-min" class="rbim-label">Minimum Age</label>
                    <input
                        id="resident-filter-age-min"
                        v-model="filters.age_min"
                        type="number"
                        min="0"
                        max="150"
                        class="rbim-input py-2"
                        placeholder="Minimum"
                    >
                </div>
                <div class="flex items-end gap-2">
                    <div class="flex-1">
                        <label for="resident-filter-age-max" class="rbim-label">Maximum Age</label>
                        <input
                            id="resident-filter-age-max"
                            v-model="filters.age_max"
                            type="number"
                            min="0"
                            max="150"
                            class="rbim-input py-2"
                            placeholder="Maximum"
                        >
                    </div>
                    <button type="button" class="rbim-btn py-2" :disabled="loading" @click="clearFilters">
                        Refresh
                    </button>
                </div>
            </form>

            <div v-if="loading" class="rbim-card p-8 text-center text-slate-500">
                Loading residents...
            </div>
            <div v-else class="rbim-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full table-fixed divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-3 py-3 text-center font-semibold text-slate-600">Name</th>
                                <th class="w-[14%] px-3 py-3 text-center font-semibold text-slate-600">Household</th>
                                <th class="w-[16%] px-3 py-3 text-center font-semibold text-slate-600">Relationship to Household Head</th>
                                <th class="w-[8%] px-3 py-3 text-center font-semibold text-slate-600">Gender</th>
                                <th class="w-[6%] px-3 py-3 text-center font-semibold text-slate-600">Age</th>
                                <th class="w-[12%] px-3 py-3 text-center font-semibold text-slate-600">Resident Status</th>
                                <th class="w-[18%] px-3 py-3 text-center font-semibold text-slate-600">Profiling</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="resident in pagedItems"
                                :key="resident.resident_id"
                                class="cursor-pointer hover:bg-slate-50"
                                @click="openDetail(resident.resident_id)"
                            >
                                <td class="px-3 py-3 text-center font-medium text-slate-900">{{ resident.full_name || personDisplayName(resident) }}</td>
                                <td class="px-3 py-3 text-center text-slate-600">{{ householdLabelFor(resident) }}</td>
                                <td class="px-3 py-3 text-center text-slate-600">{{ resident.relationship_to_hh || '—' }}</td>
                                <td class="px-3 py-3 text-center text-slate-600">{{ resident.sex || '—' }}</td>
                                <td class="px-3 py-3 text-center text-slate-600">{{ resident.age ?? ageLabel(resident.date_of_birth) }}</td>
                                <td class="px-3 py-3 text-center text-slate-600">{{ resident.resident_status || '—' }}</td>
                                <td class="px-3 py-3 text-center text-slate-600">
                                    <span
                                        class="rounded-full px-2 py-0.5 text-xs"
                                        :class="profilingBadgeClass(resident)"
                                    >
                                        {{ profilingSummary(resident) }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="!items.length">
                                <td colspan="7" class="px-4 py-8 text-center text-slate-500">
                                    {{ filtersAreActive ? 'No residents match the current filters.' : 'No resident records found.' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div
                    v-if="pageCount > 1"
                    class="grid grid-cols-3 items-center gap-3 border-t border-slate-200 px-4 py-3 text-sm"
                >
                    <div>
                        <button v-if="page > 1" type="button" class="rbim-btn-outline" @click="page -= 1">
                            Previous
                        </button>
                    </div>
                    <span class="text-center text-slate-500">Page {{ page }} of {{ pageCount }}</span>
                    <div class="flex justify-end">
                        <button type="button" class="rbim-btn-outline" :disabled="page >= pageCount" @click="page += 1">
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AppLayout from '@/layouts/AppLayout.vue';
import PageTabs from '@/components/PageTabs.vue';
import { useSectionTabs } from '@/composables/useSectionTabs';
import { extractErrorMessage } from '@/services/http';
import * as householdService from '@/services/householdService';
import * as lookupService from '@/services/lookupService';
import * as residentService from '@/services/residentService';
import { ageFromDateOfBirth, householdDisplayLabel, personDisplayName } from '@/utils/format';
import { toId } from '@/utils/residentForm';

const route = useRoute();
const router = useRouter();
const { residentTabs } = useSectionTabs();

const items = ref([]);
const households = ref([]);
const residentTypes = ref([]);
const residentStatuses = ref([]);
const loading = ref(false);
const error = ref('');
const successMessage = ref('');
const PAGE_SIZE = 50;
const page = ref(1);
const listMeta = ref({
    total: 0,
    per_page: PAGE_SIZE,
    current_page: 1,
    last_page: 1,
});
const filters = reactive({
    search: '',
    resident_type_id: '',
    resident_status_id: '',
    age_min: '',
    age_max: '',
});

const pageCount = computed(() => Math.max(1, Number(listMeta.value.last_page) || 1));
const pagedItems = computed(() => items.value);

const householdById = computed(() => {
    const map = new Map();

    households.value.forEach((household) => {
        map.set(Number(household.household_id), household);
    });

    return map;
});

function openDetail(residentId) {
    router.push({ name: 'resident-detail', params: { id: residentId } });
}

async function loadLookups() {
    const [householdItems, typeItems, statusItems] = await Promise.all([
        householdService.fetchHouseholds(),
        lookupService.fetchLookup('resident-type'),
        lookupService.fetchLookup('resident-status'),
    ]);

    households.value = householdItems;
    residentTypes.value = typeItems;
    residentStatuses.value = statusItems;
}

async function loadResidents() {
    loading.value = true;
    error.value = '';

    try {
        const payload = await residentService.fetchResidentPage({
            search: filters.search.trim() || undefined,
            resident_type_id: toId(filters.resident_type_id) ?? undefined,
            resident_status_id: toId(filters.resident_status_id) ?? undefined,
            age_min: filters.age_min === '' ? undefined : Number(filters.age_min),
            age_max: filters.age_max === '' ? undefined : Number(filters.age_max),
            page: page.value,
            per_page: PAGE_SIZE,
        });
        items.value = payload.items;
        listMeta.value = {
            total: payload.total,
            per_page: payload.per_page,
            current_page: payload.current_page,
            last_page: payload.last_page,
        };
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to load residents.');
    } finally {
        loading.value = false;
    }
}

const filtersAreActive = computed(() => (
    Boolean(
        filters.search.trim()
        || filters.resident_type_id
        || filters.resident_status_id
        || filters.age_min !== ''
        || filters.age_max !== '',
    )
));

let filterTimer = null;
let ignorePageWatch = false;

function applyFilters() {
    clearTimeout(filterTimer);
    loadResidents();
}

function clearFilters() {
    if (page.value !== 1) {
        ignorePageWatch = true;
    }
    page.value = 1;
    filters.search = '';
    filters.resident_type_id = '';
    filters.resident_status_id = '';
    filters.age_min = '';
    filters.age_max = '';
    applyFilters();
}

watch(
    () => [
        filters.search,
        filters.resident_type_id,
        filters.resident_status_id,
        filters.age_min,
        filters.age_max,
    ],
    () => {
        if (page.value !== 1) {
            ignorePageWatch = true;
            page.value = 1;
        }
        clearTimeout(filterTimer);
        filterTimer = setTimeout(() => {
            loadResidents();
        }, 250);
    },
);

watch(page, () => {
    if (ignorePageWatch) {
        ignorePageWatch = false;

        return;
    }

    clearTimeout(filterTimer);
    loadResidents();
});

watch(pageCount, (count) => {
    if (page.value > count) {
        page.value = count;
    }
});

function profilingSummary(resident) {
    return resident?.profiling_completeness?.summary || '—';
}

function profilingBadgeClass(resident) {
    const completeness = resident?.profiling_completeness;

    if (!completeness || completeness.applicable_count === 0) {
        return 'bg-slate-100 text-slate-500';
    }

    return completeness.is_complete
        ? 'bg-emerald-50 text-emerald-700'
        : 'bg-amber-50 text-amber-800';
}

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

onMounted(async () => {
    try {
        await loadLookups();
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to load resident filters.');
    }

    await loadResidents();

    const registeredCount = Number.parseInt(String(route.query.registered ?? ''), 10);

    if (Number.isInteger(registeredCount) && registeredCount > 0) {
        successMessage.value = registeredCount === 1
            ? '1 resident registered successfully.'
            : `${registeredCount} residents registered successfully.`;
        router.replace({ name: 'residents' });
    }
});
</script>

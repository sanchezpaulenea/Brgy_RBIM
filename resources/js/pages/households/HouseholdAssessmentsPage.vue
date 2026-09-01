<template>
    <AppLayout title="Household Management">
        <div class="space-y-6">
            <PageTabs :tabs="householdTabs" />

            <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ error }}
            </div>

            <form class="rbim-card grid gap-3 p-4 sm:grid-cols-3">
                <div>
                    <label for="assessment-search" class="rbim-label">Search</label>
                    <input
                        id="assessment-search"
                        v-model="filters.search"
                        type="search"
                        name="assessment-search"
                        autocomplete="off"
                        autocapitalize="off"
                        spellcheck="false"
                        class="rbim-input py-2"
                        placeholder="Search household, street, or head name"
                    >
                </div>
                <div>
                    <label for="assessment-filter-status" class="rbim-label">Census Status</label>
                    <select id="assessment-filter-status" v-model="filters.census_status_id" class="rbim-input py-2">
                        <option value="">All statuses</option>
                        <option v-for="status in censusStatuses" :key="status.id" :value="status.id">
                            {{ censusStatusLabel(status) }}
                        </option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="button" class="rbim-btn-outline" :disabled="loading" @click="loadAssessments">
                        Refresh
                    </button>
                </div>
            </form>

            <div v-if="loading" class="rbim-card p-8 text-center text-sm text-slate-500">
                Loading assessments...
            </div>
            <div v-else class="rbim-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Assessment ID</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Household</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Head Resident Name</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Census Status</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Visit End</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Encoder</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="assessment in filteredItems"
                                :key="assessment.assessment_id"
                                class="cursor-pointer hover:bg-slate-50"
                                @click="openDetail(assessment.household_id)"
                            >
                                <td class="px-4 py-3 font-mono text-xs text-slate-700">{{ assessment.assessment_id }}</td>
                                <td class="px-4 py-3 text-slate-900">
                                    {{ assessment.household_id }}
                                    <span v-if="assessment.street_name" class="text-slate-600">
                                        — {{ assessment.street_name }}{{ assessment.house_lot ? `, ${assessment.house_lot}` : '' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-medium text-slate-900">{{ assessment.head_name || '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ censusStatusLabel(assessment) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600">{{ formatDateTime(assessment.visit_end) }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ assessment.encoder_name || '—' }}</td>
                                <td class="px-4 py-3" @click.stop>
                                    <button type="button" class="rbim-btn-action" @click="openDetail(assessment.household_id)">
                                        View
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!filteredItems.length">
                                <td colspan="7" class="px-4 py-8 text-center text-slate-500">
                                    {{ items.length ? 'No assessments match the current filters.' : 'No household assessments on file.' }}
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
import { useRouter } from 'vue-router';
import AppLayout from '@/layouts/AppLayout.vue';
import PageTabs from '@/components/PageTabs.vue';
import { useSectionTabs } from '@/composables/useSectionTabs';
import { extractErrorMessage } from '@/services/http';
import * as householdService from '@/services/householdService';
import * as lookupService from '@/services/lookupService';
import { censusStatusLabel, formatDateTime, matchesSearch } from '@/utils/format';
import { toId } from '@/utils/residentForm';

const router = useRouter();
const { householdTabs } = useSectionTabs();

const items = ref([]);
const censusStatuses = ref([]);
const loading = ref(false);
const error = ref('');
const filters = reactive({
    search: '',
    census_status_id: '',
});

const filteredItems = computed(() => (
    items.value.filter((assessment) => {
        if (toId(filters.census_status_id) && Number(assessment.census_status_id) !== Number(filters.census_status_id)) {
            return false;
        }

        const haystack = [
            assessment.assessment_id,
            assessment.household_id,
            assessment.head_name,
            assessment.street_name,
            assessment.house_lot,
            censusStatusLabel(assessment),
        ].filter(Boolean).join(' ');

        return matchesSearch(haystack, filters.search);
    })
));

function openDetail(householdId) {
    router.push({ name: 'household-assessment-detail', params: { id: householdId } });
}

async function loadAssessments() {
    loading.value = true;
    error.value = '';

    try {
        items.value = await householdService.fetchAllHouseholdAssessments();
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to load assessments.');
    } finally {
        loading.value = false;
    }
}

onMounted(async () => {
    try {
        censusStatuses.value = await lookupService.fetchLookup('census-status');
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to load assessment filters.');
    }

    await loadAssessments();
});
</script>

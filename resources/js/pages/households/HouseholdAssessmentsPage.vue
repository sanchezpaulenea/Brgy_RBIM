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

            <form class="rbim-card grid gap-3 p-4 sm:grid-cols-3">
                <div>
                    <label for="assessment-search" class="rbim-label">Search household</label>
                    <input
                        id="assessment-search"
                        v-model="filters.search"
                        type="search"
                        name="assessment-search"
                        autocomplete="off"
                        autocapitalize="off"
                        spellcheck="false"
                        class="rbim-input py-2"
                        placeholder="Search household"
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
                <p class="text-xs text-slate-500 sm:col-span-3">
                    Showing the latest visit per household. Use View to open visit history. Update status appears only when that latest visit is Callback.
                </p>
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
                                    <div class="flex gap-2">
                                        <button type="button" class="rbim-btn-action" @click="openDetail(assessment.household_id)">
                                            View
                                        </button>
                                        <button
                                            v-if="canUpdateAssessmentStatus(assessment)"
                                            type="button"
                                            class="rbim-btn-action"
                                            @click="startStatusUpdate(assessment)"
                                        >
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path d="M13.586 2.586a2 2 0 112.828 2.828l-8.5 8.5a1 1 0 01-.44.253l-3 .857a.5.5 0 01-.618-.618l.857-3a1 1 0 01.253-.44l8.62-8.38z" />
                                            </svg>
                                            Update status
                                        </button>
                                    </div>
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

        <UpdateAssessmentStatusDialog
            v-model="statusForm.census_status_id"
            :open="statusForm.open"
            :statuses="censusStatuses"
            :current-status-label="statusForm.currentLabel"
            :household-label="statusForm.householdLabel"
            :error="statusForm.error"
            :saving="statusForm.saving"
            @submit="handleStatusUpdate"
            @cancel="cancelStatusUpdate"
        />

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
import PageTabs from '@/components/PageTabs.vue';
import UpdateAssessmentStatusDialog from '@/components/UpdateAssessmentStatusDialog.vue';
import { useAuth } from '@/composables/useAuth';
import { useSectionTabs } from '@/composables/useSectionTabs';
import { ROLES } from '@/constants/roles';
import { extractErrorMessage, extractValidationErrors } from '@/services/http';
import * as householdService from '@/services/householdService';
import * as lookupService from '@/services/lookupService';
import {
    censusStatusLabel,
    formatDateTime,
    householdIdentitySearchText,
    isCallbackCensusStatus,
    matchesSearch,
} from '@/utils/format';
import { toId } from '@/utils/residentForm';

const router = useRouter();
const { householdTabs } = useSectionTabs();
const { hasPermission, hasRole } = useAuth();

const items = ref([]);
const censusStatuses = ref([]);
const loading = ref(false);
const error = ref('');
const successMessage = ref('');
const filters = reactive({
    search: '',
    census_status_id: '',
});
const statusForm = reactive({
    open: false,
    saving: false,
    assessmentId: null,
    census_status_id: '',
    currentLabel: '',
    householdLabel: '',
    error: '',
});
const confirm = reactive({
    open: false,
    title: '',
    message: '',
    confirmLabel: 'Confirm',
    variant: 'primary',
    onConfirm: null,
    onCancel: null,
});

const canUpdateStatus = computed(() => (
    hasRole(ROLES.ADMIN) && hasPermission('householdassessment.updatestatus')
));

const filteredItems = computed(() => (
    items.value.filter((assessment) => {
        if (toId(filters.census_status_id) && Number(assessment.census_status_id) !== Number(filters.census_status_id)) {
            return false;
        }

        return matchesSearch(householdIdentitySearchText(assessment), filters.search);
    })
));

function assessmentHouseholdLabel(assessment) {
    const address = [assessment.street_name, assessment.house_lot].filter(Boolean).join(', ');

    if (address) {
        return `${assessment.household_id} — ${address}`;
    }

    return `Household ${assessment.household_id}`;
}

function canUpdateAssessmentStatus(assessment) {
    return canUpdateStatus.value
        && assessment.is_latest
        && isCallbackCensusStatus(assessment);
}

function openDetail(householdId) {
    router.push({ name: 'household-assessment-detail', params: { id: householdId } });
}

function startStatusUpdate(assessment) {
    statusForm.open = true;
    statusForm.saving = false;
    statusForm.assessmentId = assessment.assessment_id;
    statusForm.census_status_id = '';
    statusForm.currentLabel = censusStatusLabel(assessment);
    statusForm.householdLabel = assessmentHouseholdLabel(assessment);
    statusForm.error = '';
    successMessage.value = '';
}

function cancelStatusUpdate() {
    statusForm.open = false;
    statusForm.saving = false;
    statusForm.assessmentId = null;
    statusForm.census_status_id = '';
    statusForm.currentLabel = '';
    statusForm.householdLabel = '';
    statusForm.error = '';
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

async function handleStatusUpdate() {
    if (!toId(statusForm.census_status_id)) {
        statusForm.error = 'Census status is required.';
        return;
    }

    const allowed = await askConfirm({
        title: 'Update census status',
        message: 'Save this census status change? Only the status will be updated.',
        confirmLabel: 'Save changes',
    });

    if (!allowed) {
        return;
    }

    statusForm.saving = true;
    statusForm.error = '';
    error.value = '';
    successMessage.value = '';

    try {
        await householdService.updateHouseholdAssessmentStatus(statusForm.assessmentId, {
            census_status_id: toId(statusForm.census_status_id),
        });
        successMessage.value = 'Household assessment status updated successfully.';
        cancelStatusUpdate();
        await loadAssessments();
    } catch (err) {
        const validationErrors = extractValidationErrors(err);
        statusForm.error = validationErrors.census_status_id
            || extractErrorMessage(err, 'Unable to update this assessment status.');
    } finally {
        statusForm.saving = false;
    }
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

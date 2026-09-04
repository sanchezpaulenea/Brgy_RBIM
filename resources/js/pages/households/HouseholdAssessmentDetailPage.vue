<template>
    <AppLayout title="Household Management">
        <div class="space-y-6">
            <PageTabs :tabs="householdTabs" active-name="household-assessments" />

            <div class="flex flex-wrap items-center justify-between gap-3">
                <RouterLink :to="{ name: 'household-assessments' }" class="text-sm font-medium text-brand hover:underline">
                    ← Back to assessments
                </RouterLink>
            </div>

            <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ error }}
            </div>
            <div v-if="successMessage" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ successMessage }}
            </div>

            <div v-if="loading" class="rbim-card p-8 text-center text-sm text-slate-500">
                Loading assessment...
            </div>

            <template v-else-if="household">
                <p class="text-sm text-slate-900">
                    {{ householdLabel }}
                </p>

                <article
                    v-for="(assessment, index) in assessments"
                    :id="`assessment-${assessment.assessment_id}`"
                    :key="assessment.assessment_id"
                    class="rbim-card p-6"
                >
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">
                            {{ index === 0 ? latestAssessmentTitle : `Assessment ${assessment.assessment_id}` }}
                        </h2>
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
                    <dl class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Census Status</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ censusStatusLabel(assessment) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Visit Start</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ formatDateTime(assessment.visit_start) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Visit End</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ formatDateTime(assessment.visit_end) }}</dd>
                        </div>
                        <div v-if="assessment.next_visit_date || isCallbackCensusStatus(assessment)">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Next Visit Date</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ formatDate(assessment.next_visit_date) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Encoder</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ assessment.encoder_name || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Interviewer</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ assessment.interviewer_name || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Supervisor</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ assessment.supervisor_name || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Previous Assessment</dt>
                            <dd class="mt-1 text-sm text-slate-900">
                                <button
                                    v-if="assessment.previous_assessment_id"
                                    type="button"
                                    class="font-medium text-brand hover:underline"
                                    @click="scrollToAssessment(assessment.previous_assessment_id)"
                                >
                                    {{ previousAssessmentLinkLabel(assessment) }}
                                </button>
                                <span v-else>None (first visit)</span>
                            </dd>
                        </div>
                    </dl>
                </article>
                <p v-if="!assessments.length" class="rbim-card p-6 text-sm text-slate-500">
                    No household assessment on file.
                </p>
            </template>
        </div>

        <UpdateAssessmentStatusDialog
            v-model="statusForm.census_status_id"
            :open="statusForm.open"
            :statuses="censusStatuses"
            :current-status-label="statusForm.currentLabel"
            :household-label="householdLabel"
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
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
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
    formatDate,
    formatDateTime,
    householdDisplayLabel,
    isCallbackCensusStatus,
} from '@/utils/format';
import { toId } from '@/utils/residentForm';

const route = useRoute();
const router = useRouter();
const { householdTabs } = useSectionTabs();
const { hasPermission, hasRole } = useAuth();

const household = ref(null);
const assessments = ref([]);
const censusStatuses = ref([]);
const loading = ref(false);
const error = ref('');
const successMessage = ref('');
const statusForm = reactive({
    open: false,
    saving: false,
    assessmentId: null,
    census_status_id: '',
    currentLabel: '',
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

const householdLabel = computed(() => householdDisplayLabel(household.value));
const canUpdateStatus = computed(() => (
    hasRole(ROLES.ADMIN) && hasPermission('householdassessment.updatestatus')
));
const latestAssessmentTitle = computed(() => {
    const id = household.value?.household_id;
    const street = household.value?.street_name;

    if (!id) {
        return 'Latest assessment';
    }

    return street ? `Household ${id} — ${street}` : `Household ${id}`;
});

function previousAssessmentLinkLabel(assessment) {
    const previous = assessment.previous_assessment;

    if (!previous) {
        return `Assessment ${assessment.previous_assessment_id}`;
    }

    return `Assessment ${previous.assessment_id} — ${censusStatusLabel(previous)}`;
}

function canUpdateAssessmentStatus(assessment) {
    return canUpdateStatus.value
        && assessment.is_latest
        && isCallbackCensusStatus(assessment);
}

function startStatusUpdate(assessment) {
    statusForm.open = true;
    statusForm.saving = false;
    statusForm.assessmentId = assessment.assessment_id;
    statusForm.census_status_id = '';
    statusForm.currentLabel = censusStatusLabel(assessment);
    statusForm.error = '';
}

function cancelStatusUpdate() {
    statusForm.open = false;
    statusForm.saving = false;
    statusForm.assessmentId = null;
    statusForm.census_status_id = '';
    statusForm.currentLabel = '';
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

function scrollToAssessment(assessmentId) {
    const el = document.getElementById(`assessment-${assessmentId}`);

    el?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

async function loadAssessments() {
    loading.value = true;
    error.value = '';

    try {
        const [item, assessmentItems, statusItems] = await Promise.all([
            householdService.fetchHousehold(route.params.id),
            householdService.fetchHouseholdAssessments(route.params.id),
            lookupService.fetchLookup('census-status'),
        ]);
        household.value = item;
        assessments.value = assessmentItems;
        censusStatuses.value = statusItems;
    } catch (err) {
        household.value = null;
        assessments.value = [];
        error.value = extractErrorMessage(err, 'Unable to load this household assessment.');
    } finally {
        loading.value = false;
    }
}

watch(() => route.params.id, loadAssessments);
watch(() => route.query.encoded, (encoded) => {
    if (!encoded) {
        return;
    }

    successMessage.value = 'Household assessment encoded successfully.';
    router.replace({
        name: 'household-assessment-detail',
        params: { id: route.params.id },
    });
}, { immediate: true });
onMounted(loadAssessments);
</script>

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
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">
                        {{ index === 0 ? 'Latest assessment' : `Assessment ${assessment.assessment_id}` }}
                    </h2>
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
    </AppLayout>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AppLayout from '@/layouts/AppLayout.vue';
import PageTabs from '@/components/PageTabs.vue';
import { useSectionTabs } from '@/composables/useSectionTabs';
import { extractErrorMessage } from '@/services/http';
import * as householdService from '@/services/householdService';
import {
    censusStatusLabel,
    formatDate,
    formatDateTime,
    householdDisplayLabel,
    isCallbackCensusStatus,
} from '@/utils/format';

const route = useRoute();
const router = useRouter();
const { householdTabs } = useSectionTabs();

const household = ref(null);
const assessments = ref([]);
const loading = ref(false);
const error = ref('');
const successMessage = ref('');

const householdLabel = computed(() => householdDisplayLabel(household.value));

function previousAssessmentLinkLabel(assessment) {
    const previous = assessment.previous_assessment;

    if (!previous) {
        return `Assessment ${assessment.previous_assessment_id}`;
    }

    return `Assessment ${previous.assessment_id} — ${censusStatusLabel(previous)}`;
}

function scrollToAssessment(assessmentId) {
    const el = document.getElementById(`assessment-${assessmentId}`);

    el?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

async function loadAssessments() {
    loading.value = true;
    error.value = '';

    try {
        const [item, assessmentItems] = await Promise.all([
            householdService.fetchHousehold(route.params.id),
            householdService.fetchHouseholdAssessments(route.params.id),
        ]);
        household.value = item;
        assessments.value = assessmentItems;
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

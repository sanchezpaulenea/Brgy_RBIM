<template>
    <AppLayout title="Resident Management">
        <div class="space-y-6">
            <PageTabs :tabs="residentTabs" active-name="residents" />

            <div class="flex flex-wrap items-center justify-between gap-3">
                <RouterLink :to="{ name: 'residents' }" class="text-sm font-medium text-brand hover:underline">
                    ← Back to residents
                </RouterLink>
                <button
                    v-if="canUpdateDemographics && resident"
                    type="button"
                    class="rbim-btn-action"
                    @click="startDemographicsEdit"
                >
                    Update demographics
                </button>
            </div>

            <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ error }}
            </div>
            <div v-if="successMessage" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ successMessage }}
            </div>

            <div v-if="loading" class="rbim-card p-8 text-center text-sm text-slate-500">
                Loading resident...
            </div>

            <template v-else-if="resident">
                <article class="rbim-card p-6">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">Profiling completeness</h2>
                            <p class="mt-2 text-sm text-slate-900">{{ completeness.summary }}</p>
                        </div>
                        <span
                            class="rounded-full px-2 py-0.5 text-xs"
                            :class="completeness.is_complete ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-800'"
                        >
                            {{ completeness.is_complete ? 'Complete' : 'Incomplete' }}
                        </span>
                    </div>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Completed</h3>
                            <ul v-if="completeness.completed.length" class="mt-2 list-disc space-y-1 pl-5 text-sm text-slate-700">
                                <li v-for="section in completeness.completed" :key="`done-${section.key}`">{{ section.label }}</li>
                            </ul>
                            <p v-else class="mt-2 text-sm text-slate-500">No applicable sub-records completed yet.</p>
                        </div>
                        <div>
                            <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Missing</h3>
                            <ul v-if="completeness.missing.length" class="mt-2 list-disc space-y-1 pl-5 text-sm text-amber-800">
                                <li v-for="section in completeness.missing" :key="`missing-${section.key}`">{{ section.label }}</li>
                            </ul>
                            <p v-else class="mt-2 text-sm text-slate-500">No missing applicable sections.</p>
                        </div>
                    </div>
                    <p class="mt-4 text-xs text-slate-500">
                        Infant health, women's health, CTC, and skills are counted only when the resident's age and sex make them applicable.
                    </p>
                </article>

                <article class="rbim-card p-6">
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">Core demographics</h2>
                    <dl class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div v-for="field in demographicFields" :key="field.label">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ field.label }}</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ field.value }}</dd>
                        </div>
                    </dl>
                </article>

                <article
                    v-for="section in visibleSections"
                    :key="section.key"
                    class="rbim-card p-6"
                >
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">{{ section.title }}</h2>
                            <span
                                v-if="!section.record"
                                class="rounded-full bg-amber-50 px-2 py-0.5 text-xs text-amber-800"
                            >
                                Missing
                            </span>
                        </div>
                        <button
                            v-if="section.record && section.canUpdate"
                            type="button"
                            class="rbim-btn-action"
                            @click="startSectionEdit(section.key)"
                        >
                            Update
                        </button>
                        <button
                            v-else-if="!section.record && section.canCreate"
                            type="button"
                            class="rbim-btn-action"
                            @click="startSectionEdit(section.key)"
                        >
                            Add
                        </button>
                    </div>
                    <p v-if="!section.record" class="mt-4 text-sm text-slate-500">
                        No {{ section.title.toLowerCase() }} record yet.
                    </p>
                    <dl v-else class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div v-for="field in section.fields" :key="field.label">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ field.label }}</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ field.value }}</dd>
                        </div>
                    </dl>
                </article>
            </template>
        </div>

        <div
            v-if="editing"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4"
            @click.self="cancelEdit"
        >
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-xl bg-white shadow-xl" role="dialog" aria-modal="true">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="text-base font-semibold text-slate-900">{{ editorTitle }}</h2>
                </div>
                <form class="space-y-4 px-5 py-4" novalidate @submit.prevent="handleSave">
                    <div v-if="editError" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ editError }}
                    </div>

                    <template v-if="editing === 'demographics'">
                        <ResidentDemographicsFields
                            v-model="editForm"
                            id-prefix="resident-edit"
                            :errors="editErrors"
                            :sexes="lookups.sex"
                            :relationships="relationshipOptions"
                            :nationalities="lookups.nationality"
                            :religions="lookups.religion"
                            :ethnicities="lookups.ethnicity"
                            :marital-statuses="lookups.maritalStatus"
                            :resident-types="lookups.residentType"
                            :relationship-locked="Boolean(resident?.is_household_head)"
                            @validate-name="() => {}"
                        />
                        <div>
                            <label for="resident-edit-status" class="rbim-label">Resident Status</label>
                            <select id="resident-edit-status" v-model="editForm.resident_status_id" class="rbim-input">
                                <option v-for="option in lookups.residentStatus" :key="option.id" :value="option.id">
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>
                    </template>

                    <template v-else-if="editing === 'education'">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="rbim-label">Highest level of education<span class="rbim-required">*</span></label>
                                <select v-model="editForm.highest_lvl_of_educ_id" class="rbim-input">
                                    <option value="">Select</option>
                                    <option v-for="option in lookups.highestLvlOfEduc" :key="option.id" :value="option.id">{{ option.label }}</option>
                                </select>
                                <p v-if="editErrors.highest_lvl_of_educ_id" class="rbim-error">{{ editErrors.highest_lvl_of_educ_id }}</p>
                            </div>
                            <div>
                                <label class="rbim-label">Enrollment status<span class="rbim-required">*</span></label>
                                <select v-model="editForm.current_enrollement_status_id" class="rbim-input">
                                    <option value="">Select</option>
                                    <option v-for="option in lookups.currentEnrollmentStatus" :key="option.id" :value="option.id">{{ option.label }}</option>
                                </select>
                                <p v-if="editErrors.current_enrollement_status_id" class="rbim-error">{{ editErrors.current_enrollement_status_id }}</p>
                            </div>
                            <div>
                                <label class="rbim-label">School level<span class="rbim-required">*</span></label>
                                <select v-model="editForm.school_lvl_id" class="rbim-input">
                                    <option value="">Select</option>
                                    <option v-for="option in lookups.schoolLvl" :key="option.id" :value="option.id">{{ option.label }}</option>
                                </select>
                                <p v-if="editErrors.school_lvl_id" class="rbim-error">{{ editErrors.school_lvl_id }}</p>
                            </div>
                            <div v-if="educationEnrolled">
                                <label class="rbim-label">School barangay</label>
                                <input v-model="editForm.place_of_school_brgy" type="text" maxlength="45" class="rbim-input">
                                <p v-if="editErrors.place_of_school_brgy" class="rbim-error">{{ editErrors.place_of_school_brgy }}</p>
                            </div>
                            <div v-if="educationEnrolled">
                                <label class="rbim-label">School city / municipality</label>
                                <input v-model="editForm.place_of_school_city_municipality" type="text" maxlength="45" class="rbim-input">
                                <p v-if="editErrors.place_of_school_city_municipality" class="rbim-error">{{ editErrors.place_of_school_city_municipality }}</p>
                            </div>
                        </div>
                    </template>

                    <template v-else-if="editing === 'economic'">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="rbim-label">Monthly income<span class="rbim-required">*</span></label>
                                <input v-model="editForm.monthly_income" type="number" min="0" class="rbim-input">
                                <p v-if="editErrors.monthly_income" class="rbim-error">{{ editErrors.monthly_income }}</p>
                            </div>
                            <div>
                                <label class="rbim-label">Source of income<span class="rbim-required">*</span></label>
                                <select v-model="editForm.source_of_income_id" class="rbim-input">
                                    <option value="">Select</option>
                                    <option v-for="option in lookups.sourceOfIncome" :key="option.id" :value="option.id">{{ option.label }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="rbim-label">Status of work / business<span class="rbim-required">*</span></label>
                                <select v-model="editForm.status_of_work_business_id" class="rbim-input">
                                    <option value="">Select</option>
                                    <option v-for="option in lookups.statusOfWorkBusiness" :key="option.id" :value="option.id">{{ option.label }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="rbim-label">Place of work / business</label>
                                <input v-model="editForm.place_of_work_business" type="text" maxlength="45" class="rbim-input">
                            </div>
                        </div>
                    </template>

                    <template v-else-if="editing === 'infant_health'">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="rbim-label">Place of delivery<span class="rbim-required">*</span></label>
                                <select v-model="editForm.place_of_delivery_id" class="rbim-input">
                                    <option value="">Select</option>
                                    <option v-for="option in lookups.placeOfDelivery" :key="option.id" :value="option.id">{{ option.label }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="rbim-label">Birth attendant<span class="rbim-required">*</span></label>
                                <select v-model="editForm.birth_attendant_id" class="rbim-input">
                                    <option value="">Select</option>
                                    <option v-for="option in lookups.birthAttendant" :key="option.id" :value="option.id">{{ option.label }}</option>
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="rbim-label">Immunization<span class="rbim-required">*</span></label>
                                <input v-model="editForm.immunization" type="text" maxlength="45" class="rbim-input">
                            </div>
                        </div>
                    </template>

                    <template v-else-if="editing === 'health'">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="rbim-label">Health insurance<span class="rbim-required">*</span></label>
                                <select v-model="editForm.health_insurance_id" class="rbim-input">
                                    <option value="">Select</option>
                                    <option v-for="option in lookups.healthInsurance" :key="option.id" :value="option.id">{{ option.label }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="rbim-label">Facility visited (past 12 months)<span class="rbim-required">*</span></label>
                                <select v-model="editForm.facility_visited_past_12mos_id" class="rbim-input">
                                    <option value="">Select</option>
                                    <option v-for="option in lookups.facilityVisited" :key="option.id" :value="option.id">{{ option.label }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="rbim-label">Visit reason<span class="rbim-required">*</span></label>
                                <select v-model="editForm.facility_visit_reason_id" class="rbim-input">
                                    <option value="">Select</option>
                                    <option v-for="option in lookups.facilityVisitReason" :key="option.id" :value="option.id">{{ option.label }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="rbim-label">Disability</label>
                                <input v-model="editForm.disability" type="text" maxlength="45" class="rbim-input">
                            </div>
                        </div>
                    </template>

                    <template v-else-if="editing === 'women_health'">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="rbim-label">Living children<span class="rbim-required">*</span></label>
                                <input v-model="editForm.living_children" type="number" min="0" class="rbim-input">
                            </div>
                            <div>
                                <label class="rbim-label">Family planning method<span class="rbim-required">*</span></label>
                                <select v-model="editForm.family_planning_method_id" class="rbim-input">
                                    <option value="">Select</option>
                                    <option v-for="option in lookups.familyPlanningMethod" :key="option.id" :value="option.id">{{ option.label }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="rbim-label">Source of FP method<span class="rbim-required">*</span></label>
                                <select v-model="editForm.source_of_fp_method_id" class="rbim-input">
                                    <option value="">Select</option>
                                    <option v-for="option in lookups.sourceOfFpMethod" :key="option.id" :value="option.id">{{ option.label }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="rbim-label">Intention to use FP<span class="rbim-required">*</span></label>
                                <select v-model="editForm.have_intention_to_use_fp" class="rbim-input">
                                    <option :value="true">Yes</option>
                                    <option :value="false">No</option>
                                </select>
                            </div>
                        </div>
                    </template>

                    <template v-else-if="editing === 'sociocivic'">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div v-if="resident?.sociocivic?.field_relevance?.solo_parent !== false || !resident?.sociocivic">
                                <label class="rbim-label">Solo parent status<span class="rbim-required">*</span></label>
                                <select v-model="editForm.solo_parent_status_id" class="rbim-input">
                                    <option value="">Select</option>
                                    <option v-for="option in lookups.soloParentStatus" :key="option.id" :value="option.id">{{ option.label }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="rbim-label">Registered senior citizen<span class="rbim-required">*</span></label>
                                <select v-model="editForm.registered_sen_citizen" class="rbim-input">
                                    <option :value="true">Yes</option>
                                    <option :value="false">No</option>
                                </select>
                            </div>
                            <div>
                                <label class="rbim-label">Registered barangay voter</label>
                                <input v-model="editForm.registered_barangay_voter" type="text" maxlength="45" class="rbim-input">
                            </div>
                        </div>
                    </template>

                    <template v-else-if="editing === 'migration'">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="rbim-label">Previous residence (6 months) barangay</label>
                                <input v-model="editForm.previous_residence_6mos_brgy" type="text" maxlength="45" class="rbim-input">
                            </div>
                            <div>
                                <label class="rbim-label">Previous residence (6 months) city</label>
                                <input v-model="editForm.previous_residence_6mos_city_municipality" type="text" maxlength="45" class="rbim-input">
                            </div>
                            <div>
                                <label class="rbim-label">Previous residence (5 years) barangay</label>
                                <input v-model="editForm.previous_residence_5yrs_brgy" type="text" maxlength="45" class="rbim-input">
                            </div>
                            <div>
                                <label class="rbim-label">Previous residence (5 years) city</label>
                                <input v-model="editForm.previous_residence_5yrs_city_municipality" type="text" maxlength="45" class="rbim-input">
                            </div>
                            <div>
                                <label class="rbim-label">Date of transfer in barangay</label>
                                <input v-model="editForm.date_of_transfer_in_brgy" type="date" class="rbim-input">
                            </div>
                            <div>
                                <label class="rbim-label">Duration of stay</label>
                                <input v-model="editForm.duration_of_stay" type="date" class="rbim-input">
                            </div>
                            <div>
                                <label class="rbim-label">Reason for leaving<span class="rbim-required">*</span></label>
                                <select v-model="editForm.reason_for_leaving_id" class="rbim-input">
                                    <option value="">Select</option>
                                    <option v-for="option in lookups.reasonForLeaving" :key="option.id" :value="option.id">{{ option.label }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="rbim-label">Reason for transfer<span class="rbim-required">*</span></label>
                                <select v-model="editForm.reason_for_transfer_id" class="rbim-input">
                                    <option value="">Select</option>
                                    <option v-for="option in lookups.reasonForTransfer" :key="option.id" :value="option.id">{{ option.label }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="rbim-label">Will return to previous residence<span class="rbim-required">*</span></label>
                                <select v-model="editForm.will_return_to_previous_residence" class="rbim-input">
                                    <option :value="true">Yes</option>
                                    <option :value="false">No</option>
                                </select>
                            </div>
                        </div>
                    </template>

                    <template v-else-if="editing === 'ctc'">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="rbim-label">Has valid CTC<span class="rbim-required">*</span></label>
                                <select v-model="editForm.has_valid_ctc" class="rbim-input">
                                    <option :value="true">Yes</option>
                                    <option :value="false">No</option>
                                </select>
                            </div>
                            <div>
                                <label class="rbim-label">CTC issued here<span class="rbim-required">*</span></label>
                                <select v-model="editForm.ctc_issued_here" class="rbim-input">
                                    <option :value="true">Yes</option>
                                    <option :value="false">No</option>
                                </select>
                            </div>
                        </div>
                    </template>

                    <template v-else-if="editing === 'skills'">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="rbim-label">Skills development training<span class="rbim-required">*</span></label>
                                <input v-model="editForm.skills_development_training" type="text" maxlength="45" class="rbim-input">
                            </div>
                            <div>
                                <label class="rbim-label">Skill type<span class="rbim-required">*</span></label>
                                <select v-model="editForm.skill_type_id" class="rbim-input">
                                    <option value="">Select</option>
                                    <option v-for="option in lookups.skillType" :key="option.id" :value="option.id">{{ option.label }}</option>
                                </select>
                            </div>
                        </div>
                    </template>

                    <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                        <button type="button" class="rbim-btn-outline" :disabled="saving" @click="cancelEdit">Cancel</button>
                        <button type="submit" class="rbim-btn" :disabled="saving">{{ saving ? 'Saving...' : 'Save' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useRoute } from 'vue-router';
import AppLayout from '@/layouts/AppLayout.vue';
import PageTabs from '@/components/PageTabs.vue';
import ResidentDemographicsFields from '@/components/ResidentDemographicsFields.vue';
import { useAuth } from '@/composables/useAuth';
import { useSectionTabs } from '@/composables/useSectionTabs';
import { extractErrorMessage, extractValidationErrors } from '@/services/http';
import * as lookupService from '@/services/lookupService';
import * as residentService from '@/services/residentService';
import { applyValidationErrors } from '@/utils/residentForm';

const route = useRoute();
const { residentTabs } = useSectionTabs();
const { hasPermission } = useAuth();

const loading = ref(false);
const saving = ref(false);
const error = ref('');
const successMessage = ref('');
const editError = ref('');
const resident = ref(null);
const editing = ref(null);
const editForm = reactive({});
const editErrors = reactive({});
const lookups = reactive({
    sex: [],
    relationship: [],
    nationality: [],
    religion: [],
    ethnicity: [],
    maritalStatus: [],
    residentType: [],
    residentStatus: [],
    highestLvlOfEduc: [],
    currentEnrollmentStatus: [],
    schoolLvl: [],
    sourceOfIncome: [],
    statusOfWorkBusiness: [],
    placeOfDelivery: [],
    birthAttendant: [],
    healthInsurance: [],
    facilityVisited: [],
    facilityVisitReason: [],
    familyPlanningMethod: [],
    sourceOfFpMethod: [],
    soloParentStatus: [],
    reasonForLeaving: [],
    reasonForTransfer: [],
    skillType: [],
});

const canUpdateDemographics = computed(() => hasPermission('resident.update'));

const relationshipOptions = computed(() => {
    if (resident.value?.is_household_head) {
        return lookups.relationship;
    }

    return lookups.relationship.filter((option) => Number(option.id) !== 1);
});

const educationEnrolled = computed(() => {
    const status = lookups.currentEnrollmentStatus.find(
        (option) => Number(option.id) === Number(editForm.current_enrollement_status_id),
    );

    return status ? String(status.label).toLowerCase() !== 'no' : true;
});

const editorTitle = computed(() => {
    const titles = {
        demographics: 'Update demographics',
        education: resident.value?.education ? 'Update education' : 'Add education',
        economic: resident.value?.economic ? 'Update economic' : 'Add economic',
        infant_health: resident.value?.infant_health ? 'Update infant health' : 'Add infant health',
        health: resident.value?.health ? 'Update health' : 'Add health',
        women_health: resident.value?.women_health ? 'Update women\'s health' : 'Add women\'s health',
        sociocivic: resident.value?.sociocivic ? 'Update sociocivic' : 'Add sociocivic',
        migration: resident.value?.migration ? 'Update migration' : 'Add migration',
        ctc: resident.value?.ctc ? 'Update community tax certificate' : 'Add community tax certificate',
        skills: resident.value?.skills ? 'Update skills' : 'Add skills',
    };

    return titles[editing.value] || 'Update';
});

const completeness = computed(() => {
    const payload = resident.value?.profiling_completeness;

    return {
        summary: payload?.summary || 'No applicable sections',
        is_complete: Boolean(payload?.is_complete),
        completed: payload?.completed || [],
        missing: payload?.missing || [],
    };
});

const demographicFields = computed(() => {
    const item = resident.value;

    if (!item) {
        return [];
    }

    return [
        { label: 'Full name', value: item.full_name || '—' },
        { label: 'Sex', value: item.sex || '—' },
        { label: 'Date of birth', value: item.date_of_birth || '—' },
        { label: 'Age', value: item.age ?? '—' },
        { label: 'Relationship to household head', value: item.relationship_to_hh || '—' },
        { label: 'Household', value: item.household_id || '—' },
        { label: 'Birth city / municipality', value: item.birth_city_municipality || '—' },
        { label: 'Birth province', value: item.birth_province || '—' },
        { label: 'Birth country', value: item.birth_country || '—' },
        { label: 'Nationality', value: item.nationality || '—' },
        { label: 'Religion', value: item.religion || '—' },
        { label: 'Ethnicity', value: item.ethnicity || '—' },
        { label: 'Marital status', value: item.marital_status || '—' },
        { label: 'Resident type', value: item.resident_type || '—' },
        { label: 'Clan', value: item.clan_name || '—' },
        { label: 'Status', value: item.resident_status || '—' },
    ];
});

const visibleSections = computed(() => {
    const item = resident.value;
    const applicable = item?.applicable_sections || {};

    return [
        {
            key: 'education',
            title: 'Education',
            record: item?.education,
            canCreate: hasPermission('education.create'),
            canUpdate: hasPermission('edcuation.update'),
            fields: displayEducation(item?.education),
        },
        {
            key: 'economic',
            title: 'Economic',
            record: item?.economic,
            canCreate: hasPermission('economic.create'),
            canUpdate: hasPermission('economic.update'),
            fields: displayEconomic(item?.economic),
        },
        {
            key: 'infant_health',
            title: 'Infant health',
            record: item?.infant_health,
            canCreate: hasPermission('infanthealth.create'),
            canUpdate: hasPermission('infanthealth.update'),
            fields: displayInfant(item?.infant_health),
        },
        {
            key: 'health',
            title: 'Health',
            record: item?.health,
            canCreate: hasPermission('health.create'),
            canUpdate: hasPermission('health.update'),
            fields: displayHealth(item?.health),
        },
        {
            key: 'women_health',
            title: 'Women\'s health',
            record: item?.women_health,
            canCreate: hasPermission('womanhealth.create') && Boolean(item?.health),
            canUpdate: hasPermission('womanhealth.update'),
            fields: displayWomenHealth(item?.women_health),
        },
        {
            key: 'sociocivic',
            title: 'Sociocivic',
            record: item?.sociocivic,
            canCreate: hasPermission('sociocivic.create'),
            canUpdate: hasPermission('sociocivic.update'),
            fields: displaySociocivic(item?.sociocivic),
        },
        {
            key: 'migration',
            title: 'Migration',
            record: item?.migration,
            canCreate: hasPermission('migration.create'),
            canUpdate: hasPermission('migration.update'),
            fields: displayMigration(item?.migration),
        },
        {
            key: 'ctc',
            title: 'Community tax certificate',
            record: item?.ctc,
            canCreate: hasPermission('ctc.create'),
            canUpdate: hasPermission('ctc.update'),
            fields: displayCtc(item?.ctc),
        },
        {
            key: 'skills',
            title: 'Skills development',
            record: item?.skills,
            canCreate: hasPermission('skills.create'),
            canUpdate: hasPermission('skills.update'),
            fields: displaySkills(item?.skills),
        },
    ].filter((section) => applicable[section.key] !== false);
});

function yesNo(value) {
    if (value === true) {
        return 'Yes';
    }

    if (value === false) {
        return 'No';
    }

    return '—';
}

function displayEducation(record) {
    if (!record) {
        return [];
    }

    return [
        { label: 'Highest level of education', value: record.highest_lvl_of_educ || '—' },
        { label: 'Enrollment status', value: record.current_enrollment_status || '—' },
        { label: 'School level', value: record.school_lvl || '—' },
        { label: 'School barangay', value: record.place_of_school_brgy || '—' },
        { label: 'School city / municipality', value: record.place_of_school_city_municipality || '—' },
    ];
}

function displayEconomic(record) {
    if (!record) {
        return [];
    }

    return [
        { label: 'Monthly income', value: record.monthly_income ?? '—' },
        { label: 'Source of income', value: record.source_of_income || '—' },
        { label: 'Status of work / business', value: record.status_of_work_business || '—' },
        { label: 'Place of work / business', value: record.place_of_work_business || '—' },
    ];
}

function displayInfant(record) {
    if (!record) {
        return [];
    }

    return [
        { label: 'Place of delivery', value: record.place_of_delivery || '—' },
        { label: 'Birth attendant', value: record.birth_attendant || '—' },
        { label: 'Immunization', value: record.immunization || '—' },
    ];
}

function displayHealth(record) {
    if (!record) {
        return [];
    }

    return [
        { label: 'Health insurance', value: record.health_insurance || '—' },
        { label: 'Facility visited (past 12 months)', value: record.facility_visited_past_12mos || '—' },
        { label: 'Visit reason', value: record.facility_visit_reason || '—' },
        { label: 'Disability', value: record.disability || '—' },
    ];
}

function displayWomenHealth(record) {
    if (!record) {
        return [];
    }

    return [
        { label: 'Living children', value: record.living_children ?? '—' },
        { label: 'Family planning method', value: record.family_planning_method || '—' },
        { label: 'Source of FP method', value: record.source_of_fp_method || '—' },
        { label: 'Intention to use FP', value: yesNo(record.have_intention_to_use_fp) },
    ];
}

function displaySociocivic(record) {
    if (!record) {
        return [];
    }

    return [
        { label: 'Solo parent status', value: record.solo_parent_status || '—' },
        { label: 'Registered senior citizen', value: yesNo(record.registered_sen_citizen) },
        { label: 'Registered barangay voter', value: record.registered_barangay_voter || '—' },
    ];
}

function displayMigration(record) {
    if (!record) {
        return [];
    }

    return [
        { label: 'Previous residence (6 months) barangay', value: record.previous_residence_6mos_brgy || '—' },
        { label: 'Previous residence (6 months) city', value: record.previous_residence_6mos_city_municipality || '—' },
        { label: 'Previous residence (5 years) barangay', value: record.previous_residence_5yrs_brgy || '—' },
        { label: 'Previous residence (5 years) city', value: record.previous_residence_5yrs_city_municipality || '—' },
        { label: 'Date of transfer in barangay', value: record.date_of_transfer_in_brgy || '—' },
        { label: 'Reason for leaving', value: record.reason_for_leaving || '—' },
        { label: 'Will return to previous residence', value: yesNo(record.will_return_to_previous_residence) },
        { label: 'Reason for transfer', value: record.reason_for_transfer || '—' },
        { label: 'Duration of stay', value: record.duration_of_stay || '—' },
    ];
}

function displayCtc(record) {
    if (!record) {
        return [];
    }

    return [
        { label: 'Has valid CTC', value: yesNo(record.has_valid_ctc) },
        { label: 'CTC issued here', value: yesNo(record.ctc_issued_here) },
    ];
}

function displaySkills(record) {
    if (!record) {
        return [];
    }

    return [
        { label: 'Training', value: record.skills_development_training || '—' },
        { label: 'Skill type', value: record.skill_type || '—' },
    ];
}

function resetEditForm(values = {}) {
    Object.keys(editForm).forEach((key) => {
        delete editForm[key];
    });
    Object.keys(editErrors).forEach((key) => {
        delete editErrors[key];
    });
    Object.assign(editForm, values);
    editError.value = '';
}

function startDemographicsEdit() {
    const item = resident.value;
                resetEditForm({
        last_name: item.last_name,
        first_name: item.first_name,
        middle_name: item.middle_name || '',
        suffix: item.suffix || '',
        relationship_to_hh_id: item.relationship_to_hh_id,
        sex_id: item.sex_id,
        date_of_birth: item.date_of_birth,
        birth_city_municipality: item.birth_city_municipality,
        birth_province: item.birth_province,
        birth_country: item.birth_country,
        nationality_id: item.nationality_id,
        nationality_name: item.nationality || '',
        religion_id: item.religion_id,
        religion_name: item.religion || '',
        ethnicity_id: item.ethnicity_id,
        ethnicity_name: item.ethnicity || '',
        marital_status_id: item.marital_status_id,
        resident_type_id: item.resident_type_id,
        resident_status_id: item.resident_status_id,
    });
    editing.value = 'demographics';
}

function startSectionEdit(key) {
    const record = resident.value?.[key] || {};
    const defaults = {
        education: {
            highest_lvl_of_educ_id: record.highest_lvl_of_educ_id || '',
            current_enrollement_status_id: record.current_enrollement_status_id || '',
            school_lvl_id: record.school_lvl_id || '',
            place_of_school_brgy: record.place_of_school_brgy || '',
            place_of_school_city_municipality: record.place_of_school_city_municipality || '',
        },
        economic: {
            monthly_income: record.monthly_income ?? '',
            source_of_income_id: record.source_of_income_id || '',
            status_of_work_business_id: record.status_of_work_business_id || '',
            place_of_work_business: record.place_of_work_business || '',
        },
        infant_health: {
            place_of_delivery_id: record.place_of_delivery_id || '',
            birth_attendant_id: record.birth_attendant_id || '',
            immunization: record.immunization || '',
        },
        health: {
            health_insurance_id: record.health_insurance_id || '',
            facility_visited_past_12mos_id: record.facility_visited_past_12mos_id || '',
            facility_visit_reason_id: record.facility_visit_reason_id || '',
            disability: record.disability || '',
        },
        women_health: {
            living_children: record.living_children ?? '',
            family_planning_method_id: record.family_planning_method_id || '',
            source_of_fp_method_id: record.source_of_fp_method_id || '',
            have_intention_to_use_fp: record.have_intention_to_use_fp ?? false,
        },
        sociocivic: {
            solo_parent_status_id: record.solo_parent_status_id || '',
            registered_sen_citizen: record.registered_sen_citizen ?? false,
            registered_barangay_voter: record.registered_barangay_voter || '',
        },
        migration: {
            previous_residence_6mos_brgy: record.previous_residence_6mos_brgy || '',
            previous_residence_6mos_city_municipality: record.previous_residence_6mos_city_municipality || '',
            previous_residence_5yrs_brgy: record.previous_residence_5yrs_brgy || '',
            previous_residence_5yrs_city_municipality: record.previous_residence_5yrs_city_municipality || '',
            date_of_transfer_in_brgy: record.date_of_transfer_in_brgy || '',
            reason_for_leaving_id: record.reason_for_leaving_id || '',
            will_return_to_previous_residence: record.will_return_to_previous_residence ?? false,
            reason_for_transfer_id: record.reason_for_transfer_id || '',
            duration_of_stay: record.duration_of_stay || '',
        },
        ctc: {
            has_valid_ctc: record.has_valid_ctc ?? false,
            ctc_issued_here: record.ctc_issued_here ?? false,
        },
        skills: {
            skills_development_training: record.skills_development_training || '',
            skill_type_id: record.skill_type_id || '',
        },
    };

    resetEditForm(defaults[key] || {});
    editing.value = key;
}

function cancelEdit() {
    editing.value = null;
    editError.value = '';
}

function toPayload(form) {
    const payload = {};

    Object.entries(form).forEach(([key, value]) => {
        if (value === '') {
            payload[key] = null;
            return;
        }

        payload[key] = value;
    });

    return payload;
}

async function handleSave() {
    saving.value = true;
    editError.value = '';
    Object.keys(editErrors).forEach((key) => {
        delete editErrors[key];
    });

    const id = resident.value.resident_id;
    const payload = toPayload(editForm);

    try {
        if (editing.value === 'demographics') {
            if (resident.value.is_household_head) {
                delete payload.relationship_to_hh_id;
            }

            delete payload.nationality_name;
            delete payload.religion_name;
            delete payload.ethnicity_name;

            await residentService.updateResident(id, payload);
        } else if (editing.value === 'education') {
            if (resident.value.education) {
                await residentService.updateEducation(resident.value.education.education_id, payload);
            } else {
                await residentService.createEducation(id, payload);
            }
        } else if (editing.value === 'economic') {
            if (resident.value.economic) {
                await residentService.updateEconomic(resident.value.economic.economic_id, payload);
            } else {
                await residentService.createEconomic(id, payload);
            }
        } else if (editing.value === 'infant_health') {
            if (resident.value.infant_health) {
                await residentService.updateInfantHealth(resident.value.infant_health.infant_health_id, payload);
            } else {
                await residentService.createInfantHealth(id, payload);
            }
        } else if (editing.value === 'health') {
            if (resident.value.health) {
                await residentService.updateHealth(resident.value.health.health_id, payload);
            } else {
                await residentService.createHealth(id, payload);
            }
        } else if (editing.value === 'women_health') {
            if (resident.value.women_health) {
                await residentService.updateWomenHealth(resident.value.women_health.women_health_id, payload);
            } else {
                await residentService.createWomenHealth(id, payload);
            }
        } else if (editing.value === 'sociocivic') {
            if (resident.value.sociocivic) {
                await residentService.updateSociocivic(resident.value.sociocivic.sociocivic_id, payload);
            } else {
                await residentService.createSociocivic(id, payload);
            }
        } else if (editing.value === 'migration') {
            if (resident.value.migration) {
                await residentService.updateMigration(resident.value.migration.migration_id, payload);
            } else {
                await residentService.createMigration(id, payload);
            }
        } else if (editing.value === 'ctc') {
            if (resident.value.ctc) {
                await residentService.updateCtc(resident.value.ctc.community_tax_cert, payload);
            } else {
                await residentService.createCtc(id, payload);
            }
        } else if (editing.value === 'skills') {
            if (resident.value.skills) {
                await residentService.updateSkills(resident.value.skills.skills_development_id, payload);
            } else {
                await residentService.createSkills(id, payload);
            }
        }

        await loadResident();
        successMessage.value = 'Record saved successfully.';
        cancelEdit();
    } catch (err) {
        const validationErrors = extractValidationErrors(err);

        if (Object.keys(validationErrors).length) {
            applyValidationErrors(editErrors, validationErrors);
        } else {
            editError.value = extractErrorMessage(err, 'Unable to save this record.');
        }
    } finally {
        saving.value = false;
    }
}

async function loadLookups() {
    const [
        sex,
        relationship,
        nationality,
        religion,
        ethnicity,
        maritalStatus,
        residentType,
        residentStatus,
        highestLvlOfEduc,
        currentEnrollmentStatus,
        schoolLvl,
        sourceOfIncome,
        statusOfWorkBusiness,
        placeOfDelivery,
        birthAttendant,
        healthInsurance,
        facilityVisited,
        facilityVisitReason,
        familyPlanningMethod,
        sourceOfFpMethod,
        soloParentStatus,
        reasonForLeaving,
        reasonForTransfer,
        skillType,
    ] = await Promise.all([
        lookupService.fetchLookup('sex'),
        lookupService.fetchLookup('relationship-to-hh'),
        lookupService.fetchNationalities(),
        lookupService.fetchReligions(),
        lookupService.fetchEthnicities(),
        lookupService.fetchLookup('marital-status'),
        lookupService.fetchLookup('resident-type'),
        lookupService.fetchLookup('resident-status'),
        lookupService.fetchLookup('highest-lvl-of-educ'),
        lookupService.fetchLookup('current-enrollment-status'),
        lookupService.fetchLookup('school-lvl'),
        lookupService.fetchLookup('source-of-income'),
        lookupService.fetchLookup('status-of-work-business'),
        lookupService.fetchLookup('place-of-delivery'),
        lookupService.fetchLookup('birth-attendant'),
        lookupService.fetchLookup('health-insurance'),
        lookupService.fetchLookup('facility-visited-past-12mos'),
        lookupService.fetchLookup('facility-visit-reason'),
        lookupService.fetchLookup('family-planning-method'),
        lookupService.fetchLookup('source-of-fp-method'),
        lookupService.fetchLookup('solo-parent-status'),
        lookupService.fetchLookup('reason-for-leaving'),
        lookupService.fetchLookup('reason-for-transfer'),
        lookupService.fetchLookup('skill-type'),
    ]);

    lookups.sex = sex;
    lookups.relationship = relationship;
    lookups.nationality = nationality;
    lookups.religion = religion;
    lookups.ethnicity = ethnicity;
    lookups.maritalStatus = maritalStatus;
    lookups.residentType = residentType;
    lookups.residentStatus = residentStatus;
    lookups.highestLvlOfEduc = highestLvlOfEduc;
    lookups.currentEnrollmentStatus = currentEnrollmentStatus;
    lookups.schoolLvl = schoolLvl;
    lookups.sourceOfIncome = sourceOfIncome;
    lookups.statusOfWorkBusiness = statusOfWorkBusiness;
    lookups.placeOfDelivery = placeOfDelivery;
    lookups.birthAttendant = birthAttendant;
    lookups.healthInsurance = healthInsurance;
    lookups.facilityVisited = facilityVisited;
    lookups.facilityVisitReason = facilityVisitReason;
    lookups.familyPlanningMethod = familyPlanningMethod;
    lookups.sourceOfFpMethod = sourceOfFpMethod;
    lookups.soloParentStatus = soloParentStatus;
    lookups.reasonForLeaving = reasonForLeaving;
    lookups.reasonForTransfer = reasonForTransfer;
    lookups.skillType = skillType;
}

async function loadResident() {
    resident.value = await residentService.fetchResident(route.params.id);
}

onMounted(async () => {
    loading.value = true;

    try {
        await Promise.all([loadLookups(), loadResident()]);
    } catch (err) {
        error.value = extractErrorMessage(err, 'Unable to load resident.');
    } finally {
        loading.value = false;
    }
});
</script>

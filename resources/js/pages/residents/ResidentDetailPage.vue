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
                    Update
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
                            <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">Profile Completeness</h2>
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
                </article>

                <article class="rbim-card p-6">
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">Resident Information</h2>
                    <dl class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div v-for="field in demographicFields" :key="field.label">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ field.label }}</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ field.value }}</dd>
                        </div>
                    </dl>
                </article>

                <nav v-if="visibleSections.length" class="flex flex-wrap gap-2">
                    <button
                        v-for="section in visibleSections"
                        :key="`tab-${section.key}`"
                        type="button"
                        class="rounded-lg px-3 py-2 text-sm font-medium transition"
                        :class="activeSection === section.key
                            ? 'bg-brand text-white'
                            : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50'"
                        @click="scrollToSection(section.key)"
                    >
                        {{ section.title }}
                        <span
                            v-if="!section.record"
                            class="ml-1 rounded-full px-1.5 py-0.5 text-[10px]"
                            :class="activeSection === section.key ? 'bg-white/20 text-white' : 'bg-amber-50 text-amber-800'"
                        >
                            Missing
                        </span>
                    </button>
                </nav>

                <article
                    v-for="section in visibleSections"
                    :id="`resident-section-${section.key}`"
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
                            v-else-if="!section.record && section.canCreate && section.createBlockedReason"
                            type="button"
                            class="rbim-btn-action"
                            disabled
                            :title="section.createBlockedReason"
                        >
                            Add {{ section.title }}
                        </button>
                        <button
                            v-else-if="!section.record && section.canCreate"
                            type="button"
                            class="rbim-btn-action"
                            @click="startSectionEdit(section.key)"
                        >
                            Add {{ section.title }}
                        </button>
                    </div>
                    <p v-if="section.createBlockedReason && !section.record" class="mt-4 text-sm text-slate-500">
                        {{ section.createBlockedReason }}
                    </p>
                    <p v-else-if="!section.record" class="mt-4 text-sm text-slate-500">
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
                            :min-age="resident?.is_household_head ? HOUSEHOLD_HEAD_MIN_AGE : 0"
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
                            <p v-if="editErrors.resident_status_id" class="rbim-error">{{ editErrors.resident_status_id }}</p>
                        </div>
                    </template>

                    <template v-else-if="editing === 'education'">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="rbim-label">Highest Level of Education<span class="rbim-required" aria-hidden="true">*</span></label>
                                <select
                                    v-model="editForm.highest_lvl_of_educ_id"
                                    class="rbim-input"
                                    :class="{ 'rbim-input-error': editErrors.highest_lvl_of_educ_id }"
                                >
                                    <option value="">Select</option>
                                    <option v-for="option in lookups.highestLvlOfEduc" :key="option.id" :value="option.id">{{ option.label }}</option>
                                </select>
                                <p v-if="editErrors.highest_lvl_of_educ_id" class="rbim-error">{{ editErrors.highest_lvl_of_educ_id }}</p>
                            </div>
                            <div>
                                <label class="rbim-label">Current Enrollment Status<span class="rbim-required" aria-hidden="true">*</span></label>
                                <select
                                    v-model="editForm.current_enrollement_status_id"
                                    class="rbim-input"
                                    :class="{ 'rbim-input-error': editErrors.current_enrollement_status_id }"
                                >
                                    <option value="">Select</option>
                                    <option v-for="option in lookups.currentEnrollmentStatus" :key="option.id" :value="option.id">{{ option.label }}</option>
                                </select>
                                <p v-if="editErrors.current_enrollement_status_id" class="rbim-error">{{ editErrors.current_enrollement_status_id }}</p>
                            </div>
                            <template v-if="educationEnrolled">
                                <div>
                                    <label class="rbim-label">School Level<span class="rbim-required" aria-hidden="true">*</span></label>
                                    <select
                                        v-model="editForm.school_lvl_id"
                                        class="rbim-input"
                                        :class="{ 'rbim-input-error': editErrors.school_lvl_id }"
                                    >
                                        <option value="">Select</option>
                                        <option v-for="option in lookups.schoolLvl" :key="option.id" :value="option.id">{{ option.label }}</option>
                                    </select>
                                    <p v-if="editErrors.school_lvl_id" class="rbim-error">{{ editErrors.school_lvl_id }}</p>
                                </div>
                                <div>
                                    <label class="rbim-label">School Barangay<span class="rbim-required" aria-hidden="true">*</span></label>
                                    <input
                                        v-model="editForm.place_of_school_brgy"
                                        type="text"
                                        maxlength="45"
                                        class="rbim-input"
                                        :class="{ 'rbim-input-error': editErrors.place_of_school_brgy }"
                                    >
                                    <p v-if="editErrors.place_of_school_brgy" class="rbim-error">{{ editErrors.place_of_school_brgy }}</p>
                                </div>
                                <div>
                                    <label class="rbim-label">School City / Municipality<span class="rbim-required" aria-hidden="true">*</span></label>
                                    <input
                                        v-model="editForm.place_of_school_city_municipality"
                                        type="text"
                                        maxlength="45"
                                        class="rbim-input"
                                        :class="{ 'rbim-input-error': editErrors.place_of_school_city_municipality }"
                                    >
                                    <p v-if="editErrors.place_of_school_city_municipality" class="rbim-error">{{ editErrors.place_of_school_city_municipality }}</p>
                                </div>
                            </template>
                        </div>
                    </template>

                    <template v-else-if="editing === 'economic'">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="rbim-label">Monthly Income<span class="rbim-required" aria-hidden="true">*</span></label>
                                <input
                                    v-model="editForm.monthly_income"
                                    type="number"
                                    min="0"
                                    class="rbim-input"
                                    :class="{ 'rbim-input-error': editErrors.monthly_income }"
                                >
                                <p v-if="editErrors.monthly_income" class="rbim-error">{{ editErrors.monthly_income }}</p>
                            </div>
                            <div>
                                <label class="rbim-label">Source of Income<span class="rbim-required" aria-hidden="true">*</span></label>
                                <select
                                    v-model="editForm.source_of_income_id"
                                    class="rbim-input"
                                    :class="{ 'rbim-input-error': editErrors.source_of_income_id }"
                                >
                                    <option value="">Select</option>
                                    <option v-for="option in lookups.sourceOfIncome" :key="option.id" :value="option.id">{{ option.label }}</option>
                                </select>
                                <p v-if="editErrors.source_of_income_id" class="rbim-error">{{ editErrors.source_of_income_id }}</p>
                            </div>
                            <div>
                                <label class="rbim-label">Status of Work / Business<span class="rbim-required" aria-hidden="true">*</span></label>
                                <select
                                    v-model="editForm.status_of_work_business_id"
                                    class="rbim-input"
                                    :class="{ 'rbim-input-error': editErrors.status_of_work_business_id }"
                                >
                                    <option value="">Select</option>
                                    <option v-for="option in lookups.statusOfWorkBusiness" :key="option.id" :value="option.id">{{ option.label }}</option>
                                </select>
                                <p v-if="editErrors.status_of_work_business_id" class="rbim-error">{{ editErrors.status_of_work_business_id }}</p>
                            </div>
                            <div v-if="economicActiveWork">
                                <label class="rbim-label">Place of Work / Business<span class="rbim-required" aria-hidden="true">*</span></label>
                                <input
                                    v-model="editForm.place_of_work_business"
                                    type="text"
                                    maxlength="45"
                                    class="rbim-input"
                                    :class="{ 'rbim-input-error': editErrors.place_of_work_business }"
                                >
                                <p v-if="editErrors.place_of_work_business" class="rbim-error">{{ editErrors.place_of_work_business }}</p>
                            </div>
                        </div>
                    </template>

                    <template v-else-if="editing === 'infant_health'">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="rbim-label">Place of Delivery<span class="rbim-required" aria-hidden="true">*</span></label>
                                <select
                                    v-model="editForm.place_of_delivery_id"
                                    class="rbim-input"
                                    :class="{ 'rbim-input-error': editErrors.place_of_delivery_id }"
                                >
                                    <option value="">Select</option>
                                    <option v-for="option in lookups.placeOfDelivery" :key="option.id" :value="option.id">{{ option.label }}</option>
                                </select>
                                <p v-if="editErrors.place_of_delivery_id" class="rbim-error">{{ editErrors.place_of_delivery_id }}</p>
                            </div>
                            <div>
                                <label class="rbim-label">Birth Attendant<span class="rbim-required" aria-hidden="true">*</span></label>
                                <select
                                    v-model="editForm.birth_attendant_id"
                                    class="rbim-input"
                                    :class="{ 'rbim-input-error': editErrors.birth_attendant_id }"
                                >
                                    <option value="">Select</option>
                                    <option v-for="option in lookups.birthAttendant" :key="option.id" :value="option.id">{{ option.label }}</option>
                                </select>
                                <p v-if="editErrors.birth_attendant_id" class="rbim-error">{{ editErrors.birth_attendant_id }}</p>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="rbim-label">Immunization<span class="rbim-required" aria-hidden="true">*</span></label>
                                <input
                                    v-model="editForm.immunization"
                                    type="text"
                                    maxlength="45"
                                    class="rbim-input"
                                    :class="{ 'rbim-input-error': editErrors.immunization }"
                                >
                                <p v-if="editErrors.immunization" class="rbim-error">{{ editErrors.immunization }}</p>
                            </div>
                        </div>
                    </template>

                    <template v-else-if="editing === 'health'">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="rbim-label">Health Insurance<span class="rbim-required" aria-hidden="true">*</span></label>
                                <select
                                    v-model="editForm.health_insurance_id"
                                    class="rbim-input"
                                    :class="{ 'rbim-input-error': editErrors.health_insurance_id }"
                                >
                                    <option value="">Select</option>
                                    <option v-for="option in lookups.healthInsurance" :key="option.id" :value="option.id">{{ option.label }}</option>
                                </select>
                                <p v-if="editErrors.health_insurance_id" class="rbim-error">{{ editErrors.health_insurance_id }}</p>
                            </div>
                            <div>
                                <label class="rbim-label">Facility Visited Past 12 Months<span class="rbim-required" aria-hidden="true">*</span></label>
                                <select
                                    v-model="editForm.facility_visited_past_12mos_id"
                                    class="rbim-input"
                                    :class="{ 'rbim-input-error': editErrors.facility_visited_past_12mos_id }"
                                >
                                    <option value="">Select</option>
                                    <option v-for="option in lookups.facilityVisited" :key="option.id" :value="option.id">{{ option.label }}</option>
                                </select>
                                <p v-if="editErrors.facility_visited_past_12mos_id" class="rbim-error">{{ editErrors.facility_visited_past_12mos_id }}</p>
                            </div>
                            <div>
                                <label class="rbim-label">Facility Visit Reason<span class="rbim-required" aria-hidden="true">*</span></label>
                                <select
                                    v-model="editForm.facility_visit_reason_id"
                                    class="rbim-input"
                                    :class="{ 'rbim-input-error': editErrors.facility_visit_reason_id }"
                                >
                                    <option value="">Select</option>
                                    <option v-for="option in lookups.facilityVisitReason" :key="option.id" :value="option.id">{{ option.label }}</option>
                                </select>
                                <p v-if="editErrors.facility_visit_reason_id" class="rbim-error">{{ editErrors.facility_visit_reason_id }}</p>
                            </div>
                            <div>
                                <label class="rbim-label">Disability</label>
                                <input
                                    v-model="editForm.disability"
                                    type="text"
                                    maxlength="45"
                                    class="rbim-input"
                                    :class="{ 'rbim-input-error': editErrors.disability }"
                                >
                                <p v-if="editErrors.disability" class="rbim-error">{{ editErrors.disability }}</p>
                            </div>
                        </div>
                    </template>

                    <template v-else-if="editing === 'women_health'">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="rbim-label">Living Children<span class="rbim-required" aria-hidden="true">*</span></label>
                                <input
                                    v-model="editForm.living_children"
                                    type="number"
                                    min="0"
                                    class="rbim-input"
                                    :class="{ 'rbim-input-error': editErrors.living_children }"
                                >
                                <p v-if="editErrors.living_children" class="rbim-error">{{ editErrors.living_children }}</p>
                            </div>
                            <div>
                                <label class="rbim-label">Family Planning Method<span class="rbim-required" aria-hidden="true">*</span></label>
                                <select
                                    v-model="editForm.family_planning_method_id"
                                    class="rbim-input"
                                    :class="{ 'rbim-input-error': editErrors.family_planning_method_id }"
                                >
                                    <option value="">Select</option>
                                    <option v-for="option in lookups.familyPlanningMethod" :key="option.id" :value="option.id">{{ option.label }}</option>
                                </select>
                                <p v-if="editErrors.family_planning_method_id" class="rbim-error">{{ editErrors.family_planning_method_id }}</p>
                            </div>
                            <template v-if="!familyPlanningIsNone">
                                <div>
                                    <label class="rbim-label">Source of Family Planning Method<span class="rbim-required" aria-hidden="true">*</span></label>
                                    <select
                                        v-model="editForm.source_of_fp_method_id"
                                        class="rbim-input"
                                        :class="{ 'rbim-input-error': editErrors.source_of_fp_method_id }"
                                    >
                                        <option value="">Select</option>
                                        <option v-for="option in lookups.sourceOfFpMethod" :key="option.id" :value="option.id">{{ option.label }}</option>
                                    </select>
                                    <p v-if="editErrors.source_of_fp_method_id" class="rbim-error">{{ editErrors.source_of_fp_method_id }}</p>
                                </div>
                                <div>
                                    <label class="rbim-label">Intention to Use Family Planning<span class="rbim-required" aria-hidden="true">*</span></label>
                                    <select
                                        v-model="editForm.have_intention_to_use_fp"
                                        class="rbim-input"
                                        :class="{ 'rbim-input-error': editErrors.have_intention_to_use_fp }"
                                    >
                                        <option :value="true">Yes</option>
                                        <option :value="false">No</option>
                                    </select>
                                    <p v-if="editErrors.have_intention_to_use_fp" class="rbim-error">{{ editErrors.have_intention_to_use_fp }}</p>
                                </div>
                            </template>
                        </div>
                    </template>

                    <template v-else-if="editing === 'sociocivic'">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div v-if="sociocivicRelevance.solo_parent">
                                <label class="rbim-label">Solo Parent Status<span class="rbim-required" aria-hidden="true">*</span></label>
                                <select
                                    v-model="editForm.solo_parent_status_id"
                                    class="rbim-input"
                                    :class="{ 'rbim-input-error': editErrors.solo_parent_status_id }"
                                >
                                    <option value="">Select</option>
                                    <option v-for="option in lookups.soloParentStatus" :key="option.id" :value="option.id">{{ option.label }}</option>
                                </select>
                                <p v-if="editErrors.solo_parent_status_id" class="rbim-error">{{ editErrors.solo_parent_status_id }}</p>
                            </div>
                            <div v-if="sociocivicRelevance.senior_citizen">
                                <label class="rbim-label">Registered Senior Citizen<span class="rbim-required" aria-hidden="true">*</span></label>
                                <select
                                    v-model="editForm.registered_sen_citizen"
                                    class="rbim-input"
                                    :class="{ 'rbim-input-error': editErrors.registered_sen_citizen }"
                                >
                                    <option :value="true">Yes</option>
                                    <option :value="false">No</option>
                                </select>
                                <p v-if="editErrors.registered_sen_citizen" class="rbim-error">{{ editErrors.registered_sen_citizen }}</p>
                            </div>
                            <div v-if="sociocivicRelevance.barangay_voter">
                                <label class="rbim-label">Registered Barangay Voter<span class="rbim-required" aria-hidden="true">*</span></label>
                                <select
                                    v-model="editForm.registered_barangay_voter"
                                    class="rbim-input"
                                    :class="{ 'rbim-input-error': editErrors.registered_barangay_voter }"
                                >
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                                <p v-if="editErrors.registered_barangay_voter" class="rbim-error">{{ editErrors.registered_barangay_voter }}</p>
                            </div>
                            <p
                                v-if="!sociocivicRelevance.solo_parent && !sociocivicRelevance.senior_citizen && !sociocivicRelevance.barangay_voter"
                                class="sm:col-span-2 text-sm text-slate-500"
                            >
                                No sociocivic fields apply at this resident's current age. Saving will store the age-normalized defaults.
                            </p>
                        </div>
                    </template>

                    <template v-else-if="editing === 'migration'">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="rbim-label">Previous Residence 6 Months Barangay</label>
                                <input
                                    v-model="editForm.previous_residence_6mos_brgy"
                                    type="text"
                                    maxlength="45"
                                    class="rbim-input"
                                    :class="{ 'rbim-input-error': editErrors.previous_residence_6mos_brgy }"
                                >
                                <p v-if="editErrors.previous_residence_6mos_brgy" class="rbim-error">{{ editErrors.previous_residence_6mos_brgy }}</p>
                            </div>
                            <div>
                                <label class="rbim-label">Previous Residence 6 Months City / Municipality</label>
                                <input
                                    v-model="editForm.previous_residence_6mos_city_municipality"
                                    type="text"
                                    maxlength="45"
                                    class="rbim-input"
                                    :class="{ 'rbim-input-error': editErrors.previous_residence_6mos_city_municipality }"
                                >
                                <p v-if="editErrors.previous_residence_6mos_city_municipality" class="rbim-error">{{ editErrors.previous_residence_6mos_city_municipality }}</p>
                            </div>
                            <div>
                                <label class="rbim-label">Previous Residence 5 Years Barangay</label>
                                <input
                                    v-model="editForm.previous_residence_5yrs_brgy"
                                    type="text"
                                    maxlength="45"
                                    class="rbim-input"
                                    :class="{ 'rbim-input-error': editErrors.previous_residence_5yrs_brgy }"
                                >
                                <p v-if="editErrors.previous_residence_5yrs_brgy" class="rbim-error">{{ editErrors.previous_residence_5yrs_brgy }}</p>
                            </div>
                            <div>
                                <label class="rbim-label">Previous Residence 5 Years City / Municipality</label>
                                <input
                                    v-model="editForm.previous_residence_5yrs_city_municipality"
                                    type="text"
                                    maxlength="45"
                                    class="rbim-input"
                                    :class="{ 'rbim-input-error': editErrors.previous_residence_5yrs_city_municipality }"
                                >
                                <p v-if="editErrors.previous_residence_5yrs_city_municipality" class="rbim-error">{{ editErrors.previous_residence_5yrs_city_municipality }}</p>
                            </div>
                            <div>
                                <BirthDateField
                                    v-model="editForm.date_of_transfer_in_brgy"
                                    label="Date of Transfer into Barangay"
                                    input-id="migration-transfer-date"
                                    placeholder="Select date"
                                    :max="todayIso"
                                    :show-age="false"
                                    :error="editErrors.date_of_transfer_in_brgy"
                                />
                            </div>
                            <div>
                                <BirthDateField
                                    v-model="editForm.duration_of_stay"
                                    label="Duration of Stay"
                                    input-id="migration-duration-of-stay"
                                    placeholder="Select date"
                                    :show-age="false"
                                    :error="editErrors.duration_of_stay"
                                />
                            </div>
                            <div>
                                <label class="rbim-label">Reason for Leaving<span class="rbim-required" aria-hidden="true">*</span></label>
                                <select
                                    v-model="editForm.reason_for_leaving_id"
                                    class="rbim-input"
                                    :class="{ 'rbim-input-error': editErrors.reason_for_leaving_id }"
                                >
                                    <option value="">Select</option>
                                    <option v-for="option in lookups.reasonForLeaving" :key="option.id" :value="option.id">{{ option.label }}</option>
                                </select>
                                <p v-if="editErrors.reason_for_leaving_id" class="rbim-error">{{ editErrors.reason_for_leaving_id }}</p>
                            </div>
                            <div>
                                <label class="rbim-label">Reason for Transfer<span class="rbim-required" aria-hidden="true">*</span></label>
                                <select
                                    v-model="editForm.reason_for_transfer_id"
                                    class="rbim-input"
                                    :class="{ 'rbim-input-error': editErrors.reason_for_transfer_id }"
                                >
                                    <option value="">Select</option>
                                    <option v-for="option in lookups.reasonForTransfer" :key="option.id" :value="option.id">{{ option.label }}</option>
                                </select>
                                <p v-if="editErrors.reason_for_transfer_id" class="rbim-error">{{ editErrors.reason_for_transfer_id }}</p>
                            </div>
                            <div>
                                <label class="rbim-label">Will Return to Previous Residence<span class="rbim-required" aria-hidden="true">*</span></label>
                                <select
                                    v-model="editForm.will_return_to_previous_residence"
                                    class="rbim-input"
                                    :class="{ 'rbim-input-error': editErrors.will_return_to_previous_residence }"
                                >
                                    <option :value="true">Yes</option>
                                    <option :value="false">No</option>
                                </select>
                                <p v-if="editErrors.will_return_to_previous_residence" class="rbim-error">{{ editErrors.will_return_to_previous_residence }}</p>
                            </div>
                        </div>
                    </template>

                    <template v-else-if="editing === 'ctc'">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="rbim-label">Has Valid Community Tax Certificate<span class="rbim-required" aria-hidden="true">*</span></label>
                                <select
                                    v-model="editForm.has_valid_ctc"
                                    class="rbim-input"
                                    :class="{ 'rbim-input-error': editErrors.has_valid_ctc }"
                                >
                                    <option :value="true">Yes</option>
                                    <option :value="false">No</option>
                                </select>
                                <p v-if="editErrors.has_valid_ctc" class="rbim-error">{{ editErrors.has_valid_ctc }}</p>
                            </div>
                            <div>
                                <label class="rbim-label">Community Tax Certificate Issued Here<span class="rbim-required" aria-hidden="true">*</span></label>
                                <select
                                    v-model="editForm.ctc_issued_here"
                                    class="rbim-input"
                                    :class="{ 'rbim-input-error': editErrors.ctc_issued_here }"
                                >
                                    <option :value="true">Yes</option>
                                    <option :value="false">No</option>
                                </select>
                                <p v-if="editErrors.ctc_issued_here" class="rbim-error">{{ editErrors.ctc_issued_here }}</p>
                            </div>
                        </div>
                    </template>

                    <template v-else-if="editing === 'skills'">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="rbim-label">Skills Development Training<span class="rbim-required" aria-hidden="true">*</span></label>
                                <input
                                    v-model="editForm.skills_development_training"
                                    type="text"
                                    maxlength="45"
                                    class="rbim-input"
                                    :class="{ 'rbim-input-error': editErrors.skills_development_training }"
                                >
                                <p v-if="editErrors.skills_development_training" class="rbim-error">{{ editErrors.skills_development_training }}</p>
                            </div>
                            <div>
                                <label class="rbim-label">Skill Type<span class="rbim-required" aria-hidden="true">*</span></label>
                                <select
                                    v-model="editForm.skill_type_id"
                                    class="rbim-input"
                                    :class="{ 'rbim-input-error': editErrors.skill_type_id }"
                                >
                                    <option value="">Select</option>
                                    <option v-for="option in lookups.skillType" :key="option.id" :value="option.id">{{ option.label }}</option>
                                </select>
                                <p v-if="editErrors.skill_type_id" class="rbim-error">{{ editErrors.skill_type_id }}</p>
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
import { useRoute } from 'vue-router';
import AppLayout from '@/layouts/AppLayout.vue';
import BirthDateField from '@/components/BirthDateField.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import PageTabs from '@/components/PageTabs.vue';
import ResidentDemographicsFields from '@/components/ResidentDemographicsFields.vue';
import { useAuth } from '@/composables/useAuth';
import { useSectionTabs } from '@/composables/useSectionTabs';
import { extractErrorMessage, extractValidationErrors } from '@/services/http';
import * as lookupService from '@/services/lookupService';
import * as residentService from '@/services/residentService';
import { HOUSEHOLD_HEAD_MIN_AGE, applyValidationErrors } from '@/utils/residentForm';
import { ageFromDateOfBirth } from '@/utils/format';
import {
    canPerformCreate,
    canPerformUpdate,
    isActiveWorkStatus,
    isEnrollmentStatusEnrolled,
    isFamilyPlanningNone,
    lookupById,
    NON_SOLO_PARENT_STATUS_ID,
    resolveApplicableSections,
    sociocivicFieldRelevance,
    titleCaseWords,
} from '@/utils/residentProfiling';

const route = useRoute();
const { residentTabs } = useSectionTabs();
const auth = useAuth();

const loading = ref(false);
const saving = ref(false);
const error = ref('');
const successMessage = ref('');
const editError = ref('');
const resident = ref(null);
const editing = ref(null);
const activeSection = ref('');
const editForm = reactive({});
const editErrors = reactive({});
const todayIso = new Date().toISOString().slice(0, 10);
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
const confirm = reactive({
    open: false,
    title: '',
    message: '',
    confirmLabel: 'Confirm',
    variant: 'primary',
    onConfirm: null,
    onCancel: null,
});

const canUpdateDemographics = computed(() => canPerformUpdate(auth, 'resident.update'));

const relationshipOptions = computed(() => {
    if (resident.value?.is_household_head) {
        return lookups.relationship;
    }

    return lookups.relationship.filter((option) => Number(option.id) !== 1);
});

const educationEnrolled = computed(() => (
    isEnrollmentStatusEnrolled(lookupById(lookups.currentEnrollmentStatus, editForm.current_enrollement_status_id))
));

const economicActiveWork = computed(() => (
    isActiveWorkStatus(lookupById(lookups.statusOfWorkBusiness, editForm.status_of_work_business_id))
));

const familyPlanningIsNone = computed(() => (
    isFamilyPlanningNone(lookupById(lookups.familyPlanningMethod, editForm.family_planning_method_id))
));

const sociocivicRelevance = computed(() => sociocivicFieldRelevance(resident.value));

const editorTitle = computed(() => {
    const titles = {
        demographics: 'Update resident information',
        education: resident.value?.education ? 'Update Education' : 'Add Education',
        economic: resident.value?.economic ? 'Update Economic' : 'Add Economic',
        infant_health: resident.value?.infant_health ? 'Update Infant Health' : 'Add Infant Health',
        health: resident.value?.health ? 'Update Health' : 'Add Health',
        women_health: resident.value?.women_health ? 'Update Women\'s Health' : 'Add Women\'s Health',
        sociocivic: resident.value?.sociocivic ? 'Update Sociocivic' : 'Add Sociocivic',
        migration: resident.value?.migration ? 'Update Migration' : 'Add Migration',
        ctc: resident.value?.ctc ? 'Update Community Tax Certificate' : 'Add Community Tax Certificate',
        skills: resident.value?.skills ? 'Update Skills' : 'Add Skills',
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
        { label: 'Full Name', value: item.full_name || '—' },
        { label: 'Sex', value: item.sex || '—' },
        { label: 'Date of Birth', value: item.date_of_birth || '—' },
        { label: 'Age', value: item.age ?? '—' },
        { label: 'Relationship to Household Head', value: item.relationship_to_hh || '—' },
        { label: 'Household', value: item.household_id || '—' },
        { label: 'Birth City / Municipality', value: item.birth_city_municipality || '—' },
        { label: 'Birth Province', value: item.birth_province || '—' },
        { label: 'Birth Country', value: item.birth_country || '—' },
        { label: 'Nationality', value: item.nationality || '—' },
        { label: 'Religion', value: item.religion || '—' },
        { label: 'Ethnicity', value: item.ethnicity || '—' },
        { label: 'Marital Status', value: item.marital_status || '—' },
        { label: 'Resident Type', value: item.resident_type || '—' },
        { label: 'Clan', value: item.clan_name || '—' },
        { label: 'Resident Status', value: item.resident_status || '—' },
    ];
});

const visibleSections = computed(() => {
    const item = resident.value;
    const applicable = resolveApplicableSections(item);

    return [
        {
            key: 'education',
            title: 'Education',
            record: item?.education,
            canCreate: canPerformCreate(auth, 'education.create'),
            canUpdate: canPerformUpdate(auth, ['edcuation.update', 'education.update']),
            fields: displayEducation(item?.education),
        },
        {
            key: 'economic',
            title: 'Economic',
            record: item?.economic,
            canCreate: canPerformCreate(auth, 'economic.create'),
            canUpdate: canPerformUpdate(auth, 'economic.update'),
            fields: displayEconomic(item?.economic),
        },
        {
            key: 'infant_health',
            title: 'Infant Health',
            record: item?.infant_health,
            canCreate: canPerformCreate(auth, 'infanthealth.create'),
            canUpdate: canPerformUpdate(auth, 'infanthealth.update'),
            fields: displayInfant(item?.infant_health),
        },
        {
            key: 'health',
            title: 'Health',
            record: item?.health,
            canCreate: canPerformCreate(auth, 'health.create'),
            canUpdate: canPerformUpdate(auth, 'health.update'),
            fields: displayHealth(item?.health),
        },
        {
            key: 'women_health',
            title: 'Women\'s Health',
            record: item?.women_health,
            canCreate: canPerformCreate(auth, 'womanhealth.create'),
            canUpdate: canPerformUpdate(auth, 'womanhealth.update'),
            createBlockedReason: item?.health
                ? ''
                : 'Add a Health record first before adding Women\'s Health.',
            fields: displayWomenHealth(item?.women_health),
        },
        {
            key: 'sociocivic',
            title: 'Sociocivic',
            record: item?.sociocivic,
            canCreate: canPerformCreate(auth, 'sociocivic.create'),
            canUpdate: canPerformUpdate(auth, 'sociocivic.update'),
            fields: displaySociocivic(item?.sociocivic),
        },
        {
            key: 'migration',
            title: 'Migration',
            record: item?.migration,
            canCreate: canPerformCreate(auth, 'migration.create'),
            canUpdate: canPerformUpdate(auth, 'migration.update'),
            fields: displayMigration(item?.migration),
        },
        {
            key: 'ctc',
            title: 'Community Tax Certificate',
            record: item?.ctc,
            canCreate: canPerformCreate(auth, 'ctc.create'),
            canUpdate: canPerformUpdate(auth, 'ctc.update'),
            fields: displayCtc(item?.ctc),
        },
        {
            key: 'skills',
            title: 'Skills',
            record: item?.skills,
            canCreate: canPerformCreate(auth, 'skills.create'),
            canUpdate: canPerformUpdate(auth, 'skills.update'),
            fields: displaySkills(item?.skills),
        },
    ].filter((section) => applicable[section.key] === true);
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
        { label: 'Highest Level of Education', value: record.highest_lvl_of_educ || '—' },
        { label: 'Current Enrollment Status', value: record.current_enrollment_status || '—' },
        { label: 'School Level', value: record.school_lvl || '—' },
        { label: 'School Barangay', value: record.place_of_school_brgy || '—' },
        { label: 'School City / Municipality', value: record.place_of_school_city_municipality || '—' },
    ];
}

function displayEconomic(record) {
    if (!record) {
        return [];
    }

    return [
        { label: 'Monthly Income', value: record.monthly_income ?? '—' },
        { label: 'Source of Income', value: record.source_of_income || '—' },
        { label: 'Status of Work / Business', value: record.status_of_work_business || '—' },
        { label: 'Place of Work / Business', value: record.place_of_work_business || '—' },
    ];
}

function displayInfant(record) {
    if (!record) {
        return [];
    }

    return [
        { label: 'Place of Delivery', value: record.place_of_delivery || '—' },
        { label: 'Birth Attendant', value: record.birth_attendant || '—' },
        { label: 'Immunization', value: record.immunization || '—' },
    ];
}

function displayHealth(record) {
    if (!record) {
        return [];
    }

    return [
        { label: 'Health Insurance', value: record.health_insurance || '—' },
        { label: 'Facility Visited Past 12 Months', value: record.facility_visited_past_12mos || '—' },
        { label: 'Facility Visit Reason', value: record.facility_visit_reason || '—' },
        { label: 'Disability', value: record.disability || '—' },
    ];
}

function displayWomenHealth(record) {
    if (!record) {
        return [];
    }

    return [
        { label: 'Living Children', value: record.living_children ?? '—' },
        { label: 'Family Planning Method', value: record.family_planning_method || '—' },
        { label: 'Source of Family Planning Method', value: record.source_of_fp_method || '—' },
        { label: 'Intention to Use Family Planning', value: yesNo(record.have_intention_to_use_fp) },
    ];
}

function displaySociocivic(record) {
    if (!record) {
        return [];
    }

    const relevance = sociocivicFieldRelevance(resident.value);
    const fields = [];

    if (relevance.solo_parent) {
        fields.push({ label: 'Solo Parent Status', value: record.solo_parent_status || '—' });
    }

    if (relevance.senior_citizen) {
        fields.push({ label: 'Registered Senior Citizen', value: yesNo(record.registered_sen_citizen) });
    }

    if (relevance.barangay_voter) {
        fields.push({ label: 'Registered Barangay Voter', value: record.registered_barangay_voter || '—' });
    }

    return fields;
}

function displayMigration(record) {
    if (!record) {
        return [];
    }

    return [
        { label: 'Previous Residence 6 Months Barangay', value: record.previous_residence_6mos_brgy || '—' },
        { label: 'Previous Residence 6 Months City / Municipality', value: record.previous_residence_6mos_city_municipality || '—' },
        { label: 'Previous Residence 5 Years Barangay', value: record.previous_residence_5yrs_brgy || '—' },
        { label: 'Previous Residence 5 Years City / Municipality', value: record.previous_residence_5yrs_city_municipality || '—' },
        { label: 'Date of Transfer into Barangay', value: record.date_of_transfer_in_brgy || '—' },
        { label: 'Reason for Leaving', value: record.reason_for_leaving || '—' },
        { label: 'Will Return to Previous Residence', value: yesNo(record.will_return_to_previous_residence) },
        { label: 'Reason for Transfer', value: record.reason_for_transfer || '—' },
        { label: 'Duration of Stay', value: record.duration_of_stay || '—' },
    ];
}

function displayCtc(record) {
    if (!record) {
        return [];
    }

    return [
        { label: 'Has Valid Community Tax Certificate', value: yesNo(record.has_valid_ctc) },
        { label: 'Community Tax Certificate Issued Here', value: yesNo(record.ctc_issued_here) },
    ];
}

function displaySkills(record) {
    if (!record) {
        return [];
    }

    return [
        { label: 'Skills Development Training', value: record.skills_development_training || '—' },
        { label: 'Skill Type', value: record.skill_type || '—' },
    ];
}

function scrollToSection(key) {
    activeSection.value = key;
    document.getElementById(`resident-section-${key}`)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
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
    const voterValue = record.registered_barangay_voter;

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
            registered_barangay_voter: voterValue === 'Yes' || voterValue === 'No' ? voterValue : (voterValue || ''),
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

function firstLookupId(options) {
    return options[0]?.id ?? null;
}

function nonSoloParentId() {
    const match = lookups.soloParentStatus.find((option) => /non[- ]solo/i.test(String(option.label ?? '')));

    return match?.id ?? NON_SOLO_PARENT_STATUS_ID;
}

function preparePayload() {
    const payload = toPayload(editForm);

    if (editing.value === 'education' && !educationEnrolled.value) {
        payload.place_of_school_brgy = null;
        payload.place_of_school_city_municipality = null;
        payload.school_lvl_id = payload.school_lvl_id || firstLookupId(lookups.schoolLvl);
    }

    if (editing.value === 'economic' && !economicActiveWork.value) {
        payload.place_of_work_business = null;
    }

    if (editing.value === 'women_health' && familyPlanningIsNone.value) {
        payload.have_intention_to_use_fp = false;
        payload.source_of_fp_method_id = payload.source_of_fp_method_id || firstLookupId(lookups.sourceOfFpMethod);
    }

    if (editing.value === 'sociocivic') {
        const relevance = sociocivicRelevance.value;

        if (!relevance.solo_parent) {
            payload.solo_parent_status_id = nonSoloParentId();
        }

        if (!relevance.senior_citizen) {
            payload.registered_sen_citizen = false;
        }

        if (!relevance.barangay_voter) {
            payload.registered_barangay_voter = null;
        }
    }

    if (editing.value === 'skills' && payload.skills_development_training) {
        payload.skills_development_training = titleCaseWords(payload.skills_development_training);
    }

    return payload;
}

function isCreatingSection() {
    return editing.value && editing.value !== 'demographics' && !resident.value?.[editing.value];
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

async function handleSave() {
    Object.keys(editErrors).forEach((key) => {
        delete editErrors[key];
    });
    editError.value = '';

    if (editing.value === 'demographics' && resident.value.is_household_head) {
        const age = ageFromDateOfBirth(editForm.date_of_birth);

        if (age === null || age < HOUSEHOLD_HEAD_MIN_AGE) {
            editErrors.date_of_birth = `The household head must be at least ${HOUSEHOLD_HEAD_MIN_AGE} years old.`;
            return;
        }
    }

    if (isCreatingSection()) {
        const sectionTitle = editorTitle.value.replace(/^Add\s+/i, '');
        const residentName = resident.value.full_name || 'this resident';
        const allowed = await askConfirm({
            title: `Save ${sectionTitle}`,
            message: `Save ${sectionTitle} record for ${residentName}?`,
            confirmLabel: 'Save',
        });

        if (!allowed) {
            return;
        }
    }

    saving.value = true;

    const id = resident.value.resident_id;
    const payload = preparePayload();

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
    activeSection.value = visibleSections.value[0]?.key || '';
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

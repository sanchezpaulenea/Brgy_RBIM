<template>
    <div class="grid gap-4 sm:grid-cols-2">
        <template v-if="section === 'education'">
            <div v-if="educationRelevance.highest_level">
                <label class="rbim-label" :for="`${idPrefix}-highest_lvl_of_educ_id`">
                    Highest Level of Education<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <select
                    :id="`${idPrefix}-highest_lvl_of_educ_id`"
                    v-model="form.highest_lvl_of_educ_id"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.highest_lvl_of_educ_id }"
                >
                    <option value="">Select</option>
                    <option v-for="option in lookups.highestLvlOfEduc" :key="option.id" :value="option.id">{{ option.label }}</option>
                </select>
                <p v-if="errors.highest_lvl_of_educ_id" class="rbim-error">{{ errors.highest_lvl_of_educ_id }}</p>
            </div>
            <div v-if="educationRelevance.enrollment">
                <label class="rbim-label" :for="`${idPrefix}-current_enrollement_status_id`">
                    Current Enrollment Status<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <select
                    :id="`${idPrefix}-current_enrollement_status_id`"
                    v-model="form.current_enrollement_status_id"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.current_enrollement_status_id }"
                >
                    <option value="">Select</option>
                    <option v-for="option in lookups.currentEnrollmentStatus" :key="option.id" :value="option.id">{{ option.label }}</option>
                </select>
                <p v-if="errors.current_enrollement_status_id" class="rbim-error">{{ errors.current_enrollement_status_id }}</p>
            </div>
            <template v-if="educationRelevance.enrollment && educationEnrolled">
                <div>
                    <label class="rbim-label" :for="`${idPrefix}-school_lvl_id`">
                        School Level<span class="rbim-required" aria-hidden="true">*</span>
                    </label>
                    <select
                        :id="`${idPrefix}-school_lvl_id`"
                        v-model="form.school_lvl_id"
                        class="rbim-input"
                        :class="{ 'rbim-input-error': errors.school_lvl_id }"
                    >
                        <option value="">Select</option>
                        <option v-for="option in enrolledSchoolLevels" :key="option.id" :value="option.id">{{ option.label }}</option>
                    </select>
                    <p v-if="errors.school_lvl_id" class="rbim-error">{{ errors.school_lvl_id }}</p>
                </div>
                <div>
                    <label class="rbim-label" :for="`${idPrefix}-place_of_school_brgy`">
                        School Barangay<span class="rbim-required" aria-hidden="true">*</span>
                    </label>
                    <input
                        :id="`${idPrefix}-place_of_school_brgy`"
                        v-model="form.place_of_school_brgy"
                        type="text"
                        maxlength="45"
                        class="rbim-input"
                        :class="{ 'rbim-input-error': errors.place_of_school_brgy }"
                    >
                    <p v-if="errors.place_of_school_brgy" class="rbim-error">{{ errors.place_of_school_brgy }}</p>
                </div>
                <div>
                    <label class="rbim-label" :for="`${idPrefix}-place_of_school_city_municipality`">
                        School City / Municipality<span class="rbim-required" aria-hidden="true">*</span>
                    </label>
                    <input
                        :id="`${idPrefix}-place_of_school_city_municipality`"
                        v-model="form.place_of_school_city_municipality"
                        type="text"
                        maxlength="45"
                        class="rbim-input"
                        :class="{ 'rbim-input-error': errors.place_of_school_city_municipality }"
                    >
                    <p v-if="errors.place_of_school_city_municipality" class="rbim-error">{{ errors.place_of_school_city_municipality }}</p>
                </div>
            </template>
            <p
                v-if="!educationRelevance.highest_level && !educationRelevance.enrollment"
                class="sm:col-span-2 text-sm text-slate-500"
            >
                No education fields apply at this resident's current age.
            </p>
        </template>

        <template v-else-if="section === 'economic'">
            <div>
                <label class="rbim-label" :for="`${idPrefix}-monthly_income`">
                    Monthly Income<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <input
                    :id="`${idPrefix}-monthly_income`"
                    v-model="form.monthly_income"
                    type="number"
                    min="0"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.monthly_income }"
                >
                <p v-if="errors.monthly_income" class="rbim-error">{{ errors.monthly_income }}</p>
            </div>
            <div>
                <label class="rbim-label" :for="`${idPrefix}-source_of_income_id`">
                    Source of Income<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <select
                    :id="`${idPrefix}-source_of_income_id`"
                    v-model="form.source_of_income_id"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.source_of_income_id }"
                >
                    <option value="">Select</option>
                    <option v-for="option in lookups.sourceOfIncome" :key="option.id" :value="option.id">{{ option.label }}</option>
                </select>
                <p v-if="errors.source_of_income_id" class="rbim-error">{{ errors.source_of_income_id }}</p>
            </div>
            <div v-if="economicShowsWorkDetails">
                <label class="rbim-label" :for="`${idPrefix}-status_of_work_business_id`">
                    Status of Work / Business<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <select
                    :id="`${idPrefix}-status_of_work_business_id`"
                    v-model="form.status_of_work_business_id"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.status_of_work_business_id }"
                >
                    <option value="">Select</option>
                    <option v-for="option in lookups.statusOfWorkBusiness" :key="option.id" :value="option.id">{{ option.label }}</option>
                </select>
                <p v-if="errors.status_of_work_business_id" class="rbim-error">{{ errors.status_of_work_business_id }}</p>
            </div>
            <div v-if="economicShowsWorkDetails">
                <label class="rbim-label" :for="`${idPrefix}-place_of_work_business`">
                    Place of Work / Business<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <input
                    :id="`${idPrefix}-place_of_work_business`"
                    v-model="form.place_of_work_business"
                    type="text"
                    maxlength="45"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.place_of_work_business }"
                >
                <p v-if="errors.place_of_work_business" class="rbim-error">{{ errors.place_of_work_business }}</p>
            </div>
        </template>

        <template v-else-if="section === 'infant_health'">
            <div>
                <label class="rbim-label" :for="`${idPrefix}-place_of_delivery_id`">
                    Place of Delivery<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <select
                    :id="`${idPrefix}-place_of_delivery_id`"
                    v-model="form.place_of_delivery_id"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.place_of_delivery_id }"
                >
                    <option value="">Select</option>
                    <option v-for="option in lookups.placeOfDelivery" :key="option.id" :value="option.id">{{ option.label }}</option>
                </select>
                <p v-if="errors.place_of_delivery_id" class="rbim-error">{{ errors.place_of_delivery_id }}</p>
            </div>
            <div>
                <label class="rbim-label" :for="`${idPrefix}-birth_attendant_id`">
                    Birth Attendant<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <select
                    :id="`${idPrefix}-birth_attendant_id`"
                    v-model="form.birth_attendant_id"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.birth_attendant_id }"
                >
                    <option value="">Select</option>
                    <option v-for="option in lookups.birthAttendant" :key="option.id" :value="option.id">{{ option.label }}</option>
                </select>
                <p v-if="errors.birth_attendant_id" class="rbim-error">{{ errors.birth_attendant_id }}</p>
            </div>
            <div class="sm:col-span-2">
                <label class="rbim-label" :for="`${idPrefix}-immunization`">
                    Immunization<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <input
                    :id="`${idPrefix}-immunization`"
                    v-model="form.immunization"
                    type="text"
                    maxlength="45"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.immunization }"
                >
                <p v-if="errors.immunization" class="rbim-error">{{ errors.immunization }}</p>
            </div>
        </template>

        <template v-else-if="section === 'health'">
            <div>
                <label class="rbim-label" :for="`${idPrefix}-health_insurance_id`">
                    Health Insurance
                </label>
                <select
                    :id="`${idPrefix}-health_insurance_id`"
                    v-model="form.health_insurance_id"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.health_insurance_id }"
                >
                    <option value="">Select</option>
                    <option v-for="option in lookups.healthInsurance" :key="option.id" :value="option.id">{{ option.label }}</option>
                </select>
                <p v-if="errors.health_insurance_id" class="rbim-error">{{ errors.health_insurance_id }}</p>
            </div>
            <div>
                <label class="rbim-label" :for="`${idPrefix}-facility_visited_past_12mos_id`">
                    Facility Visited Past 12 Months
                </label>
                <select
                    :id="`${idPrefix}-facility_visited_past_12mos_id`"
                    v-model="form.facility_visited_past_12mos_id"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.facility_visited_past_12mos_id }"
                >
                    <option value="">Select</option>
                    <option v-for="option in lookups.facilityVisited" :key="option.id" :value="option.id">{{ option.label }}</option>
                </select>
                <p v-if="errors.facility_visited_past_12mos_id" class="rbim-error">{{ errors.facility_visited_past_12mos_id }}</p>
            </div>
            <div>
                <label class="rbim-label" :for="`${idPrefix}-facility_visit_reason_id`">
                    Facility Visit Reason
                </label>
                <select
                    :id="`${idPrefix}-facility_visit_reason_id`"
                    v-model="form.facility_visit_reason_id"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.facility_visit_reason_id }"
                >
                    <option value="">Select</option>
                    <option v-for="option in lookups.facilityVisitReason" :key="option.id" :value="option.id">{{ option.label }}</option>
                </select>
                <p v-if="errors.facility_visit_reason_id" class="rbim-error">{{ errors.facility_visit_reason_id }}</p>
            </div>
            <LookupCombobox
                v-model="form.disability_id"
                v-model:query="form.disability_name"
                :options="lookups.disability ?? []"
                :input-id="`${idPrefix}-disability`"
                label="Disability"
                placeholder="Search or type a disability"
                required
                can-create
                :error="errors.disability_id || errors.disability"
                hint="Choose from the list, or type a new name and press Enter to add it."
                @create="createDisability"
            />
            <div>
                <label class="rbim-label" :for="`${idPrefix}-pwd_id_number`">
                    PWD ID Number
                </label>
                <input
                    :id="`${idPrefix}-pwd_id_number`"
                    v-model="form.pwd_id_number"
                    type="number"
                    min="1"
                    step="1"
                    inputmode="numeric"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.pwd_id_number }"
                >
                <p v-if="errors.pwd_id_number" class="rbim-error">{{ errors.pwd_id_number }}</p>
            </div>
        </template>

        <template v-else-if="section === 'women_health'">
            <div>
                <label class="rbim-label" :for="`${idPrefix}-number_pregnancies`">
                    Number of Pregnancies<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <input
                    :id="`${idPrefix}-number_pregnancies`"
                    v-model="form.number_pregnancies"
                    type="number"
                    min="0"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.number_pregnancies }"
                >
                <p v-if="errors.number_pregnancies" class="rbim-error">{{ errors.number_pregnancies }}</p>
            </div>
            <div>
                <label class="rbim-label" :for="`${idPrefix}-living_children`">
                    Living Children<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <input
                    :id="`${idPrefix}-living_children`"
                    v-model="form.living_children"
                    type="number"
                    min="0"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.living_children }"
                >
                <p v-if="errors.living_children" class="rbim-error">{{ errors.living_children }}</p>
            </div>
            <div>
                <label class="rbim-label" :for="`${idPrefix}-family_planning_method_id`">
                    Family Planning Method
                </label>
                <select
                    :id="`${idPrefix}-family_planning_method_id`"
                    v-model="form.family_planning_method_id"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.family_planning_method_id }"
                >
                    <option value="">Select</option>
                    <option v-for="option in lookups.familyPlanningMethod" :key="option.id" :value="option.id">{{ option.label }}</option>
                </select>
                <p v-if="errors.family_planning_method_id" class="rbim-error">{{ errors.family_planning_method_id }}</p>
            </div>
            <template v-if="!familyPlanningIsNone">
                <div>
                    <label class="rbim-label" :for="`${idPrefix}-source_of_fp_method_id`">
                        Source of Family Planning Method
                    </label>
                    <select
                        :id="`${idPrefix}-source_of_fp_method_id`"
                        v-model="form.source_of_fp_method_id"
                        class="rbim-input"
                        :class="{ 'rbim-input-error': errors.source_of_fp_method_id }"
                    >
                        <option value="">Select</option>
                        <option v-for="option in lookups.sourceOfFpMethod" :key="option.id" :value="option.id">{{ option.label }}</option>
                    </select>
                    <p v-if="errors.source_of_fp_method_id" class="rbim-error">{{ errors.source_of_fp_method_id }}</p>
                </div>
                <div>
                    <label class="rbim-label" :for="`${idPrefix}-have_intention_to_use_fp`">
                        Intention to Use Family Planning
                    </label>
                    <select
                        :id="`${idPrefix}-have_intention_to_use_fp`"
                        :value="booleanSelectValue(form.have_intention_to_use_fp)"
                        class="rbim-input"
                        :class="{ 'rbim-input-error': errors.have_intention_to_use_fp }"
                        @change="form.have_intention_to_use_fp = parseBoolean($event.target.value)"
                    >
                        <option value="">Select</option>
                        <option value="true">Yes</option>
                        <option value="false">No</option>
                    </select>
                    <p v-if="errors.have_intention_to_use_fp" class="rbim-error">{{ errors.have_intention_to_use_fp }}</p>
                </div>
            </template>
        </template>

        <template v-else-if="section === 'sociocivic'">
            <div v-if="sociocivicRelevance.solo_parent">
                <label class="rbim-label" :for="`${idPrefix}-solo_parent_status_id`">
                    Solo Parent Status<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <select
                    :id="`${idPrefix}-solo_parent_status_id`"
                    v-model="form.solo_parent_status_id"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.solo_parent_status_id }"
                >
                    <option value="">Select</option>
                    <option v-for="option in lookups.soloParentStatus" :key="option.id" :value="option.id">{{ option.label }}</option>
                </select>
                <p v-if="errors.solo_parent_status_id" class="rbim-error">{{ errors.solo_parent_status_id }}</p>
            </div>
            <div v-if="sociocivicRelevance.senior_citizen">
                <label class="rbim-label" :for="`${idPrefix}-registered_sen_citizen`">
                    Registered Senior Citizen<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <select
                    :id="`${idPrefix}-registered_sen_citizen`"
                    :value="booleanSelectValue(form.registered_sen_citizen)"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.registered_sen_citizen }"
                    @change="form.registered_sen_citizen = parseBoolean($event.target.value)"
                >
                    <option value="">Select</option>
                    <option value="true">Yes</option>
                    <option value="false">No</option>
                </select>
                <p v-if="errors.registered_sen_citizen" class="rbim-error">{{ errors.registered_sen_citizen }}</p>
            </div>
            <template v-if="sociocivicRelevance.senior_citizen && form.registered_sen_citizen === true">
                <div>
                    <label class="rbim-label" :for="`${idPrefix}-ncsc_rrn_id_number`">
                        NCSC-RRN
                    </label>
                    <input
                        :id="`${idPrefix}-ncsc_rrn_id_number`"
                        v-model="form.ncsc_rrn_id_number"
                        type="text"
                        inputmode="numeric"
                        maxlength="6"
                        placeholder="6-digit RRN"
                        class="rbim-input"
                        :class="{ 'rbim-input-error': errors.ncsc_rrn_id_number }"
                    >
                    <p v-if="errors.ncsc_rrn_id_number" class="rbim-error">{{ errors.ncsc_rrn_id_number }}</p>
                </div>
                <div>
                    <label class="rbim-label" :for="`${idPrefix}-osca_id_number`">
                        OSCA ID Number
                    </label>
                    <input
                        :id="`${idPrefix}-osca_id_number`"
                        v-model="form.osca_id_number"
                        type="text"
                        maxlength="45"
                        class="rbim-input"
                        :class="{ 'rbim-input-error': errors.osca_id_number }"
                    >
                    <p v-if="errors.osca_id_number" class="rbim-error">{{ errors.osca_id_number }}</p>
                </div>
            </template>
            <div v-if="sociocivicRelevance.barangay_voter">
                <label class="rbim-label" :for="`${idPrefix}-is_registered_barangay_voter`">
                    Registered Barangay Voter<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <select
                    :id="`${idPrefix}-is_registered_barangay_voter`"
                    v-model="form.is_registered_barangay_voter"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.is_registered_barangay_voter }"
                >
                    <option value="">Select</option>
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>
                <p v-if="errors.is_registered_barangay_voter" class="rbim-error">{{ errors.is_registered_barangay_voter }}</p>
            </div>
            <div v-if="sociocivicRelevance.barangay_voter && form.is_registered_barangay_voter === 'Yes'">
                <label class="rbim-label" :for="`${idPrefix}-registered_barangay_voter`">
                    Barangay Where Registered<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <input
                    :id="`${idPrefix}-registered_barangay_voter`"
                    v-model="form.registered_barangay_voter"
                    type="text"
                    maxlength="45"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.registered_barangay_voter }"
                >
                <p v-if="errors.registered_barangay_voter" class="rbim-error">{{ errors.registered_barangay_voter }}</p>
            </div>
        </template>

        <template v-else-if="section === 'migration'">
            <p class="sm:col-span-2 text-sm text-slate-500">
                Current household address: {{ location.barangay || '—' }}, {{ location.city || '—' }}.
                Resident type is identified by comparing previous residence 6 months ago with this address.
            </p>
            <div>
                <label class="rbim-label" :for="`${idPrefix}-previous_residence_6mos_brgy`">
                    Previous Residence 6 Months Ago (Barangay)<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <input
                    :id="`${idPrefix}-previous_residence_6mos_brgy`"
                    v-model="form.previous_residence_6mos_brgy"
                    type="text"
                    maxlength="45"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.previous_residence_6mos_brgy }"
                >
                <p v-if="errors.previous_residence_6mos_brgy" class="rbim-error">{{ errors.previous_residence_6mos_brgy }}</p>
            </div>
            <div>
                <label class="rbim-label" :for="`${idPrefix}-previous_residence_6mos_city_municipality`">
                    Previous Residence 6 Months Ago (City / Municipality)<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <input
                    :id="`${idPrefix}-previous_residence_6mos_city_municipality`"
                    v-model="form.previous_residence_6mos_city_municipality"
                    type="text"
                    maxlength="45"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.previous_residence_6mos_city_municipality }"
                >
                <p v-if="errors.previous_residence_6mos_city_municipality" class="rbim-error">{{ errors.previous_residence_6mos_city_municipality }}</p>
            </div>
            <div>
                <label class="rbim-label" :for="`${idPrefix}-previous_residence_5yrs_brgy`">
                    Previous Residence 5 Years Ago (Barangay)<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <input
                    :id="`${idPrefix}-previous_residence_5yrs_brgy`"
                    v-model="form.previous_residence_5yrs_brgy"
                    type="text"
                    maxlength="45"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.previous_residence_5yrs_brgy }"
                >
                <p v-if="errors.previous_residence_5yrs_brgy" class="rbim-error">{{ errors.previous_residence_5yrs_brgy }}</p>
            </div>
            <div>
                <label class="rbim-label" :for="`${idPrefix}-previous_residence_5yrs_city_municipality`">
                    Previous Residence 5 Years Ago (City / Municipality)<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <input
                    :id="`${idPrefix}-previous_residence_5yrs_city_municipality`"
                    v-model="form.previous_residence_5yrs_city_municipality"
                    type="text"
                    maxlength="45"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.previous_residence_5yrs_city_municipality }"
                >
                <p v-if="errors.previous_residence_5yrs_city_municipality" class="rbim-error">{{ errors.previous_residence_5yrs_city_municipality }}</p>
            </div>
            <div>
                <label class="rbim-label">Length of Stay in the Barangay</label>
                <input
                    type="text"
                    class="rbim-input bg-slate-50"
                    :value="migrationClassification.stayLabel || '—'"
                    disabled
                >
                <p class="mt-1 text-xs text-slate-500">Computed from date of transfer. Do not encode.</p>
            </div>
            <div>
                <label class="rbim-label">Type of Resident</label>
                <input
                    type="text"
                    class="rbim-input bg-slate-50"
                    :value="migrationClassification.typeLabel || '—'"
                    disabled
                >
                <p class="mt-1 text-xs text-slate-500">Identified from previous and current barangay / city.</p>
            </div>
            <template v-if="!migrationClassification.nonMigrant">
                <div>
                    <BirthDateField
                        v-model="form.date_of_transfer_in_brgy"
                        label="Date of Transfer into Barangay"
                        :input-id="`${idPrefix}-date_of_transfer_in_brgy`"
                        placeholder="Select month and year"
                        precision="month"
                        required
                        :max="todayIso"
                        :show-age="false"
                        :error="errors.date_of_transfer_in_brgy"
                    />
                </div>
                <div>
                    <BirthDateField
                        v-model="form.duration_of_stay"
                        label="Until When Does the Resident Intend to Stay"
                        :input-id="`${idPrefix}-duration_of_stay`"
                        placeholder="Select date"
                        :show-age="false"
                        :error="errors.duration_of_stay"
                    />
                </div>
                <div>
                    <label class="rbim-label" :for="`${idPrefix}-reason_for_leaving_id`">
                        Reason for Leaving Previous Residence<span class="rbim-required" aria-hidden="true">*</span>
                    </label>
                    <select
                        :id="`${idPrefix}-reason_for_leaving_id`"
                        v-model="form.reason_for_leaving_id"
                        class="rbim-input"
                        :class="{ 'rbim-input-error': errors.reason_for_leaving_id }"
                    >
                        <option value="">Select</option>
                        <option v-for="option in lookups.reasonForLeaving" :key="option.id" :value="option.id">{{ option.label }}</option>
                    </select>
                    <p v-if="errors.reason_for_leaving_id" class="rbim-error">{{ errors.reason_for_leaving_id }}</p>
                </div>
                <div>
                    <label class="rbim-label" :for="`${idPrefix}-reason_for_transfer_id`">
                        Reason for Transferring in this Barangay<span class="rbim-required" aria-hidden="true">*</span>
                    </label>
                    <select
                        :id="`${idPrefix}-reason_for_transfer_id`"
                        v-model="form.reason_for_transfer_id"
                        class="rbim-input"
                        :class="{ 'rbim-input-error': errors.reason_for_transfer_id }"
                    >
                        <option value="">Select</option>
                        <option v-for="option in lookups.reasonForTransfer" :key="option.id" :value="option.id">{{ option.label }}</option>
                    </select>
                    <p v-if="errors.reason_for_transfer_id" class="rbim-error">{{ errors.reason_for_transfer_id }}</p>
                </div>
                <div>
                    <label class="rbim-label" :for="`${idPrefix}-will_return_to_previous_residence`">
                        Plan to Return to Previous Residence<span class="rbim-required" aria-hidden="true">*</span>
                    </label>
                    <select
                        :id="`${idPrefix}-will_return_to_previous_residence`"
                        :value="booleanSelectValue(form.will_return_to_previous_residence)"
                        class="rbim-input"
                        :class="{ 'rbim-input-error': errors.will_return_to_previous_residence }"
                        @change="form.will_return_to_previous_residence = parseBoolean($event.target.value)"
                    >
                        <option value="">Select</option>
                        <option value="true">Yes</option>
                        <option value="false">No</option>
                    </select>
                    <p v-if="errors.will_return_to_previous_residence" class="rbim-error">{{ errors.will_return_to_previous_residence }}</p>
                </div>
            </template>
            <p v-else class="sm:col-span-2 text-sm text-slate-500">
                Previous residence matches the current barangay. Date of transfer, reasons, return plan, and intended stay are skipped for non-migrants.
            </p>
        </template>

        <template v-else-if="section === 'ctc'">
            <div>
                <label class="rbim-label" :for="`${idPrefix}-has_valid_ctc`">
                    Has Valid Community Tax Certificate<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <select
                    :id="`${idPrefix}-has_valid_ctc`"
                    :value="booleanSelectValue(form.has_valid_ctc)"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.has_valid_ctc }"
                    @change="form.has_valid_ctc = parseBoolean($event.target.value)"
                >
                    <option value="">Select</option>
                    <option value="true">Yes</option>
                    <option value="false">No</option>
                </select>
                <p v-if="errors.has_valid_ctc" class="rbim-error">{{ errors.has_valid_ctc }}</p>
            </div>
            <div v-if="form.has_valid_ctc === true">
                <label class="rbim-label" :for="`${idPrefix}-ctc_issued_here`">
                    Community Tax Certificate Issued Here<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <select
                    :id="`${idPrefix}-ctc_issued_here`"
                    :value="booleanSelectValue(form.ctc_issued_here)"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.ctc_issued_here }"
                    @change="form.ctc_issued_here = parseBoolean($event.target.value)"
                >
                    <option value="">Select</option>
                    <option value="true">Yes</option>
                    <option value="false">No</option>
                </select>
                <p v-if="errors.ctc_issued_here" class="rbim-error">{{ errors.ctc_issued_here }}</p>
            </div>
        </template>

        <template v-else-if="section === 'skills'">
            <div>
                <label class="rbim-label" :for="`${idPrefix}-skills_development_training`">
                    Skills Development Training<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <input
                    :id="`${idPrefix}-skills_development_training`"
                    v-model="form.skills_development_training"
                    type="text"
                    maxlength="45"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.skills_development_training }"
                >
                <p v-if="errors.skills_development_training" class="rbim-error">{{ errors.skills_development_training }}</p>
            </div>
            <div>
                <label class="rbim-label" :for="`${idPrefix}-skill_type_id`">
                    Skill Type<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <select
                    :id="`${idPrefix}-skill_type_id`"
                    v-model="form.skill_type_id"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.skill_type_id }"
                >
                    <option value="">Select</option>
                    <option v-for="option in lookups.skillType" :key="option.id" :value="option.id">{{ option.label }}</option>
                </select>
                <p v-if="errors.skill_type_id" class="rbim-error">{{ errors.skill_type_id }}</p>
            </div>
        </template>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import BirthDateField from '@/components/BirthDateField.vue';
import LookupCombobox from '@/components/LookupCombobox.vue';
import { extractErrorMessage } from '@/services/http';
import * as lookupService from '@/services/lookupService';
import { classifyMigrationForm } from '@/utils/migration';
import {
    educationFieldRelevance,
    isEnrollmentStatusEnrolled,
    isFamilyPlanningNone,
    isNotApplicableSchoolLvl,
    lookupById,
    sociocivicFieldRelevance,
    sourceOfIncomeSkipsWorkDetails,
} from '@/utils/residentProfiling';

const props = defineProps({
    section: {
        type: String,
        required: true,
    },
    form: {
        type: Object,
        required: true,
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
    lookups: {
        type: Object,
        default: () => ({}),
    },
    resident: {
        type: Object,
        default: () => ({}),
    },
    location: {
        type: Object,
        default: () => ({ barangay: '', city: '' }),
    },
    idPrefix: {
        type: String,
        default: 'section',
    },
});

const todayIso = new Date().toISOString().slice(0, 10);

const educationRelevance = computed(() => educationFieldRelevance(props.resident));

const educationEnrolled = computed(() => (
    isEnrollmentStatusEnrolled(lookupById(props.lookups.currentEnrollmentStatus, props.form.current_enrollement_status_id))
));

const enrolledSchoolLevels = computed(() => (
    (props.lookups.schoolLvl ?? []).filter((option) => !isNotApplicableSchoolLvl(option))
));

const economicShowsWorkDetails = computed(() => (
    !sourceOfIncomeSkipsWorkDetails(props.form.source_of_income_id, props.lookups.sourceOfIncome)
        && Boolean(props.form.source_of_income_id)
));

const familyPlanningIsNone = computed(() => (
    isFamilyPlanningNone(lookupById(props.lookups.familyPlanningMethod, props.form.family_planning_method_id))
));

const sociocivicRelevance = computed(() => sociocivicFieldRelevance(props.resident));

const migrationClassification = computed(() => classifyMigrationForm(props.form, props.location));

function booleanSelectValue(value) {
    if (value === true) {
        return 'true';
    }

    if (value === false) {
        return 'false';
    }

    return '';
}

function parseBoolean(value) {
    if (value === 'true') {
        return true;
    }

    if (value === 'false') {
        return false;
    }

    return '';
}

async function createDisability(name) {
    try {
        const item = await lookupService.createDisability({ disability: name });

        if (!Array.isArray(props.lookups.disability)) {
            props.lookups.disability = [];
        }

        if (!props.lookups.disability.some((option) => Number(option.id) === Number(item.id))) {
            props.lookups.disability.push(item);
        }

        props.form.disability_id = item.id;
        props.form.disability_name = item.label;
        delete props.errors.disability_id;
        delete props.errors.disability;
    } catch (err) {
        props.errors.disability_id = extractErrorMessage(err, 'Unable to add this disability.');
    }
}
</script>

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
                    <option value="">Select education level</option>
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
                    <option value="">Select enrollment status</option>
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
                        <option value="">Select school level</option>
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
                        School City/Municipality<span class="rbim-required" aria-hidden="true">*</span>
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
                    type="text"
                    inputmode="decimal"
                    autocomplete="off"
                    placeholder="0.00"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.monthly_income }"
                    @blur="form.monthly_income = formatDecimalAmount(form.monthly_income)"
                >
                <p v-if="errors.monthly_income" class="rbim-error">{{ errors.monthly_income }}</p>
                <p v-else class="rbim-hint">Amount in pesos, up to two decimal places (e.g. 12500.00).</p>
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
                    <option value="">Select source of income</option>
                    <option v-for="option in lookups.sourceOfIncome" :key="option.id" :value="option.id">{{ option.label }}</option>
                </select>
                <p v-if="errors.source_of_income_id" class="rbim-error">{{ errors.source_of_income_id }}</p>
            </div>
            <p v-if="economicSkipsWorkDetails" class="sm:col-span-2 text-sm text-slate-500">
                Status of work/business and place of work/business do not apply to this source of income and are skipped.
            </p>
            <div v-if="economicShowsWorkDetails">
                <label class="rbim-label" :for="`${idPrefix}-status_of_work_business_id`">
                    Status of Work/Business<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <select
                    :id="`${idPrefix}-status_of_work_business_id`"
                    v-model="form.status_of_work_business_id"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.status_of_work_business_id }"
                >
                    <option value="">Select work/business status</option>
                    <option v-for="option in lookups.statusOfWorkBusiness" :key="option.id" :value="option.id">{{ option.label }}</option>
                </select>
                <p v-if="errors.status_of_work_business_id" class="rbim-error">{{ errors.status_of_work_business_id }}</p>
            </div>
            <div v-if="economicShowsWorkDetails">
                <label class="rbim-label" :for="`${idPrefix}-place_of_work_business`">
                    Place of Work/Business<span class="rbim-required" aria-hidden="true">*</span>
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
            <LookupCombobox
                v-model="form.place_of_delivery_id"
                v-model:query="form.place_of_delivery_name"
                :options="lookups.placeOfDelivery ?? []"
                :input-id="`${idPrefix}-place_of_delivery`"
                label="Place of Delivery"
                placeholder="Search or type a place of delivery"
                required
                can-create
                :limit="5"
                :error="errors.place_of_delivery_id || errors.place_of_delivery"
                hint="Choose from the list, or type a new name and press Enter to add it."
                @create="(name) => createLookup('placeOfDelivery', () => lookupService.createPlaceOfDelivery({ place_of_delivery: name }), 'place_of_delivery_id', 'place_of_delivery_name', 'place_of_delivery_id')"
            />
            <LookupCombobox
                v-model="form.birth_attendant_id"
                v-model:query="form.birth_attendant_name"
                :options="lookups.birthAttendant ?? []"
                :input-id="`${idPrefix}-birth_attendant`"
                label="Birth Attendant"
                placeholder="Search or type a birth attendant"
                required
                can-create
                :limit="5"
                :error="errors.birth_attendant_id || errors.birth_attendant"
                hint="Choose from the list, or type a new name and press Enter to add it."
                @create="(name) => createLookup('birthAttendant', () => lookupService.createBirthAttendant({ birth_attendant: name }), 'birth_attendant_id', 'birth_attendant_name', 'birth_attendant_id')"
            />
            <LookupCombobox
                v-model="form.immunization_id"
                v-model:query="form.immunization_name"
                :options="lookups.immunization ?? []"
                :input-id="`${idPrefix}-immunization`"
                label="Immunization"
                placeholder="Search or type a vaccine"
                required
                can-create
                :limit="5"
                :error="errors.immunization_id || errors.immunization"
                hint="Write the vaccine last received by the infant."
                @create="(name) => createLookup('immunization', () => lookupService.createImmunization({ immunization: name }), 'immunization_id', 'immunization_name', 'immunization_id')"
            />
        </template>

        <template v-else-if="section === 'health'">
            <LookupCombobox
                v-model="form.health_insurance_id"
                v-model:query="form.health_insurance_name"
                :options="lookups.healthInsurance ?? []"
                :input-id="`${idPrefix}-health_insurance`"
                label="Health Insurance"
                placeholder="Search or type a health insurance"
                required
                can-create
                :limit="5"
                :error="errors.health_insurance_id || errors.health_insurance"
                hint="Choose from the list, or type a new name and press Enter to add it."
                @create="(name) => createLookup('healthInsurance', () => lookupService.createHealthInsurance({ health_insurance: name }), 'health_insurance_id', 'health_insurance_name', 'health_insurance_id')"
            />
            <LookupCombobox
                v-model="form.facility_visited_past_12mos_id"
                v-model:query="form.facility_visited_name"
                :options="lookups.facilityVisited ?? []"
                :input-id="`${idPrefix}-facility_visited`"
                label="Facility Visited Past 12 Months"
                placeholder="Search or type a facility"
                required
                can-create
                :limit="5"
                :error="errors.facility_visited_past_12mos_id || errors.facility_visited_past_12mos"
                hint="Choose from the list, or type a new name and press Enter to add it."
                @create="(name) => createLookup('facilityVisited', () => lookupService.createFacilityVisited({ facility_visited_past_12mos: name }), 'facility_visited_past_12mos_id', 'facility_visited_name', 'facility_visited_past_12mos_id')"
            />
            <LookupCombobox
                v-if="!facilityVisitedIsNone"
                v-model="form.facility_visit_reason_id"
                v-model:query="form.facility_visit_reason_name"
                :options="lookups.facilityVisitReason ?? []"
                :input-id="`${idPrefix}-facility_visit_reason`"
                label="Facility Visit Reason"
                placeholder="Search or type a visit reason"
                required
                can-create
                :limit="5"
                :error="errors.facility_visit_reason_id || errors.facility_visit_reason"
                hint="Choose from the list, or type a new name and press Enter to add it."
                @create="(name) => createLookup('facilityVisitReason', () => lookupService.createFacilityVisitReason({ facility_visit_reason: name }), 'facility_visit_reason_id', 'facility_visit_reason_name', 'facility_visit_reason_id')"
            />
            <LookupCombobox
                v-model="form.disability_id"
                v-model:query="form.disability_name"
                :options="lookups.disability ?? []"
                :input-id="`${idPrefix}-disability`"
                label="Disability"
                placeholder="Search or type a disability"
                required
                can-create
                :limit="5"
                :error="errors.disability_id || errors.disability"
                hint="Choose from the list, or type a new name and press Enter to add it."
                @create="createDisability"
            />
            <div v-if="showsPwdId">
                <label class="rbim-label" :for="`${idPrefix}-pwd_id_number`">
                    PWD ID Number
                </label>
                <input
                    :id="`${idPrefix}-pwd_id_number`"
                    v-model="form.pwd_id_number"
                    type="text"
                    inputmode="numeric"
                    autocomplete="off"
                    :maxlength="PWD_ID_DIGITS"
                    :placeholder="'0'.repeat(PWD_ID_DIGITS)"
                    class="rbim-input font-mono tracking-wider"
                    :class="{ 'rbim-input-error': errors.pwd_id_number }"
                    @input="form.pwd_id_number = digitsOnly($event.target.value).slice(0, PWD_ID_DIGITS)"
                >
                <p v-if="errors.pwd_id_number" class="rbim-error">{{ errors.pwd_id_number }}</p>
                <p v-else class="rbim-hint">{{ PWD_ID_DIGITS }} digits, numbers only. Leave blank if the resident has no PWD ID.</p>
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
                    Family Planning Method<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <select
                    :id="`${idPrefix}-family_planning_method_id`"
                    v-model="form.family_planning_method_id"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.family_planning_method_id }"
                >
                    <option value="">Select family planning method</option>
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
                        <option value="">Select source of method</option>
                        <option v-for="option in lookups.sourceOfFpMethod" :key="option.id" :value="option.id">{{ option.label }}</option>
                    </select>
                    <p v-if="errors.source_of_fp_method_id" class="rbim-error">{{ errors.source_of_fp_method_id }}</p>
                </div>
            </template>
            <div>
                <label class="rbim-label" :for="`${idPrefix}-have_intention_to_use_fp`">
                    Intention to Use Family Planning<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <select
                    :id="`${idPrefix}-have_intention_to_use_fp`"
                    :value="booleanSelectValue(form.have_intention_to_use_fp)"
                    class="rbim-input"
                    :class="{ 'rbim-input-error': errors.have_intention_to_use_fp }"
                    @change="form.have_intention_to_use_fp = parseBoolean($event.target.value)"
                >
                    <option value="">Select intention</option>
                    <option value="true">Yes</option>
                    <option value="false">No</option>
                </select>
                <p v-if="errors.have_intention_to_use_fp" class="rbim-error">{{ errors.have_intention_to_use_fp }}</p>
            </div>
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
                    <option value="">Select solo parent status</option>
                    <option v-for="option in lookups.soloParentStatus" :key="option.id" :value="option.id">{{ option.label }}</option>
                </select>
                <p v-if="errors.solo_parent_status_id" class="rbim-error">{{ errors.solo_parent_status_id }}</p>
            </div>
            <div v-if="sociocivicRelevance.solo_parent && soloParentIsRegistered">
                <label class="rbim-label" :for="`${idPrefix}-solo_parent_id_number`">
                    Solo Parent ID<span class="rbim-required" aria-hidden="true">*</span>
                </label>
                <input
                    :id="`${idPrefix}-solo_parent_id_number`"
                    v-model="form.solo_parent_id_number"
                    type="text"
                    maxlength="25"
                    autocomplete="off"
                    :placeholder="SOLO_PARENT_ID_PLACEHOLDER"
                    class="rbim-input font-mono"
                    :class="{ 'rbim-input-error': errors.solo_parent_id_number }"
                    @blur="form.solo_parent_id_number = formatSoloParentId(form.solo_parent_id_number)"
                >
                <p v-if="errors.solo_parent_id_number" class="rbim-error">{{ errors.solo_parent_id_number }}</p>
                <p v-else class="rbim-hint">10-digit PSGC code, year and month of issuance, then a 6-digit registry count.</p>
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
                    <option value="">Select senior citizen status</option>
                    <option value="true">Yes</option>
                    <option value="false">No</option>
                </select>
                <p v-if="errors.registered_sen_citizen" class="rbim-error">{{ errors.registered_sen_citizen }}</p>
            </div>
            <template v-if="sociocivicRelevance.senior_citizen && form.registered_sen_citizen === true">
                <div>
                    <label class="rbim-label" :for="`${idPrefix}-ncsc_rrn_id_number`">
                        NCSC-RRN<span class="rbim-required" aria-hidden="true">*</span>
                    </label>
                    <input
                        :id="`${idPrefix}-ncsc_rrn_id_number`"
                        v-model="form.ncsc_rrn_id_number"
                        type="text"
                        inputmode="numeric"
                        autocomplete="off"
                        :maxlength="NCSC_RRN_MAX_DIGITS"
                        :placeholder="`${NCSC_RRN_MIN_DIGITS}-${NCSC_RRN_MAX_DIGITS} digit RRN`"
                        class="rbim-input font-mono"
                        :class="{ 'rbim-input-error': errors.ncsc_rrn_id_number }"
                        @input="form.ncsc_rrn_id_number = digitsOnly($event.target.value).slice(0, NCSC_RRN_MAX_DIGITS)"
                    >
                    <p v-if="errors.ncsc_rrn_id_number" class="rbim-error">{{ errors.ncsc_rrn_id_number }}</p>
                    <p v-else class="rbim-hint">{{ NCSC_RRN_MIN_DIGITS }} to {{ NCSC_RRN_MAX_DIGITS }} digits, numbers only.</p>
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
                    <option value="">Select voter status</option>
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
                Resident type is identified by comparing previous residence 5 years ago and 6 months ago with this address.
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
                        placeholder="MM/YYYY"
                        precision="month"
                        required
                        :min="migrationClassification.transferDateMin"
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
                        placeholder="MM/YYYY"
                        precision="month"
                        :max="intendStayMax"
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
                        <option value="">Select reason for leaving</option>
                        <option v-for="option in migrantReasonsForLeaving" :key="option.id" :value="option.id">{{ option.label }}</option>
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
                        <option value="">Select reason for transferring</option>
                        <option v-for="option in migrantReasonsForTransfer" :key="option.id" :value="option.id">{{ option.label }}</option>
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
                        <option value="">Select plan to return</option>
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
                    <option value="">Select CTC status</option>
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
                    <option value="">Select where issued</option>
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
            <LookupCombobox
                v-model="form.skill_type_id"
                v-model:query="form.skill_type_name"
                :options="lookups.skillType ?? []"
                :input-id="`${idPrefix}-skill_type`"
                label="Skill Type"
                placeholder="Search or type a skill type"
                required
                can-create
                :limit="5"
                :error="errors.skill_type_id || errors.skill_type"
                hint="Choose from the list, or type a new name and press Enter to add it."
                @create="(name) => createLookup('skillType', () => lookupService.createSkillType({ skill_type: name }), 'skill_type_id', 'skill_type_name', 'skill_type_id')"
            />
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
    hasDisability,
    isEnrollmentStatusEnrolled,
    isFamilyPlanningNone,
    isNotApplicableLookup,
    isNotApplicableSchoolLvl,
    isRegisteredSoloParent,
    lookupById,
    sociocivicFieldRelevance,
    sourceOfIncomeSkipsWorkDetails,
} from '@/utils/residentProfiling';
import {
    digitsOnly,
    formatDecimalAmount,
    formatSoloParentId,
    NCSC_RRN_MAX_DIGITS,
    NCSC_RRN_MIN_DIGITS,
    PWD_ID_DIGITS,
    SOLO_PARENT_ID_PLACEHOLDER,
} from '@/utils/validation';

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
const intendStayMax = `${new Date().getFullYear() + 30}-12-01`;

const educationRelevance = computed(() => educationFieldRelevance(props.resident));

const educationEnrolled = computed(() => (
    isEnrollmentStatusEnrolled(lookupById(props.lookups.currentEnrollmentStatus, props.form.current_enrollement_status_id))
));

const enrolledSchoolLevels = computed(() => (
    (props.lookups.schoolLvl ?? []).filter((option) => !isNotApplicableSchoolLvl(option))
));

const migrantReasonsForLeaving = computed(() => (
    (props.lookups.reasonForLeaving ?? []).filter((option) => !isNotApplicableLookup(option))
));

const migrantReasonsForTransfer = computed(() => (
    (props.lookups.reasonForTransfer ?? []).filter((option) => !isNotApplicableLookup(option))
));

const economicSkipsWorkDetails = computed(() => (
    Boolean(props.form.source_of_income_id)
        && sourceOfIncomeSkipsWorkDetails(props.form.source_of_income_id, props.lookups.sourceOfIncome)
));

const economicShowsWorkDetails = computed(() => (
    Boolean(props.form.source_of_income_id) && !economicSkipsWorkDetails.value
));

const familyPlanningIsNone = computed(() => (
    isFamilyPlanningNone(lookupById(props.lookups.familyPlanningMethod, props.form.family_planning_method_id))
));

const facilityVisitedIsNone = computed(() => (
    isNotApplicableLookup(lookupById(props.lookups.facilityVisited ?? [], props.form.facility_visited_past_12mos_id))
));

const showsPwdId = computed(() => (
    hasDisability(
        lookupById(props.lookups.disability ?? [], props.form.disability_id)?.label
            ?? props.form.disability_name
            ?? '',
    )
));

const sociocivicRelevance = computed(() => sociocivicFieldRelevance(props.resident));

const soloParentIsRegistered = computed(() => (
    isRegisteredSoloParent(props.form.solo_parent_status_id, props.lookups.soloParentStatus ?? [])
));

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

async function createLookup(listKey, createFn, idField, nameField, errorField) {
    try {
        const item = await createFn();

        if (!Array.isArray(props.lookups[listKey])) {
            props.lookups[listKey] = [];
        }

        if (!props.lookups[listKey].some((option) => Number(option.id) === Number(item.id))) {
            props.lookups[listKey].push(item);
        }

        props.form[idField] = item.id;
        props.form[nameField] = item.label;
        delete props.errors[errorField];
        delete props.errors[idField];
    } catch (err) {
        props.errors[errorField] = extractErrorMessage(err, 'Unable to add this value.');
    }
}

async function createDisability(name) {
    return createLookup(
        'disability',
        () => lookupService.createDisability({ disability: name }),
        'disability_id',
        'disability_name',
        'disability_id',
    );
}
</script>

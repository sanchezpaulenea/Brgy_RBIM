<?php

/*
|--------------------------------------------------------------------------
| Report columns
|--------------------------------------------------------------------------
|
| Ordered column lists keyed by report level. A column source is an Eloquent
| path from the level's root model. Optional format values:
| value (default), count, person_name, person_names, yes_no, date, age,
| pluck, records, na_zero. is_group columns expand into their child columns.
| na_zero prints 0 as N/A and leaves a missing value blank.
|
*/

return [
    'household' => [
        [
            'key' => 'household_id',
            'label' => 'Household ID',
            'source' => 'household_id',
            'is_group' => false,
        ],
        [
            'key' => 'clan',
            'label' => 'Clan',
            'source' => 'clan.clan_name',
            'is_group' => false,
        ],
        [
            'key' => 'street',
            'label' => 'Street',
            'source' => 'street.street_name',
            'is_group' => false,
        ],
        [
            'key' => 'house_lot',
            'label' => 'House Lot/Number',
            'source' => 'house_lot',
            'is_group' => false,
        ],
        [
            'key' => 'number_of_house_story',
            'label' => 'Number of Household Story',
            'source' => 'number_of_house_story',
            'is_group' => false,
        ],
        [
            'key' => 'number_of_basement_level',
            'label' => 'Number of Basement Level',
            'source' => 'number_of_basement_level',
            'null_as' => 0,
            'is_group' => false,
        ],
        [
            'key' => 'household_status',
            'label' => 'Household Status',
            'source' => 'status.household_status',
            'is_group' => false,
        ],
        [
            'key' => 'head_resident_name',
            'label' => "Head Resident's Name",
            'source' => 'head',
            'format' => 'person_name',
            'is_group' => false,
        ],
        [
            'key' => 'household_questions',
            'label' => "Household Questions' Details",
            'source' => 'questions',
            'is_group' => true,
            'columns' => [
                [
                    'key' => 'ownership_of_housing_unit',
                    'label' => 'Ownership of Housing Unit',
                    'source' => 'ownershipOfHousingUnit.ownership_type',
                ],
                [
                    'key' => 'ownership_of_lot',
                    'label' => 'Ownership of Lot',
                    'source' => 'ownershipOfLot.ownership_type',
                ],
                [
                    'key' => 'fuel_type_for_lighting',
                    'label' => 'Fuel Type for Lighting',
                    'source' => 'fuelTypeForLighting.fuel_type',
                ],
                [
                    'key' => 'fuel_type_for_cooking',
                    'label' => 'Fuel Type for Cooking',
                    'source' => 'fuelTypeForCooking.fuel_type',
                ],
                [
                    'key' => 'main_source_drinking_water',
                    'label' => 'Main Source of Drinking Water',
                    'source' => 'mainSourceDrinkingWater.water_source_id',
                ],
                [
                    'key' => 'kitchen_garbage_disposal',
                    'label' => 'Kitchen Garbage Disposal',
                    'source' => 'kitchenGarbageDisposal.kitchen_garbage_disposal',
                ],
                [
                    'key' => 'perform_garbage_seggragation',
                    'label' => 'Performs Garbage Segregation',
                    'source' => 'perform_garbage_seggragation',
                    'format' => 'yes_no',
                ],
                [
                    'key' => 'toilet_facility_type',
                    'label' => 'Toilet Facility Type',
                    'source' => 'toiletFacilityType.toilet_facility_type',
                ],
                [
                    'key' => 'type_of_building_house',
                    'label' => 'Type of Building/House',
                    'source' => 'typeOfBuildingHouse.building_house_type',
                ],
                [
                    'key' => 'construction_material_outer_wall',
                    'label' => 'Construction Material of Outer Wall',
                    'source' => 'constructionMaterialOuterWall.construction_material_outer_wall',
                ],
                [
                    'key' => 'common_diseases',
                    'label' => 'Common Diseases Causing Death in the Barangay',
                    'source' => 'commonDiseases',
                    'format' => 'pluck',
                    'pluck' => 'common_disease',
                ],
                [
                    'key' => 'primary_needs',
                    'label' => 'Primary Needs of the Barangay',
                    'source' => 'primaryNeeds',
                    'format' => 'pluck',
                    'pluck' => 'primary_need',
                ],
                [
                    'key' => 'intend_to_stay_brgy',
                    'label' => 'Intend to Stay in the Barangay',
                    'source' => 'intendToStay.intend_to_stay_brgy',
                ],
                [
                    'key' => 'intend_to_stay_municipality',
                    'label' => 'Intend to Stay in the Municipality',
                    'source' => 'intendToStay.intend_to_stay_municipality',
                ],
                [
                    'key' => 'intend_to_stay_province',
                    'label' => 'Intend to Stay in the Province',
                    'source' => 'intendToStay.intend_to_stay_province',
                ],
                [
                    'key' => 'female_deaths',
                    'label' => 'Female Household Members Who Died',
                    'source' => 'femaleDeaths',
                    'format' => 'records',
                    'fields' => [
                        ['label' => 'Age', 'source' => 'age'],
                        ['label' => 'Cause of Death', 'source' => 'cause_of_death'],
                    ],
                ],
                [
                    'key' => 'child_deaths',
                    'label' => 'Child Household Members Who Died',
                    'source' => 'childDeaths',
                    'format' => 'records',
                    'fields' => [
                        ['label' => 'Age', 'source' => 'age'],
                        ['label' => 'Sex', 'source' => 'sex.sex'],
                        ['label' => 'Cause of Death', 'source' => 'cause_of_death'],
                    ],
                ],
            ],
        ],
        [
            'key' => 'number_of_pets',
            'label' => 'Number of Pets',
            'source' => 'pets',
            'format' => 'count',
            'is_group' => false,
        ],
        [
            'key' => 'pet_details',
            'label' => 'Pet Details',
            'source' => 'pets',
            'is_group' => true,
            'columns' => [
                [
                    'key' => 'specie',
                    'label' => 'Specie',
                    'source' => 'specie.specie',
                ],
                [
                    'key' => 'breed',
                    'label' => 'Breed',
                    'source' => 'breed.breed',
                ],
                [
                    'key' => 'sex',
                    'label' => 'Sex',
                    'source' => 'sex.sex',
                ],
                [
                    'key' => 'pet_date_of_birth',
                    'label' => 'Date of Birth',
                    'source' => 'pet_date_of_birth',
                    'format' => 'date',
                ],
                [
                    'key' => 'is_spay_neuter',
                    'label' => 'Spay/Neuter',
                    'source' => 'is_spay_neuter',
                    'format' => 'yes_no',
                ],
                [
                    'key' => 'rabies_vaccination_date',
                    'label' => 'Rabies Vaccination Date',
                    'source' => 'rabies_vaccination_date',
                    'format' => 'date',
                ],
            ],
        ],
        [
            'key' => 'total_household_members',
            'label' => 'Total Household Members',
            'source' => 'residents',
            'format' => 'count',
            'is_group' => false,
        ],
        [
            'key' => 'household_members_name',
            'label' => "Household Members' Name",
            'source' => 'residents',
            'format' => 'person_names',
            'is_group' => false,
        ],
        [
            'key' => 'household_members_demographic',
            'label' => "Household Members' Demographic Details",
            'source' => 'residents',
            'is_group' => true,
            'columns' => [
                [
                    'key' => 'sex',
                    'label' => 'Sex',
                    'source' => 'sex.sex',
                ],
                [
                    'key' => 'date_of_birth',
                    'label' => 'Date of Birth',
                    'source' => 'date_of_birth',
                    'format' => 'date',
                ],
                [
                    'key' => 'age',
                    'label' => 'Age',
                    'source' => '',
                    'format' => 'age',
                ],
                [
                    'key' => 'relationship_to_hh',
                    'label' => 'Relationship to Household Head',
                    'source' => 'relationshipToHouseholdHead.relationship_to_hh',
                ],
                [
                    'key' => 'marital_status',
                    'label' => 'Marital Status',
                    'source' => 'maritalStatus.marital_status',
                ],
                [
                    'key' => 'nationality',
                    'label' => 'Nationality',
                    'source' => 'nationality.nationality',
                ],
                [
                    'key' => 'religion',
                    'label' => 'Religion',
                    'source' => 'religion.religion',
                ],
                [
                    'key' => 'ethnicity',
                    'label' => 'Ethnicity',
                    'source' => 'ethnicity.ethnicity',
                ],
                [
                    'key' => 'resident_status',
                    'label' => 'Resident Status',
                    'source' => 'status.resident_status',
                ],
                [
                    'key' => 'birth_city_municipality',
                    'label' => 'Birth City/Municipality',
                    'source' => 'birth_city_municipality',
                ],
                [
                    'key' => 'birth_province',
                    'label' => 'Birth Province',
                    'source' => 'birth_province',
                ],
                [
                    'key' => 'birth_country',
                    'label' => 'Birth Country',
                    'source' => 'birth_country',
                ],
            ],
        ],
    ],

    'resident' => [
        [
            'key' => 'household_id',
            'label' => 'Household ID',
            'source' => 'household.household_id',
            'is_group' => false,
        ],
        [
            'key' => 'resident_id',
            'label' => 'Resident ID',
            'source' => 'resident_id',
            'is_group' => false,
        ],
        [
            'key' => 'clan',
            'label' => 'Clan',
            'source' => 'household.clan.clan_name',
            'is_group' => false,
        ],
        [
            'key' => 'street',
            'label' => 'Street',
            'source' => 'household.street.street_name',
            'is_group' => false,
        ],
        [
            'key' => 'house_lot',
            'label' => 'House Lot/Number',
            'source' => 'household.house_lot',
            'is_group' => false,
        ],
        [
            'key' => 'household_status',
            'label' => 'Household Status',
            'source' => 'household.status.household_status',
            'is_group' => false,
        ],
        [
            'key' => 'head_resident_name',
            'label' => "Head Resident's Name",
            'source' => 'household.head',
            'format' => 'person_name',
            'is_group' => false,
        ],
        [
            'key' => 'number_of_pets',
            'label' => 'Number of Pets',
            'source' => 'household.pets',
            'format' => 'count',
            'is_group' => false,
        ],
        [
            'key' => 'total_household_members',
            'label' => 'Total Household Members',
            'source' => 'household.residents',
            'format' => 'count',
            'is_group' => false,
        ],
        [
            'key' => 'household_members_name',
            'label' => "Household Members' Names",
            'source' => 'household.residents',
            'format' => 'person_names',
            'is_group' => false,
        ],
        [
            'key' => 'resident_demographic',
            'label' => "Household Member's Demographic Details",
            'source' => '',
            'is_group' => true,
            'columns' => [
                [
                    'key' => 'sex',
                    'label' => 'Sex',
                    'source' => 'sex.sex',
                ],
                [
                    'key' => 'date_of_birth',
                    'label' => 'Date of Birth',
                    'source' => 'date_of_birth',
                    'format' => 'date',
                ],
                [
                    'key' => 'age',
                    'label' => 'Age',
                    'source' => '',
                    'format' => 'age',
                ],
                [
                    'key' => 'relationship_to_hh',
                    'label' => 'Relationship to Household Head',
                    'source' => 'relationshipToHouseholdHead.relationship_to_hh',
                ],
                [
                    'key' => 'marital_status',
                    'label' => 'Marital Status',
                    'source' => 'maritalStatus.marital_status',
                ],
                [
                    'key' => 'nationality',
                    'label' => 'Nationality',
                    'source' => 'nationality.nationality',
                ],
                [
                    'key' => 'religion',
                    'label' => 'Religion',
                    'source' => 'religion.religion',
                ],
                [
                    'key' => 'ethnicity',
                    'label' => 'Ethnicity',
                    'source' => 'ethnicity.ethnicity',
                ],
                [
                    'key' => 'resident_status',
                    'label' => 'Resident Status',
                    'source' => 'status.resident_status',
                ],
                [
                    'key' => 'birth_city_municipality',
                    'label' => 'Birth City/Municipality',
                    'source' => 'birth_city_municipality',
                ],
                [
                    'key' => 'birth_province',
                    'label' => 'Birth Province',
                    'source' => 'birth_province',
                ],
                [
                    'key' => 'birth_country',
                    'label' => 'Birth Country',
                    'source' => 'birth_country',
                ],
            ],
        ],
    ],

    /*
    | Category-only columns. A category lists the keys it adds under
    | extra_columns. They are not part of every household report.
    */
    'extras' => [
        'total_pets_recorded' => [
            'key' => 'total_pets_recorded',
            'label' => 'Total Pets Recorded',
            'source' => 'matching_pets',
            'format' => 'count',
            'count_via' => 'repository',
            'is_group' => false,
        ],
        'total_diseases_recorded' => [
            'key' => 'total_diseases_recorded',
            'label' => 'Total Diseases Recorded',
            'source' => 'matching_diseases',
            'format' => 'count',
            'count_via' => 'repository',
            'is_group' => false,
        ],
        'total_primary_needs_recorded' => [
            'key' => 'total_primary_needs_recorded',
            'label' => 'Total Primary Needs Recorded',
            'source' => 'matching_needs',
            'format' => 'count',
            'count_via' => 'repository',
            'is_group' => false,
        ],
        'female_death_details' => [
            'key' => 'female_death_details',
            'label' => 'Female Death Details',
            'source' => 'questions.femaleDeaths',
            'is_group' => true,
            'columns' => [
                [
                    'key' => 'age',
                    'label' => 'Age',
                    'source' => 'age',
                ],
                [
                    'key' => 'cause_of_death',
                    'label' => 'Cause of Death',
                    'source' => 'cause_of_death',
                ],
            ],
        ],
        'child_death_details' => [
            'key' => 'child_death_details',
            'label' => 'Child Death Details',
            'source' => 'questions.childDeaths',
            'is_group' => true,
            'columns' => [
                [
                    'key' => 'age',
                    'label' => 'Age',
                    'source' => 'age',
                ],
                [
                    'key' => 'cause_of_death',
                    'label' => 'Cause of Death',
                    'source' => 'cause_of_death',
                ],
                [
                    'key' => 'sex',
                    'label' => 'Sex',
                    'source' => 'sex.sex',
                ],
            ],
        ],
        'household_members_health' => [
            'key' => 'household_members_health',
            'label' => "Household Members' Health Details",
            'source' => 'residents',
            'is_group' => true,
            'columns' => [
                [
                    'key' => 'health_insurance',
                    'label' => 'Health Insurance',
                    'source' => 'health.healthInsurance.health_insurance',
                ],
                [
                    'key' => 'facility_visited_past_12mos',
                    'label' => 'Facility Visited Past 12 Months',
                    'source' => 'health.facilityVisitedPast12Mos.facility_visited_past_12mos',
                ],
                [
                    'key' => 'facility_visit_reason',
                    'label' => 'Facility Visit Reason',
                    'source' => 'health.facilityVisitReason.facility_visit_reason',
                ],
                [
                    'key' => 'disability',
                    'label' => 'Disability',
                    'source' => 'health.disabilityType.disability',
                ],
                [
                    'key' => 'pwd_id_number',
                    'label' => 'PWD ID Number',
                    'source' => 'health.pwd_id_number',
                    'format' => 'na_zero',
                ],
                [
                    'key' => 'place_of_delivery',
                    'label' => 'Place of Delivery',
                    'source' => 'infantHealth.placeOfDelivery.place_of_delivery',
                ],
                [
                    'key' => 'birth_attendant',
                    'label' => 'Birth Attendant',
                    'source' => 'infantHealth.birthAttendant.birth_attendant',
                ],
                [
                    'key' => 'immunization',
                    'label' => 'Immunization',
                    'source' => 'infantHealth.immunization.immunization',
                ],
                [
                    'key' => 'number_pregnancies',
                    'label' => 'Number of Pregnancies',
                    'source' => 'health.womenHealth.number_pregnancies',
                ],
                [
                    'key' => 'living_children',
                    'label' => 'Living Children',
                    'source' => 'health.womenHealth.living_children',
                ],
                [
                    'key' => 'family_planning_method',
                    'label' => 'Family Planning Method',
                    'source' => 'health.womenHealth.familyPlanningMethod.family_planning_method',
                ],
                [
                    'key' => 'source_of_fp_method',
                    'label' => 'Source of Family Planning Method',
                    'source' => 'health.womenHealth.sourceOfFpMethod.source_of_fp_method',
                ],
                [
                    'key' => 'have_intention_to_use_fp',
                    'label' => 'Intention to Use Family Planning',
                    'source' => 'health.womenHealth.have_intention_to_use_fp',
                    'format' => 'yes_no',
                ],
            ],
        ],
        'education_details' => [
            'key' => 'education_details',
            'label' => "Household Member's Education Details",
            'source' => 'education',
            'is_group' => true,
            'columns' => [
                [
                    'key' => 'highest_level',
                    'label' => 'Highest Level of Education',
                    'source' => 'highestLvlOfEduc.lvl_of_educ',
                ],
                [
                    'key' => 'current_enrollment',
                    'label' => 'Current Enrollment Status',
                    'source' => 'currentEnrollmentStatus.current_enrollement_status',
                ],
                [
                    'key' => 'school_lvl',
                    'label' => 'School Level',
                    'source' => 'schoolLvl.school_lvl',
                ],
                [
                    'key' => 'place_of_school_brgy',
                    'label' => 'Place of School (Barangay)',
                    'source' => 'place_of_school_brgy',
                ],
                [
                    'key' => 'place_of_school_city_municipality',
                    'label' => 'Place of School (City/Municipality)',
                    'source' => 'place_of_school_city_municipality',
                ],
            ],
        ],
        'economic_details' => [
            'key' => 'economic_details',
            'label' => "Household Member's Economic Details",
            'source' => 'economic',
            'is_group' => true,
            'columns' => [
                [
                    'key' => 'monthly_income',
                    'label' => 'Monthly Income',
                    'source' => 'monthly_income',
                    'format' => 'money',
                ],
                [
                    'key' => 'source_of_income',
                    'label' => 'Source of Income',
                    'source' => 'sourceOfIncome.source_of_income',
                ],
                [
                    'key' => 'work_status',
                    'label' => 'Status of Work/Business',
                    'source' => 'statusOfWorkBusiness.status_of_work_business',
                ],
                [
                    'key' => 'place_of_work_business',
                    'label' => 'Place of Work/Business',
                    'source' => 'place_of_work_business',
                ],
            ],
        ],
        'health_details' => [
            'key' => 'health_details',
            'label' => "Household Member's Health Details",
            'source' => '',
            'is_group' => true,
            'columns' => [
                [
                    'key' => 'health_insurance',
                    'label' => 'Health Insurance',
                    'source' => 'health.healthInsurance.health_insurance',
                ],
                [
                    'key' => 'facility_visited_past_12mos',
                    'label' => 'Facility Visited Past 12 Months',
                    'source' => 'health.facilityVisitedPast12Mos.facility_visited_past_12mos',
                ],
                [
                    'key' => 'facility_visit_reason',
                    'label' => 'Facility Visit Reason',
                    'source' => 'health.facilityVisitReason.facility_visit_reason',
                ],
                [
                    'key' => 'disability',
                    'label' => 'Disability',
                    'source' => 'health.disabilityType.disability',
                ],
                [
                    'key' => 'pwd_id_number',
                    'label' => 'PWD ID Number',
                    'source' => 'health.pwd_id_number',
                    'format' => 'na_zero',
                ],
            ],
        ],
        'infant_health_details' => [
            'key' => 'infant_health_details',
            'label' => "Household Member's Infant Health Details",
            'source' => 'infantHealth',
            'is_group' => true,
            'columns' => [
                [
                    'key' => 'place_of_delivery',
                    'label' => 'Place of Delivery',
                    'source' => 'placeOfDelivery.place_of_delivery',
                ],
                [
                    'key' => 'birth_attendant',
                    'label' => 'Birth Attendant',
                    'source' => 'birthAttendant.birth_attendant',
                ],
                [
                    'key' => 'immunization',
                    'label' => 'Immunization',
                    'source' => 'immunization.immunization',
                ],
            ],
        ],
        'women_health_details' => [
            'key' => 'women_health_details',
            'label' => "Household Member's Women Health Details",
            'source' => 'health.womenHealth',
            'is_group' => true,
            'columns' => [
                [
                    'key' => 'number_pregnancies',
                    'label' => 'Number of Pregnancies',
                    'source' => 'number_pregnancies',
                ],
                [
                    'key' => 'living_children',
                    'label' => 'Living Children',
                    'source' => 'living_children',
                ],
                [
                    'key' => 'family_planning_method',
                    'label' => 'Family Planning Method',
                    'source' => 'familyPlanningMethod.family_planning_method',
                ],
                [
                    'key' => 'source_of_fp_method',
                    'label' => 'Source of Family Planning Method',
                    'source' => 'sourceOfFpMethod.source_of_fp_method',
                ],
                [
                    'key' => 'have_intention_to_use_fp',
                    'label' => 'Intention to Use Family Planning',
                    'source' => 'have_intention_to_use_fp',
                    'format' => 'yes_no',
                ],
            ],
        ],
        'sociocivic_details' => [
            'key' => 'sociocivic_details',
            'label' => "Household Member's Sociocivic Details",
            'source' => 'sociocivic',
            'is_group' => true,
            'columns' => [
                [
                    'key' => 'solo_parent_status',
                    'label' => 'Solo Parent Status',
                    'source' => 'soloParentStatus.solo_parent_status',
                ],
                [
                    'key' => 'ncsc_rrn_id_number',
                    'label' => 'NCSC RRN ID Number',
                    'source' => 'ncsc_rrn_id_number',
                ],
                [
                    'key' => 'osca_id_number',
                    'label' => 'OSCA ID Number',
                    'source' => 'osca_id_number',
                ],
                [
                    'key' => 'registered_senior_citizen',
                    'label' => 'Registered Senior Citizen (via OSCA ID)',
                    'source' => 'registeredSeniorViaOsca',
                    'format' => 'yes_no',
                ],
                [
                    'key' => 'solo_parent_id_number',
                    'label' => 'Solo Parent ID Number',
                    'source' => 'solo_parent_id_number',
                ],
                [
                    'key' => 'registered_barangay_voter',
                    'label' => 'Registered Barangay Voter',
                    'source' => 'registered_barangay_voter',
                ],
            ],
        ],
        'migration_details' => [
            'key' => 'migration_details',
            'label' => "Household Member's Migration Details",
            'source' => 'migration',
            'is_group' => true,
            'columns' => [
                [
                    'key' => 'previous_residence_6mos_brgy',
                    'label' => 'Previous Residence 6 Months (Barangay)',
                    'source' => 'previous_residence_6mos_brgy',
                ],
                [
                    'key' => 'previous_residence_6mos_city_municipality',
                    'label' => 'Previous Residence 6 Months (City/Municipality)',
                    'source' => 'previous_residence_6mos_city_municipality',
                ],
                [
                    'key' => 'previous_residence_5yrs_brgy',
                    'label' => 'Previous Residence 5 Years (Barangay)',
                    'source' => 'previous_residence_5yrs_brgy',
                ],
                [
                    'key' => 'previous_residence_5yrs_city_municipality',
                    'label' => 'Previous Residence 5 Years (City/Municipality)',
                    'source' => 'previous_residence_5yrs_city_municipality',
                ],
                [
                    'key' => 'date_of_transfer_in_brgy',
                    'label' => 'Date of Transfer into the Barangay',
                    'source' => 'date_of_transfer_in_brgy',
                    'format' => 'date',
                ],
                [
                    'key' => 'resident_type',
                    'label' => 'Resident Type',
                    'source' => 'residentType.resident_type',
                ],
                [
                    'key' => 'reason_for_leaving',
                    'label' => 'Reason for Leaving',
                    'source' => 'reasonForLeaving.reason_for_leaving',
                ],
                [
                    'key' => 'will_return_to_previous_residence',
                    'label' => 'Will Return to Previous Residence',
                    'source' => 'will_return_to_previous_residence',
                    'format' => 'yes_no',
                ],
                [
                    'key' => 'reason_for_transfer',
                    'label' => 'Reason for Transfer',
                    'source' => 'reasonForTransfer.reason_for_transfer',
                ],
                [
                    'key' => 'duration_of_stay',
                    'label' => 'Duration of Stay',
                    'source' => 'duration_of_stay',
                    'format' => 'date',
                ],
            ],
        ],
        'ctc_details' => [
            'key' => 'ctc_details',
            'label' => "Household Member's CTC Details",
            'source' => 'communityTaxCert',
            'is_group' => true,
            'columns' => [
                [
                    'key' => 'has_valid_ctc',
                    'label' => 'Has Valid CTC',
                    'source' => 'has_valid_ctc',
                    'format' => 'yes_no',
                ],
                [
                    'key' => 'ctc_issued_here',
                    'label' => 'CTC Issued Here',
                    'source' => 'ctc_issued_here',
                    'format' => 'yes_no',
                ],
            ],
        ],
        'skills_details' => [
            'key' => 'skills_details',
            'label' => "Household Member's Skills Details",
            'source' => 'skillsDevelopments',
            'is_group' => true,
            'columns' => [
                [
                    'key' => 'skills_development_training',
                    'label' => 'Skills Development Training',
                    'source' => 'skills_development_training',
                ],
                [
                    'key' => 'skill_type',
                    'label' => 'Skill Type',
                    'source' => 'skillType.skill_type',
                ],
            ],
        ],
    ],
];

<?php

/*
|--------------------------------------------------------------------------
| Report columns
|--------------------------------------------------------------------------
|
| Ordered column lists keyed by report level. A column source is an Eloquent
| path from the level's root model. Optional format values:
| value (default), count, person_name, person_names, yes_no, date, age,
| pluck, records. is_group columns expand into their child columns.
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

    // populated in Phase 3.
    'resident' => [],

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
    ],
];

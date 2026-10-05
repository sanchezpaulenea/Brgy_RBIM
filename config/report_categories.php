<?php

use App\Models\HouseholdManagement\Breed;
use App\Models\HouseholdManagement\BuildingHouseType;
use App\Models\HouseholdManagement\Clan;
use App\Models\HouseholdManagement\CommonDiseaseCauseDeathInBrgy;
use App\Models\HouseholdManagement\ConstructionMaterialOuterWall;
use App\Models\HouseholdManagement\FuelType;
use App\Models\HouseholdManagement\HouseholdStatus;
use App\Models\HouseholdManagement\KitchenGarbageDisposal;
use App\Models\HouseholdManagement\OwnershipType;
use App\Models\HouseholdManagement\PrimaryNeedOfBrgy;
use App\Models\HouseholdManagement\Specie;
use App\Models\HouseholdManagement\Street;
use App\Models\HouseholdManagement\ToiletFacilityType;
use App\Models\HouseholdManagement\WaterSource;
use App\Models\ResidentManagement\Demographic\Ethnicity;
use App\Models\ResidentManagement\Demographic\MaritalStatus;
use App\Models\ResidentManagement\Demographic\Nationality;
use App\Models\ResidentManagement\Demographic\Religion;
use App\Models\ResidentManagement\Demographic\ResidentStatus;
use App\Models\ResidentManagement\Demographic\ResidentType;
use App\Models\ResidentManagement\Demographic\Sex;
use App\Models\ResidentManagement\Economic\Economic;
use App\Models\ResidentManagement\Economic\SourceOfIncome;
use App\Models\ResidentManagement\Economic\StatusOfWorkBusiness;
use App\Models\ResidentManagement\Education\CurrentEnrollmentStatus;
use App\Models\ResidentManagement\Education\HighestLvlOfEduc;
use App\Models\ResidentManagement\Education\SchoolLvl;
use App\Models\ResidentManagement\Health\BirthAttendant;
use App\Models\ResidentManagement\Health\Disability;
use App\Models\ResidentManagement\Health\FacilityVisitedPast12Mos;
use App\Models\ResidentManagement\Health\FacilityVisitReason;
use App\Models\ResidentManagement\Health\FamilyPlanningMethod;
use App\Models\ResidentManagement\Health\HealthInsurance;
use App\Models\ResidentManagement\Health\Immunization;
use App\Models\ResidentManagement\Health\PlaceOfDelivery;
use App\Models\ResidentManagement\Health\SourceOfFPMethod;
use App\Models\ResidentManagement\Migration\ReasonForLeaving;
use App\Models\ResidentManagement\Migration\ReasonForTransfer;
use App\Models\ResidentManagement\Skill\SkillsDevelopment;
use App\Models\ResidentManagement\Skill\SkillType;
use App\Models\ResidentManagement\Sociocivic\SoloParentStatus;

/*
|--------------------------------------------------------------------------
| Report categories
|--------------------------------------------------------------------------
|
| One entry per report. Each category has a filters list. Every sub-filter
| is ANDed. A lookup sub-filter offers All, Per one, and Select 1 or more.
| Boolean and options sub-filters use a fixed choice list. Column lists live
| in config/report_columns.php.
|
| owns: column keys hidden when the sub-filter resolves to exactly one value.
| questions: household_questions categories. Unanswered households (no row)
| are included only while every sub-filter is All.
|
*/

$standard = [
    'household_id',
    'clan',
    'street',
    'house_lot',
    'number_of_house_story',
    'number_of_basement_level',
    'household_status',
    'head_resident_name',
    'household_questions',
    'number_of_pets',
    'pet_details',
    'total_household_members',
    'household_members_name',
    'household_members_demographic',
];

$petsDefaults = [
    'household_id',
    'clan',
    'street',
    'house_lot',
    'household_status',
    'head_resident_name',
    'total_pets_recorded',
    'pet_details',
    'total_household_members',
    'household_members_name',
];

$diseaseDefaults = [
    'household_id',
    'clan',
    'street',
    'house_lot',
    'household_status',
    'head_resident_name',
    'household_questions',
    'total_diseases_recorded',
    'total_household_members',
    'household_members_name',
];

$needDefaults = [
    'household_id',
    'clan',
    'street',
    'house_lot',
    'household_status',
    'head_resident_name',
    'household_questions',
    'total_primary_needs_recorded',
    'total_household_members',
    'household_members_name',
];

$deathDefaults = [
    'household_id',
    'clan',
    'street',
    'house_lot',
    'head_resident_name',
    'household_questions',
    'total_household_members',
    'household_members_name',
];

$sortLookup = static function (string $table, string $alias, string $idColumn, string $labelColumn, string $left): array {
    return [
        'type' => 'lookup',
        'table' => $table,
        'alias' => $alias,
        'id_column' => $idColumn,
        'label_column' => $labelColumn,
        'left' => $left,
    ];
};

$lookup = static function (
    string $key,
    string $label,
    string $column,
    string $apply,
    string $model,
    string $table,
    string $idColumn,
    string $labelColumn,
    array $owns,
    array $sort,
    array $extra = [],
): array {
    return [
        'key' => $key,
        'label' => $label,
        'type' => 'lookup',
        'modes' => $extra['modes'] ?? ['all', 'one', 'multiple'],
        'plural' => $extra['plural'] ?? null,
        'column' => $column,
        'apply' => $apply,
        'model' => $model,
        'table' => $table,
        'id_column' => $idColumn,
        'label_column' => $labelColumn,
        'owns' => $owns,
        'sort' => $sort,
        'subrecord_via' => $extra['subrecord_via'] ?? null,
        'junction_table' => $extra['junction_table'] ?? null,
        'junction_owner' => $extra['junction_owner'] ?? null,
        'junction_id' => $extra['junction_id'] ?? null,
        'constrain_relation' => $extra['constrain_relation'] ?? null,
        'constrain_column' => $extra['constrain_column'] ?? null,
        'constrain_order' => $extra['constrain_order'] ?? null,
        'mode_labels' => $extra['mode_labels'] ?? null,
        'distinct_label' => $extra['distinct_label'] ?? false,
        'force_owned' => $extra['force_owned'] ?? null,
        'sentinel_mode' => $extra['sentinel_mode'] ?? null,
        'omit_sentinel_from' => $extra['omit_sentinel_from'] ?? [],
        'subrecord_table' => $extra['subrecord_table'] ?? null,
        'subrecord_owner' => $extra['subrecord_owner'] ?? null,
        'visible_when' => $extra['visible_when'] ?? null,
    ];
};

$choices = static function (
    string $key,
    string $label,
    string $type,
    string $apply,
    array $choiceList,
    array $owns,
    ?array $sort,
    ?string $expression = null,
    ?string $column = null,
    array $extra = [],
): array {
    return [
        'key' => $key,
        'label' => $label,
        'type' => $type,
        'apply' => $apply,
        'column' => $column,
        'expression' => $expression,
        'choices' => $choiceList,
        'owns' => $owns,
        'sort' => $sort,
        'presence_table' => $extra['presence_table'] ?? null,
        'presence_owner' => $extra['presence_owner'] ?? null,
        'subrecord_table' => $extra['subrecord_table'] ?? null,
        'subrecord_owner' => $extra['subrecord_owner'] ?? null,
        'subrecord_via' => $extra['subrecord_via'] ?? null,
        'visible_when' => $extra['visible_when'] ?? null,
        'sentinel_model' => $extra['sentinel_model'] ?? null,
        'force_owned' => $extra['force_owned'] ?? null,
    ];
};

$category = static function (string $label, array $filters, array $extra = []) use ($standard): array {
    return [
        'level' => $extra['level'] ?? 'household',
        'label' => $label,
        'fixed_title' => $extra['fixed_title'] ?? null,
        'header' => $extra['header'] ?? null,
        'questions' => $extra['questions'] ?? false,
        'audience' => $extra['audience'] ?? null,
        'filters' => $filters,
        'default_columns' => $extra['default_columns'] ?? $standard,
        'extra_columns' => $extra['extra_columns'] ?? [],
        'counts' => $extra['counts'] ?? [],
    ];
};

$any = ['value' => 'any', 'label' => 'Any', 'op' => 'any', 'singular' => false];

$storyChoices = [
    $any,
    ['value' => 'one', 'label' => '1 story', 'op' => 'eq', 'operand' => 1, 'singular' => true],
    ['value' => 'more', 'label' => 'More than 1', 'op' => 'gt', 'operand' => 1, 'singular' => false],
    ['value' => 'exactly', 'label' => 'Exactly', 'op' => 'eq_input', 'numeric' => true, 'min' => 1, 'max' => 50, 'singular' => true],
];

$basementChoices = [
    $any,
    ['value' => 'none', 'label' => 'No basement', 'op' => 'eq', 'operand' => 0, 'singular' => true],
    ['value' => 'one', 'label' => 'Exactly 1', 'op' => 'eq', 'operand' => 1, 'singular' => true],
    ['value' => 'more', 'label' => 'More than 1', 'op' => 'gt', 'operand' => 1, 'singular' => false],
    ['value' => 'exactly', 'label' => 'Exactly', 'op' => 'eq_input', 'numeric' => true, 'min' => 0, 'max' => 20, 'singular' => true],
];

$rabiesChoices = [
    ['value' => 'all', 'label' => 'All', 'op' => 'any', 'singular' => false],
    ['value' => 'within_12', 'label' => 'Vaccinated within last 12 months', 'op' => 'within_months', 'months' => 12, 'singular' => false],
    ['value' => 'older_12', 'label' => 'Older than 12 months', 'op' => 'older_than_months', 'months' => 12, 'singular' => false],
    ['value' => 'recorded', 'label' => 'Has vaccination record', 'op' => 'not_null', 'singular' => false],
];

$residentDefaults = [
    'household_id',
    'resident_id',
    'clan',
    'street',
    'house_lot',
    'household_status',
    'head_resident_name',
    'number_of_pets',
    'total_household_members',
    'household_members_name',
    'resident_demographic',
];

$residentExtras = [
    'education_details',
    'economic_details',
    'health_details',
    'infant_health_details',
    'women_health_details',
    'sociocivic_details',
    'migration_details',
    'ctc_details',
    'skills_details',
];

$residentCategory = static function (string $label, array $filters, array $extra = []) use ($category, $residentDefaults, $residentExtras): array {
    $defaults = $residentDefaults;

    foreach ($extra['also_default'] ?? [] as $column) {
        $defaults[] = $column;
    }

    return $category($label, $filters, [
        'level' => 'resident',
        'header' => $extra['header'] ?? 'Total Resident Records',
        'audience' => $extra['audience'] ?? null,
        'fixed_title' => $extra['fixed_title'] ?? null,
        'default_columns' => $defaults,
        'extra_columns' => $residentExtras,
    ]);
};

$attributeModes = static function (string $noun): array {
    return [
        'all' => 'All '.$noun,
        'one' => 'Per '.$noun,
        'multiple' => 'Select 1 or More '.$noun,
    ];
};

$residentAttribute = static function (
    string $categoryLabel,
    string $filterKey,
    string $noun,
    string $column,
    string $model,
    string $table,
    string $idColumn,
    string $labelColumn,
    string $owned,
) use ($lookup, $sortLookup, $residentCategory, $attributeModes): array {
    return $residentCategory($categoryLabel, [
        $lookup(
            $filterKey,
            $categoryLabel,
            $column,
            'resident',
            $model,
            $table,
            $idColumn,
            $labelColumn,
            [$owned],
            $sortLookup($table, 'sort_'.$filterKey, $idColumn, $labelColumn, 'resident.'.$column),
            ['mode_labels' => $attributeModes($noun)],
        ),
    ]);
};

$childSort = static function (
    string $childTable,
    string $childFk,
    string $lookupTable,
    string $lookupId,
    string $lookupLabel,
    ?array $via = null,
): array {
    return [
        'type' => 'child_lookup',
        'child_table' => $childTable,
        'child_owner' => 'resident_id',
        'child_fk' => $childFk,
        'lookup_table' => $lookupTable,
        'lookup_id' => $lookupId,
        'lookup_label' => $lookupLabel,
        'via_table' => $via['table'] ?? null,
        'via_from' => $via['from'] ?? null,
        'via_to' => $via['to'] ?? null,
        'via_owner' => $via['owner'] ?? null,
    ];
};

$levelModes = [
    'all' => 'All Levels',
    'one' => 'Per Level',
    'multiple' => 'Select 1 or More Levels',
];

$incomeModes = [
    'all' => 'All Income',
    'one' => 'Per Income',
    'multiple' => 'Select 1 or More Income',
];

$statusModes = $attributeModes('Status');

$typeModes = [
    'all' => 'All Types',
    'one' => 'Per Type',
    'multiple' => 'Select 1 or More Types',
];

$skillModes = [
    'all' => 'All Skills',
    'one' => 'Per Skills',
    'multiple' => 'Select 1 or More Skills',
];

$skillTypeModes = [
    'all' => 'All Type',
    'one' => 'Per Type',
    'multiple' => 'Select 1 or More Type',
];

$insuranceModes = [
    'none' => 'No Insurance',
    'all' => 'All Insurance (including no insurance)',
    'one' => 'Per Insurance',
    'multiple' => 'Select 1 or More Insurance',
];

$facilityModes = [
    'none' => 'None',
    'all' => 'All Facility (including none)',
    'one' => 'Per Facility',
    'multiple' => 'Select 1 or More Facility',
];

$reasonModes = [
    'all' => 'All Reason',
    'one' => 'Per Reason',
    'multiple' => 'Select 1 or More Reason',
];

$placeModes = [
    'all' => 'All Places',
    'one' => 'Per Place',
    'multiple' => 'Select 1 or More Places',
];

$attendantModes = [
    'all' => 'All Attendants',
    'one' => 'Per Attendant',
    'multiple' => 'Select 1 or More Attendants',
];

$immunizationModes = [
    'none' => 'No Immunization',
    'all' => 'All Immunization (including no immunization)',
    'one' => 'Per Immunization',
    'multiple' => 'Select 1 or More Immunization',
];

$fpModes = [
    'none' => 'No FP',
    'all' => 'All FP (including no FP)',
    'one' => 'Per FP',
    'multiple' => 'Select 1 or More FP',
];

$fpSourceModes = [
    'all' => 'All FP Source',
    'one' => 'Per FP Source',
    'multiple' => 'Select 1 or More FP Source',
];

$womenHealthVia = [
    'table' => 'health',
    'from' => 'health_id',
    'to' => 'health_id',
    'owner' => 'resident_id',
];

return [
    'clan' => $category('Clan', [
        $lookup(
            'clan',
            'Clan',
            'clan_id',
            'household',
            Clan::class,
            'clan',
            'clan_id',
            'clan_name',
            ['clan'],
            $sortLookup('clan', 'sort_clan', 'clan_id', 'clan_name', 'household.clan_id'),
        ),
    ]),

    'street' => $category('Street', [
        $lookup(
            'street',
            'Street',
            'street_id',
            'household',
            Street::class,
            'street',
            'street_id',
            'street_name',
            ['street'],
            $sortLookup('street', 'sort_street', 'street_id', 'street_name', 'household.street_id'),
        ),
    ]),

    'house_story_basement' => $category('House Story/Basement', [
        $choices(
            'story',
            'Story',
            'options',
            'household',
            $storyChoices,
            ['number_of_house_story'],
            ['type' => 'expression', 'sql' => 'household.number_of_house_story'],
            'household.number_of_house_story',
        ),
        $choices(
            'basement',
            'Basement',
            'options',
            'household',
            $basementChoices,
            ['number_of_basement_level'],
            ['type' => 'expression', 'sql' => 'COALESCE(household.number_of_basement_level, 0)'],
            'COALESCE(household.number_of_basement_level, 0)',
        ),
    ]),

    'household_status' => $category('Household Status', [
        $lookup(
            'household_status',
            'Household Status',
            'household_status_id',
            'household',
            HouseholdStatus::class,
            'household_status',
            'household_status_id',
            'household_status',
            ['household_status'],
            $sortLookup('household_status', 'sort_household_status', 'household_status_id', 'household_status', 'household.household_status_id'),
            ['plural' => 'Household Statuses'],
        ),
    ]),

    'ownership' => $category('Ownership', [
        $lookup(
            'unit',
            'Housing Unit',
            'ownership_of_housing_unit_id',
            'hq',
            OwnershipType::class,
            'ownership_type',
            'ownership_type_id',
            'ownership_type',
            ['household_questions.ownership_of_housing_unit'],
            $sortLookup('ownership_type', 'sort_unit_ownership', 'ownership_type_id', 'ownership_type', 'hq.ownership_of_housing_unit_id'),
            ['plural' => 'Housing Units'],
        ),
        $lookup(
            'lot',
            'Lot',
            'ownership_of_lot_id',
            'hq',
            OwnershipType::class,
            'ownership_type',
            'ownership_type_id',
            'ownership_type',
            ['household_questions.ownership_of_lot'],
            $sortLookup('ownership_type', 'sort_lot_ownership', 'ownership_type_id', 'ownership_type', 'hq.ownership_of_lot_id'),
        ),
    ], ['questions' => true]),

    'fuel' => $category('Fuel', [
        $lookup(
            'lighting',
            'Lighting',
            'fuel_type_for_lighting_id',
            'hq',
            FuelType::class,
            'fuel_type',
            'fuel_type_id',
            'fuel_type',
            ['household_questions.fuel_type_for_lighting'],
            $sortLookup('fuel_type', 'sort_fuel_lighting', 'fuel_type_id', 'fuel_type', 'hq.fuel_type_for_lighting_id'),
        ),
        $lookup(
            'cooking',
            'Cooking',
            'fuel_type_for_cooking_id',
            'hq',
            FuelType::class,
            'fuel_type',
            'fuel_type_id',
            'fuel_type',
            ['household_questions.fuel_type_for_cooking'],
            $sortLookup('fuel_type', 'sort_fuel_cooking', 'fuel_type_id', 'fuel_type', 'hq.fuel_type_for_cooking_id'),
        ),
    ], ['questions' => true]),

    'water_source' => $category('Water Source', [
        $lookup(
            'water_source',
            'Water Source',
            'main_source_drinking_water_id',
            'hq',
            WaterSource::class,
            'water_source',
            'water_source_id',
            'water_source',
            ['household_questions.main_source_drinking_water'],
            $sortLookup('water_source', 'sort_water_source', 'water_source_id', 'water_source', 'hq.main_source_drinking_water_id'),
        ),
    ], ['questions' => true]),

    'kitchen_garbage' => $category('Kitchen Garbage', [
        $lookup(
            'kitchen_garbage',
            'Kitchen Garbage Disposal',
            'kitchen_garbage_disposal_id',
            'hq',
            KitchenGarbageDisposal::class,
            'kitchen_garbage_disposal',
            'kitchen_garbage_disposal_id',
            'kitchen_garbage_disposal',
            ['household_questions.kitchen_garbage_disposal'],
            $sortLookup('kitchen_garbage_disposal', 'sort_kitchen_garbage', 'kitchen_garbage_disposal_id', 'kitchen_garbage_disposal', 'hq.kitchen_garbage_disposal_id'),
            ['plural' => 'Kitchen Garbage Disposals'],
        ),
    ], ['questions' => true]),

    'segregate_garbage' => $category('Garbage Segregation', [
        $choices(
            'segregate',
            'Garbage Segregation',
            'boolean',
            'hq',
            [
                ['value' => 'yes', 'label' => 'Segregates', 'op' => 'eq', 'operand' => 1, 'singular' => true],
                ['value' => 'no', 'label' => 'Does Not Segregate', 'op' => 'eq', 'operand' => 0, 'singular' => true],
            ],
            ['household_questions.perform_garbage_seggragation'],
            null,
            'hq.perform_garbage_seggragation',
            'perform_garbage_seggragation',
        ),
    ], ['questions' => true]),

    'toilet_facility' => $category('Toilet Facility', [
        $lookup(
            'toilet_facility',
            'Toilet Facility',
            'toilet_facility_type_id',
            'hq',
            ToiletFacilityType::class,
            'toilet_facility_type',
            'toilet_facility_type_id',
            'toilet_facility_type',
            ['household_questions.toilet_facility_type'],
            $sortLookup('toilet_facility_type', 'sort_toilet', 'toilet_facility_type_id', 'toilet_facility_type', 'hq.toilet_facility_type_id'),
            ['plural' => 'Toilet Facilities'],
        ),
    ], ['questions' => true]),

    'building_type' => $category('Building Type', [
        $lookup(
            'building_type',
            'Building Type',
            'type_of_building_house_id',
            'hq',
            BuildingHouseType::class,
            'building_house_type',
            'building_house_type_id',
            'building_house_type',
            ['household_questions.type_of_building_house'],
            $sortLookup('building_house_type', 'sort_building', 'building_house_type_id', 'building_house_type', 'hq.type_of_building_house_id'),
        ),
    ], ['questions' => true]),

    'construction_material' => $category('Construction Material', [
        $lookup(
            'construction_material',
            'Construction Material',
            'construction_material_outer_wall_id',
            'hq',
            ConstructionMaterialOuterWall::class,
            'construction_material_outer_wall',
            'construction_material_outer_wall_id',
            'construction_material_outer_wall',
            ['household_questions.construction_material_outer_wall'],
            $sortLookup(
                'construction_material_outer_wall',
                'sort_construction',
                'construction_material_outer_wall_id',
                'construction_material_outer_wall',
                'hq.construction_material_outer_wall_id',
            ),
            ['plural' => 'Construction Materials'],
        ),
    ], ['questions' => true]),

    'female_died' => $category('Female Died', [
        $choices(
            'female_died',
            'Female Household Member Died',
            'boolean',
            'presence',
            [
                ['value' => 'has', 'label' => 'Has', 'op' => 'exists', 'singular' => true],
                ['value' => 'none', 'label' => 'Does not have', 'op' => 'missing', 'singular' => true],
            ],
            [],
            null,
            null,
            null,
            ['presence_table' => 'female_hhm_died', 'presence_owner' => 'household_question_id'],
        ),
    ], [
        'questions' => true,
        'default_columns' => $deathDefaults,
        'extra_columns' => ['female_death_details', 'household_members_health'],
    ]),

    'child_died' => $category('Child Died', [
        $choices(
            'child_died',
            'Child Household Member Died',
            'boolean',
            'presence',
            [
                ['value' => 'has', 'label' => 'Has', 'op' => 'exists', 'singular' => true],
                ['value' => 'none', 'label' => 'Does not have', 'op' => 'missing', 'singular' => true],
            ],
            [],
            null,
            null,
            null,
            ['presence_table' => 'child_hhm_died', 'presence_owner' => 'household_question_id'],
        ),
    ], [
        'questions' => true,
        'default_columns' => $deathDefaults,
        'extra_columns' => ['child_death_details', 'household_members_health'],
    ]),

    'common_disease' => $category('Common Diseases', [
        $lookup(
            'disease',
            'Common Disease',
            'common_disease_id',
            'junction',
            CommonDiseaseCauseDeathInBrgy::class,
            'common_disease_cause_death_in_brgy',
            'common_disease_id',
            'common_disease',
            ['household_questions.common_diseases'],
            [
                'type' => 'junction_min',
                'junction_table' => 'common_disease',
                'junction_owner' => 'household_question_id',
                'junction_id' => 'common_disease_id',
                'lookup_table' => 'common_disease_cause_death_in_brgy',
                'lookup_id' => 'common_disease_id',
                'lookup_label' => 'common_disease',
            ],
            [
                'junction_table' => 'common_disease',
                'junction_owner' => 'household_question_id',
                'junction_id' => 'common_disease_id',
                'constrain_relation' => 'questions.commonDiseases',
                'constrain_column' => 'common_disease_cause_death_in_brgy.common_disease_id',
                'constrain_order' => 'common_disease',
            ],
        ),
    ], [
        'questions' => true,
        'default_columns' => $diseaseDefaults,
        'extra_columns' => ['total_diseases_recorded', 'household_members_health'],
        'counts' => [[
            'column' => 'total_diseases_recorded',
            'alias' => 'matching_diseases_count',
            'type' => 'junction',
            'junction_table' => 'common_disease',
            'junction_owner' => 'household_question_id',
            'junction_id' => 'common_disease_id',
            'filter' => 'disease',
        ]],
    ]),

    'primary_need' => $category('Primary Needs', [
        $lookup(
            'need',
            'Primary Need',
            'primary_need_id',
            'junction',
            PrimaryNeedOfBrgy::class,
            'primary_need_of_brgy',
            'primary_need_id',
            'primary_need',
            ['household_questions.primary_needs'],
            [
                'type' => 'junction_min',
                'junction_table' => 'primary_need',
                'junction_owner' => 'household_question_id',
                'junction_id' => 'primary_need_id',
                'lookup_table' => 'primary_need_of_brgy',
                'lookup_id' => 'primary_need_id',
                'lookup_label' => 'primary_need',
            ],
            [
                'junction_table' => 'primary_need',
                'junction_owner' => 'household_question_id',
                'junction_id' => 'primary_need_id',
                'constrain_relation' => 'questions.primaryNeeds',
                'constrain_column' => 'primary_need_of_brgy.primary_need_id',
                'constrain_order' => 'primary_need',
            ],
        ),
    ], [
        'questions' => true,
        'default_columns' => $needDefaults,
        'extra_columns' => ['total_primary_needs_recorded', 'household_members_health'],
        'counts' => [[
            'column' => 'total_primary_needs_recorded',
            'alias' => 'matching_needs_count',
            'type' => 'junction',
            'junction_table' => 'primary_need',
            'junction_owner' => 'household_question_id',
            'junction_id' => 'primary_need_id',
            'filter' => 'need',
        ]],
    ]),

    'pets' => $category('Pets', [
        $lookup(
            'specie',
            'Specie',
            'specie_id',
            'pet',
            Specie::class,
            'specie',
            'specie_id',
            'specie',
            ['pet_details.specie'],
            [
                'type' => 'pet_min',
                'lookup_table' => 'specie',
                'lookup_id' => 'specie_id',
                'lookup_label' => 'specie',
                'fk' => 'specie_id',
            ],
            ['plural' => 'Species'],
        ),
        $lookup(
            'breed',
            'Breed',
            'breed_id',
            'pet',
            Breed::class,
            'breed',
            'breed_id',
            'breed',
            ['pet_details.breed'],
            [
                'type' => 'pet_min',
                'lookup_table' => 'breed',
                'lookup_id' => 'breed_id',
                'lookup_label' => 'breed',
                'fk' => 'breed_id',
            ],
            ['modes' => ['all']],
        ),
        $choices(
            'sex',
            'Sex',
            'options',
            'pet',
            [
                ['value' => 'all', 'label' => 'All', 'op' => 'any', 'singular' => false],
                ['value' => 'male', 'label' => 'Male', 'op' => 'eq', 'operand' => Sex::MALE, 'singular' => true],
                ['value' => 'female', 'label' => 'Female', 'op' => 'eq', 'operand' => Sex::FEMALE, 'singular' => true],
            ],
            ['pet_details.sex'],
            [
                'type' => 'pet_min',
                'lookup_table' => 'sex',
                'lookup_id' => 'sex_id',
                'lookup_label' => 'sex',
                'fk' => 'sex_id',
            ],
            'pet_census.sex_id',
            'sex_id',
        ),
        $choices(
            'spay_neuter',
            'Spay/Neuter',
            'options',
            'pet',
            [
                ['value' => 'all', 'label' => 'All', 'op' => 'any', 'singular' => false],
                ['value' => 'yes', 'label' => 'Yes', 'op' => 'eq', 'operand' => 1, 'singular' => true],
                ['value' => 'no', 'label' => 'No', 'op' => 'eq', 'operand' => 0, 'singular' => true],
            ],
            ['pet_details.is_spay_neuter'],
            ['type' => 'pet_column', 'column' => 'is_spay_neuter'],
            'pet_census.is_spay_neuter',
            'is_spay_neuter',
        ),
        $choices(
            'rabies',
            'Rabies',
            'options',
            'pet',
            $rabiesChoices,
            ['pet_details.rabies_vaccination_date'],
            ['type' => 'pet_column', 'column' => 'rabies_vaccination_date'],
            'pet_census.rabies_vaccination_date',
            'rabies_vaccination_date',
        ),
    ], [
        'default_columns' => $petsDefaults,
        'extra_columns' => ['total_pets_recorded'],
        'counts' => [[
            'column' => 'total_pets_recorded',
            'alias' => 'matching_pets_count',
            'type' => 'pet',
        ]],
    ]),

    'nationality' => $residentAttribute(
        'Nationality',
        'nationality',
        'Nationality',
        'nationality_id',
        Nationality::class,
        'nationality',
        'nationality_id',
        'nationality',
        'resident_demographic.nationality',
    ),

    'religion' => $residentAttribute(
        'Religion',
        'religion',
        'Religion',
        'religion_id',
        Religion::class,
        'religion',
        'religion_id',
        'religion',
        'resident_demographic.religion',
    ),

    'ethnicity' => $residentAttribute(
        'Ethnicity',
        'ethnicity',
        'Ethnicity',
        'ethnicity_id',
        Ethnicity::class,
        'ethnicity',
        'ethnicity_id',
        'ethnicity',
        'resident_demographic.ethnicity',
    ),

    'marital_status' => $residentAttribute(
        'Marital Status',
        'marital_status',
        'Status',
        'marital_status_id',
        MaritalStatus::class,
        'marital_status',
        'marital_status_id',
        'marital_status',
        'resident_demographic.marital_status',
    ),

    'resident_status' => $residentAttribute(
        'Resident Status',
        'resident_status',
        'Status',
        'resident_status_id',
        ResidentStatus::class,
        'resident_status',
        'resident_status_id',
        'resident_status',
        'resident_demographic.resident_status',
    ),

    'education' => $residentCategory('Education', [
        $lookup(
            'highest_level',
            'Highest Level of Education',
            'highest_lvl_of_educ_id',
            'subrecord',
            HighestLvlOfEduc::class,
            'highest_lvl_of_educ',
            'highest_lvl_of_educ_id',
            'lvl_of_educ',
            ['education_details.highest_level'],
            $childSort('education', 'highest_lvl_of_educ_id', 'highest_lvl_of_educ', 'highest_lvl_of_educ_id', 'lvl_of_educ'),
            [
                'mode_labels' => $levelModes,
                'subrecord_table' => 'education',
                'subrecord_owner' => 'resident_id',
            ],
        ),
        $choices(
            'enrollment',
            'Current Enrollment Status',
            'options',
            'subrecord',
            [
                ['value' => 'all', 'label' => 'All', 'op' => 'any', 'singular' => false],
                ['value' => 'no', 'label' => 'No', 'op' => 'eq', 'operand' => CurrentEnrollmentStatus::NOT_ENROLLED, 'singular' => true],
                ['value' => 'yes', 'label' => 'Yes', 'op' => 'in', 'operand' => [CurrentEnrollmentStatus::YES_PUBLIC, CurrentEnrollmentStatus::YES_PRIVATE], 'singular' => false],
                ['value' => 'yes_private', 'label' => 'Yes, private', 'op' => 'eq', 'operand' => CurrentEnrollmentStatus::YES_PRIVATE, 'singular' => true],
                ['value' => 'yes_public', 'label' => 'Yes, public', 'op' => 'eq', 'operand' => CurrentEnrollmentStatus::YES_PUBLIC, 'singular' => true],
            ],
            ['education_details.current_enrollment'],
            $childSort(
                'education',
                'current_enrollement_status_id',
                'current_enrollment_status',
                'current_enrollment_status_id',
                'current_enrollement_status',
            ),
            'education.current_enrollement_status_id',
            'current_enrollement_status_id',
            [
                'subrecord_table' => 'education',
                'subrecord_owner' => 'resident_id',
            ],
        ),
        $lookup(
            'school_level',
            'School Level',
            'school_lvl_id',
            'subrecord',
            SchoolLvl::class,
            'school_lvl',
            'school_lvl_id',
            'school_lvl',
            ['education_details.school_lvl'],
            $childSort('education', 'school_lvl_id', 'school_lvl', 'school_lvl_id', 'school_lvl'),
            [
                'mode_labels' => $levelModes,
                'subrecord_table' => 'education',
                'subrecord_owner' => 'resident_id',
                'visible_when' => [
                    'filter' => 'enrollment',
                    'except_modes' => ['no'],
                ],
            ],
        ),
    ], ['also_default' => ['education_details']]),

    'economic' => $residentCategory('Economic', [
        [
            'key' => 'monthly_income',
            'label' => 'Monthly Income',
            'type' => 'range',
            'apply' => 'subrecord',
            'column' => 'monthly_income',
            'scale' => Economic::MONTHLY_INCOME_SCALE,
            'min_bound' => 0,
            'max_bound' => Economic::MAX_MONTHLY_INCOME,
            'owns' => [],
            'sort' => null,
            'subrecord_table' => 'economic',
            'subrecord_owner' => 'resident_id',
        ],
        $lookup(
            'source',
            'Source of Income',
            'source_of_income_id',
            'subrecord',
            SourceOfIncome::class,
            'source_of_income',
            'source_of_income_id',
            'source_of_income',
            ['economic_details.source_of_income'],
            $childSort('economic', 'source_of_income_id', 'source_of_income', 'source_of_income_id', 'source_of_income'),
            [
                'mode_labels' => $incomeModes,
                'plural' => 'Income',
                'subrecord_table' => 'economic',
                'subrecord_owner' => 'resident_id',
            ],
        ),
        $lookup(
            'work_status',
            'Status of Work/Business',
            'status_of_work_business_id',
            'subrecord',
            StatusOfWorkBusiness::class,
            'status_of_work_business',
            'status_of_work_business_id',
            'status_of_work_business',
            ['economic_details.work_status'],
            $childSort(
                'economic',
                'status_of_work_business_id',
                'status_of_work_business',
                'status_of_work_business_id',
                'status_of_work_business',
            ),
            [
                'mode_labels' => $statusModes,
                'plural' => 'Status',
                'subrecord_table' => 'economic',
                'subrecord_owner' => 'resident_id',
                'visible_when' => [
                    'filter' => 'source',
                    'lookup_ids' => [SourceOfIncome::EMPLOYMENT, SourceOfIncome::BUSINESS],
                ],
            ],
        ),
    ], ['also_default' => ['economic_details']]),

    'health' => $residentCategory('Health', [
        $lookup(
            'health_insurance',
            'Health Insurance',
            'health_insurance_id',
            'subrecord',
            HealthInsurance::class,
            'health_insurance',
            'health_insurance_id',
            'health_insurance',
            ['health_details.health_insurance'],
            $childSort('health', 'health_insurance_id', 'health_insurance', 'health_insurance_id', 'health_insurance'),
            [
                'modes' => ['none', 'all', 'one', 'multiple'],
                'mode_labels' => $insuranceModes,
                'sentinel_mode' => 'none',
                'omit_sentinel_from' => ['one'],
                'subrecord_table' => 'health',
                'subrecord_owner' => 'resident_id',
            ],
        ),
        $lookup(
            'facility_visited',
            'Facility Visited Past 12 Months',
            'facility_visited_past_12mos_id',
            'subrecord',
            FacilityVisitedPast12Mos::class,
            'facility_visited_past_12mos',
            'facility_visited_past_12mos_id',
            'facility_visited_past_12mos',
            ['health_details.facility_visited_past_12mos'],
            $childSort(
                'health',
                'facility_visited_past_12mos_id',
                'facility_visited_past_12mos',
                'facility_visited_past_12mos_id',
                'facility_visited_past_12mos',
            ),
            [
                'modes' => ['none', 'all', 'one', 'multiple'],
                'mode_labels' => $facilityModes,
                'sentinel_mode' => 'none',
                'omit_sentinel_from' => ['one'],
                'subrecord_table' => 'health',
                'subrecord_owner' => 'resident_id',
            ],
        ),
        $lookup(
            'facility_visit_reason',
            'Facility Visit Reason',
            'facility_visit_reason_id',
            'subrecord',
            FacilityVisitReason::class,
            'facility_visit_reason',
            'facility_visit_reason_id',
            'facility_visit_reason',
            ['health_details.facility_visit_reason'],
            $childSort(
                'health',
                'facility_visit_reason_id',
                'facility_visit_reason',
                'facility_visit_reason_id',
                'facility_visit_reason',
            ),
            [
                'mode_labels' => $reasonModes,
                'subrecord_table' => 'health',
                'subrecord_owner' => 'resident_id',
                'visible_when' => [
                    'filter' => 'facility_visited',
                    'unless_sentinel' => true,
                ],
            ],
        ),
        $choices(
            'disability',
            'Disability',
            'options',
            'subrecord',
            [
                ['value' => 'all', 'label' => 'With/Without Disability', 'op' => 'any', 'singular' => false],
                ['value' => 'with', 'label' => 'With Disability', 'op' => 'not_sentinel', 'singular' => false],
                ['value' => 'without', 'label' => 'Without Disability', 'op' => 'is_sentinel', 'singular' => true],
            ],
            ['health_details.disability'],
            $childSort('health', 'disability_id', 'disability', 'disability_id', 'disability'),
            'health.disability_id',
            'disability_id',
            [
                'subrecord_table' => 'health',
                'subrecord_owner' => 'resident_id',
                'sentinel_model' => Disability::class,
            ],
        ),
    ], ['also_default' => ['health_details']]),

    'infant_health' => $residentCategory('Infant Health', [
        $lookup(
            'place_of_delivery',
            'Place of Delivery',
            'place_of_delivery_id',
            'subrecord',
            PlaceOfDelivery::class,
            'place_of_delivery',
            'place_of_delivery_id',
            'place_of_delivery',
            ['infant_health_details.place_of_delivery'],
            $childSort('infant_health', 'place_of_delivery_id', 'place_of_delivery', 'place_of_delivery_id', 'place_of_delivery'),
            [
                'mode_labels' => $placeModes,
                'plural' => 'Places',
                'subrecord_table' => 'infant_health',
                'subrecord_owner' => 'resident_id',
            ],
        ),
        $lookup(
            'birth_attendant',
            'Birth Attendant',
            'birth_attendant_id',
            'subrecord',
            BirthAttendant::class,
            'birth_attendant',
            'birth_attendant_id',
            'birth_attendant',
            ['infant_health_details.birth_attendant'],
            $childSort('infant_health', 'birth_attendant_id', 'birth_attendant', 'birth_attendant_id', 'birth_attendant'),
            [
                'mode_labels' => $attendantModes,
                'plural' => 'Attendants',
                'subrecord_table' => 'infant_health',
                'subrecord_owner' => 'resident_id',
            ],
        ),
        $lookup(
            'immunization',
            'Immunization',
            'immunization_id',
            'subrecord',
            Immunization::class,
            'immunization',
            'immunization_id',
            'immunization',
            ['infant_health_details.immunization'],
            $childSort('infant_health', 'immunization_id', 'immunization', 'immunization_id', 'immunization'),
            [
                'modes' => ['none', 'all', 'one', 'multiple'],
                'mode_labels' => $immunizationModes,
                'sentinel_mode' => 'none',
                'omit_sentinel_from' => ['one'],
                'subrecord_table' => 'infant_health',
                'subrecord_owner' => 'resident_id',
            ],
        ),
    ], [
        'header' => 'Total Resident Records (scoped to infants 0-11 months)',
        'audience' => 'infant',
        'also_default' => ['infant_health_details'],
    ]),

    'women_health' => $residentCategory('Women Health', [
        [
            'key' => 'pregnancies',
            'label' => 'No. of Pregnancies',
            'type' => 'range',
            'apply' => 'subrecord',
            'column' => 'number_pregnancies',
            'scale' => 0,
            'min_bound' => 0,
            'max_bound' => 99,
            'owns' => ['women_health_details.number_pregnancies'],
            'sort' => null,
            'subrecord_table' => 'women_health',
            'subrecord_owner' => 'resident_id',
            'subrecord_via' => $womenHealthVia,
        ],
        [
            'key' => 'living_children',
            'label' => 'Living Children',
            'type' => 'range',
            'apply' => 'subrecord',
            'column' => 'living_children',
            'scale' => 0,
            'min_bound' => 0,
            'max_bound' => 99,
            'owns' => ['women_health_details.living_children'],
            'sort' => null,
            'subrecord_table' => 'women_health',
            'subrecord_owner' => 'resident_id',
            'subrecord_via' => $womenHealthVia,
        ],
        $lookup(
            'family_planning_method',
            'Family Planning Method',
            'family_planning_method_id',
            'subrecord',
            FamilyPlanningMethod::class,
            'family_planning_method',
            'family_planning_method_id',
            'family_planning_method',
            ['women_health_details.family_planning_method'],
            $childSort(
                'women_health',
                'family_planning_method_id',
                'family_planning_method',
                'family_planning_method_id',
                'family_planning_method',
                $womenHealthVia,
            ),
            [
                'modes' => ['none', 'all', 'one', 'multiple'],
                'mode_labels' => $fpModes,
                'sentinel_mode' => 'none',
                'omit_sentinel_from' => ['one'],
                'subrecord_table' => 'women_health',
                'subrecord_owner' => 'resident_id',
                'subrecord_via' => $womenHealthVia,
            ],
        ),
        $lookup(
            'source_of_fp_method',
            'Source of FP',
            'source_of_fp_method_id',
            'subrecord',
            SourceOfFPMethod::class,
            'source_of_fp_method',
            'source_of_fp_method_id',
            'source_of_fp_method',
            ['women_health_details.source_of_fp_method'],
            $childSort(
                'women_health',
                'source_of_fp_method_id',
                'source_of_fp_method',
                'source_of_fp_method_id',
                'source_of_fp_method',
                $womenHealthVia,
            ),
            [
                'mode_labels' => $fpSourceModes,
                'subrecord_table' => 'women_health',
                'subrecord_owner' => 'resident_id',
                'subrecord_via' => $womenHealthVia,
                'visible_when' => [
                    'filter' => 'family_planning_method',
                    'unless_sentinel' => true,
                ],
            ],
        ),
        $choices(
            'fp_intention',
            'Intention to Use FP',
            'options',
            'subrecord',
            [
                ['value' => 'yes', 'label' => 'Have Intention', 'op' => 'eq', 'operand' => 1, 'singular' => true],
                ['value' => 'no', 'label' => 'No Intention', 'op' => 'eq', 'operand' => 0, 'singular' => true],
            ],
            ['women_health_details.have_intention_to_use_fp'],
            null,
            'women_health.have_intention_to_use_fp',
            'have_intention_to_use_fp',
            [
                'subrecord_table' => 'women_health',
                'subrecord_owner' => 'resident_id',
                'subrecord_via' => $womenHealthVia,
                'visible_when' => [
                    'filter' => 'family_planning_method',
                    'only_sentinel' => true,
                ],
            ],
        ),
    ], [
        'header' => 'Total Resident Records (scoped to females aged 10-54)',
        'audience' => 'women',
        'also_default' => ['women_health_details'],
    ]),

    'sociocivic' => $residentCategory('Sociocivic', [
        $choices(
            'topics',
            'Categories',
            'checks',
            'none',
            [
                ['value' => 1, 'label' => 'Solo Parents', 'op' => 'any', 'singular' => false],
                ['value' => 2, 'label' => 'Senior Citizens', 'op' => 'any', 'singular' => false],
                ['value' => 3, 'label' => 'Registered Barangay Voters', 'op' => 'any', 'singular' => false],
            ],
            [],
            null,
        ),
        $lookup(
            'solo_parent_status',
            'Solo Parent Status',
            'solo_parent_status_id',
            'subrecord',
            SoloParentStatus::class,
            'solo_parent_status',
            'solo_parent_status_id',
            'solo_parent_status',
            ['sociocivic_details.solo_parent_status'],
            $childSort('sociocivic', 'solo_parent_status_id', 'solo_parent_status', 'solo_parent_status_id', 'solo_parent_status'),
            [
                'mode_labels' => $statusModes,
                'plural' => 'Statuses',
                'subrecord_table' => 'sociocivic',
                'subrecord_owner' => 'resident_id',
                'visible_when' => [
                    'filter' => 'topics',
                    'include_ids' => [1],
                ],
            ],
        ),
        $choices(
            'registered_senior',
            'Registered Senior Citizen',
            'options',
            'column_presence',
            [
                ['value' => 'all', 'label' => 'Yes/No', 'op' => 'any', 'singular' => false],
                ['value' => 'yes', 'label' => 'Yes', 'op' => 'not_null', 'singular' => true],
                ['value' => 'no', 'label' => 'No', 'op' => 'missing_or_null', 'singular' => true],
            ],
            ['sociocivic_details.registered_senior_citizen'],
            null,
            null,
            'osca_id_number',
            [
                'subrecord_table' => 'sociocivic',
                'subrecord_owner' => 'resident_id',
                'visible_when' => [
                    'filter' => 'topics',
                    'include_ids' => [2],
                ],
            ],
        ),
        $choices(
            'registered_barangay_voter',
            'Registered Barangay Voter',
            'options',
            'voter',
            [
                ['value' => 'any', 'label' => 'Yes/No', 'op' => 'any', 'singular' => false],
                ['value' => 'no', 'label' => 'No', 'op' => 'voter_no', 'singular' => true],
                ['value' => 'yes', 'label' => 'Yes', 'op' => 'voter_yes', 'singular' => false],
                ['value' => 'local', 'label' => 'Yes, registered in barangay happy hallow', 'op' => 'voter_local', 'singular' => true],
                ['value' => 'other', 'label' => 'Yes, registered in different barangay', 'op' => 'voter_other', 'singular' => true],
            ],
            ['sociocivic_details.registered_barangay_voter'],
            null,
            null,
            'registered_barangay_voter',
            [
                'subrecord_table' => 'sociocivic',
                'subrecord_owner' => 'resident_id',
                'visible_when' => [
                    'filter' => 'topics',
                    'include_ids' => [3],
                ],
            ],
        ),
    ], ['also_default' => ['sociocivic_details']]),

    'migration' => $residentCategory('Migration', [
        $lookup(
            'resident_type',
            'Resident Type',
            'resident_type_id',
            'subrecord',
            ResidentType::class,
            'resident_type',
            'resident_type_id',
            'resident_type',
            ['migration_details.resident_type'],
            $childSort('migration', 'resident_type_id', 'resident_type', 'resident_type_id', 'resident_type'),
            [
                'mode_labels' => $typeModes,
                'plural' => 'Resident Types',
                'subrecord_table' => 'migration',
                'subrecord_owner' => 'resident_id',
            ],
        ),
        $lookup(
            'reason_for_leaving',
            'Reason for Leaving',
            'reason_for_leaving_id',
            'subrecord',
            ReasonForLeaving::class,
            'reason_for_leaving',
            'reason_for_leaving_id',
            'reason_for_leaving',
            ['migration_details.reason_for_leaving'],
            $childSort('migration', 'reason_for_leaving_id', 'reason_for_leaving', 'reason_for_leaving_id', 'reason_for_leaving'),
            [
                'mode_labels' => $reasonModes,
                'plural' => 'Reasons for Leaving',
                'subrecord_table' => 'migration',
                'subrecord_owner' => 'resident_id',
            ],
        ),
        $lookup(
            'reason_for_transfer',
            'Reason for Transfer',
            'reason_for_transfer_id',
            'subrecord',
            ReasonForTransfer::class,
            'reason_for_transfer',
            'reason_for_transfer_id',
            'reason_for_transfer',
            ['migration_details.reason_for_transfer'],
            $childSort('migration', 'reason_for_transfer_id', 'reason_for_transfer', 'reason_for_transfer_id', 'reason_for_transfer'),
            [
                'mode_labels' => $reasonModes,
                'plural' => 'Reasons for Transfer',
                'subrecord_table' => 'migration',
                'subrecord_owner' => 'resident_id',
            ],
        ),
    ], ['also_default' => ['migration_details']]),

    'ctc' => $residentCategory('Community Tax Certificate', [
        $choices(
            'has_valid_ctc',
            'Has Valid CTC',
            'boolean',
            'subrecord',
            [
                ['value' => 'yes', 'label' => 'Yes', 'op' => 'eq', 'operand' => 1, 'singular' => false],
                ['value' => 'no', 'label' => 'No', 'op' => 'eq', 'operand' => 0, 'singular' => false],
            ],
            [],
            null,
            'community_tax_cert.has_valid_ctc',
            'has_valid_ctc',
            [
                'subrecord_table' => 'community_tax_cert',
                'subrecord_owner' => 'resident_id',
            ],
        ),
        $choices(
            'ctc_issued_here',
            'CTC Issued Here',
            'boolean',
            'subrecord',
            [
                ['value' => 'yes', 'label' => 'Yes', 'op' => 'eq', 'operand' => 1, 'singular' => false],
                ['value' => 'no', 'label' => 'No', 'op' => 'eq', 'operand' => 0, 'singular' => false],
            ],
            [],
            null,
            'community_tax_cert.ctc_issued_here',
            'ctc_issued_here',
            [
                'subrecord_table' => 'community_tax_cert',
                'subrecord_owner' => 'resident_id',
            ],
        ),
    ], [
        'header' => 'Total Resident Records (scoped to residents 18+)',
        'audience' => 'ctc',
        'also_default' => ['ctc_details'],
    ]),

    'skills' => $residentCategory('Skills', [
        $lookup(
            'training',
            'Skills Development Training',
            'skills_development_training',
            'subrecord',
            SkillsDevelopment::class,
            'skills_development',
            'skills_development_id',
            'skills_development_training',
            ['skills_details.skills_development_training'],
            [
                'type' => 'child_column',
                'child_table' => 'skills_development',
                'child_owner' => 'resident_id',
                'column' => 'skills_development_training',
            ],
            [
                'mode_labels' => $skillModes,
                'plural' => 'Skills',
                'distinct_label' => true,
                'subrecord_table' => 'skills_development',
                'subrecord_owner' => 'resident_id',
            ],
        ),
        $lookup(
            'skill_type',
            'Skill Type',
            'skill_type_id',
            'subrecord',
            SkillType::class,
            'skill_type',
            'skill_type_id',
            'skill_type',
            ['skills_details.skill_type'],
            $childSort('skills_development', 'skill_type_id', 'skill_type', 'skill_type_id', 'skill_type'),
            [
                'mode_labels' => $skillTypeModes,
                'plural' => 'Type',
                'subrecord_table' => 'skills_development',
                'subrecord_owner' => 'resident_id',
            ],
        ),
    ], [
        'header' => 'Total Resident Records (scoped to residents 15+)',
        'audience' => 'skills',
        'also_default' => ['skills_details'],
    ]),

    'resident_cross_filter' => $residentCategory('Age', [
        [
            'key' => 'age',
            'label' => 'Age',
            'type' => 'range',
            'apply' => 'age',
            'column' => 'date_of_birth',
            'scale' => 0,
            'min_bound' => 0,
            'max_bound' => 150,
            'plural' => 'Ages',
            'owns' => [],
            'sort' => null,
        ],
        $lookup(
            'marital_status',
            'Marital Status',
            'marital_status_id',
            'resident',
            MaritalStatus::class,
            'marital_status',
            'marital_status_id',
            'marital_status',
            ['resident_demographic.marital_status'],
            $sortLookup('marital_status', 'sort_marital_status', 'marital_status_id', 'marital_status', 'resident.marital_status_id'),
            ['mode_labels' => $statusModes],
        ),
        $choices(
            'disability',
            'Disability',
            'options',
            'subrecord',
            [
                ['value' => 'all', 'label' => 'With/Without Disability', 'op' => 'any', 'singular' => false],
                ['value' => 'with', 'label' => 'With Disability', 'op' => 'not_sentinel', 'singular' => false],
                ['value' => 'without', 'label' => 'Without Disability', 'op' => 'is_sentinel', 'singular' => true],
            ],
            ['health_details.disability'],
            $childSort('health', 'disability_id', 'disability', 'disability_id', 'disability'),
            'health.disability_id',
            'disability_id',
            [
                'subrecord_table' => 'health',
                'subrecord_owner' => 'resident_id',
                'sentinel_model' => Disability::class,
                'force_owned' => false,
            ],
        ),
        $lookup(
            'resident_type',
            'Type of Resident',
            'resident_type_id',
            'subrecord',
            ResidentType::class,
            'resident_type',
            'resident_type_id',
            'resident_type',
            ['migration_details.resident_type'],
            $childSort('migration', 'resident_type_id', 'resident_type', 'resident_type_id', 'resident_type'),
            [
                'mode_labels' => $skillTypeModes,
                'plural' => 'Type',
                'force_owned' => false,
                'subrecord_table' => 'migration',
                'subrecord_owner' => 'resident_id',
            ],
        ),
        $lookup(
            'skill_type',
            'Skill Type',
            'skill_type_id',
            'subrecord',
            SkillType::class,
            'skill_type',
            'skill_type_id',
            'skill_type',
            ['skills_details.skill_type'],
            $childSort('skills_development', 'skill_type_id', 'skill_type', 'skill_type_id', 'skill_type'),
            [
                'mode_labels' => $skillTypeModes,
                'plural' => 'Type',
                'force_owned' => false,
                'subrecord_table' => 'skills_development',
                'subrecord_owner' => 'resident_id',
            ],
        ),
    ]),
];

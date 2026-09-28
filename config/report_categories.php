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
use App\Models\ResidentManagement\Demographic\Sex;

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
        'junction_table' => $extra['junction_table'] ?? null,
        'junction_owner' => $extra['junction_owner'] ?? null,
        'junction_id' => $extra['junction_id'] ?? null,
        'constrain_relation' => $extra['constrain_relation'] ?? null,
        'constrain_column' => $extra['constrain_column'] ?? null,
        'constrain_order' => $extra['constrain_order'] ?? null,
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
    ];
};

$category = static function (string $label, array $filters, array $extra = []) use ($standard): array {
    return [
        'level' => 'household',
        'label' => $label,
        'questions' => $extra['questions'] ?? false,
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
            'water_source',
            'water_source_id',
            ['household_questions.main_source_drinking_water'],
            $sortLookup('water_source', 'sort_water_source', 'water_source', 'water_source_id', 'hq.main_source_drinking_water_id'),
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
];

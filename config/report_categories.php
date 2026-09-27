<?php

use App\Models\HouseholdManagement\Clan;

/*
|--------------------------------------------------------------------------
| Report categories
|--------------------------------------------------------------------------
|
| One entry per report. Adding a category is a new key here. Column lists
| live in config/report_columns.php under the category's level, so a new
| household-level or resident-level category does not need another file.
|
| filter_type "lookup" offers All, Per one, and Select 1+ against filter_model.
|
*/

return [
    'clan' => [
        'level' => 'household',
        'label' => 'Clan',
        'filter_type' => 'lookup',
        'filter_model' => Clan::class,
        'filter_column' => 'clan_id',
        'filter_label_column' => 'clan_name',
        'excluded_default_columns' => ['clan'],
    ],
];

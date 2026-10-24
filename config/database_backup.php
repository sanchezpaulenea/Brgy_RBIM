<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tables left out of a backup
    |--------------------------------------------------------------------------
    |
    | These hold sessions, queues, or framework bookkeeping. They are not
    | barangay records, and restoring them would interrupt the signed-in user.
    |
    */

    'excluded_tables' => [
        'migrations',
        'sessions',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
        'password_reset_tokens',
        'personal_access_tokens',
    ],

    /*
    |--------------------------------------------------------------------------
    | Columns omitted from Excel and PDF
    |--------------------------------------------------------------------------
    |
    | The SQL backup still stores these so a restore can bring accounts back.
    | Printed and spreadsheet copies leave them out.
    |
    */

    'redacted_columns' => [
        'password',
        'password_hash',
        'remember_token',
    ],

    /*
    |--------------------------------------------------------------------------
    | PDF length
    |--------------------------------------------------------------------------
    |
    | A paper copy cannot comfortably hold every row of a large table. Excel
    | and SQL include the full table.
    |
    */

    'pdf_row_limit' => 300,

    'max_upload_kilobytes' => 32768,

];

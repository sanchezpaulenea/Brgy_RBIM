<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('user')->update([
            'username' => DB::raw('LOWER(username)'),
        ]);
    }

    public function down(): void
    {
        // Original username casing cannot be restored.
    }
};

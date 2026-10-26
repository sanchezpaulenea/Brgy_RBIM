<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('type_of_certificate')) {
            return;
        }

        DB::table('type_of_certificate')
            ->where('type_of_certificate_id', 4)
            ->where('type_of_certificate', 'Oath of Undertaking')
            ->update([
                'type_of_certificate' => 'Low Income Certification (with amount)',
            ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('type_of_certificate')) {
            return;
        }

        DB::table('type_of_certificate')
            ->where('type_of_certificate_id', 4)
            ->where('type_of_certificate', 'Low Income Certification (with amount)')
            ->update([
                'type_of_certificate' => 'Oath of Undertaking',
            ]);
    }
};

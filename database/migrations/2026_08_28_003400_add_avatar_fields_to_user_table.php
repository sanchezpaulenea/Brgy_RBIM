<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->string('avatar_path', 255)->nullable()->after('must_change_password');
            $table->string('avatar_preset', 10)->nullable()->after('avatar_path');
        });
    }

    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn(['avatar_path', 'avatar_preset']);
        });
    }
};

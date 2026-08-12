<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_setting', function (Blueprint $table) {
            $table->integer('setting_id')->autoIncrement()->primary();
            $table->string('setting_key', 45);
            $table->string('setting_value', 45);
            $table->string('data_type', 45);
            $table->string('description', 255);

            $table->unique('setting_key', 'uq_setting_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_setting');
    }
};

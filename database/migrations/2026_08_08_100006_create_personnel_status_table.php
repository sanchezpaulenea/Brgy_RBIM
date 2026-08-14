<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personnel_status', function (Blueprint $table) {
            $table->integer('personnel_status_id')->autoIncrement()->primary();
            $table->string('personnel_status', 45);

            $table->unique('personnel_status', 'uq_personnel_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personnel_status');
    }
};

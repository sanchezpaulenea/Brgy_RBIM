<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personnel_position', function (Blueprint $table) {
            $table->integer('position_id')->autoIncrement()->primary();
            $table->string('position_name', 45);

            $table->unique('position_name', 'uq_position');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personnel_position');
    }
};

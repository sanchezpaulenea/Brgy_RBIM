<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role', function (Blueprint $table) {
            $table->integer('role_id')->autoIncrement()->primary();
            $table->string('role_name', 45);

            $table->unique('role_name', 'uq_role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role');
    }
};

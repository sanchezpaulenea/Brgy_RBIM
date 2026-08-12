<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission', function (Blueprint $table) {
            $table->integer('permission_id')->autoIncrement()->primary();
            $table->string('permission', 45);

            $table->unique('permission_id', 'uq_permission');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission');
    }
};

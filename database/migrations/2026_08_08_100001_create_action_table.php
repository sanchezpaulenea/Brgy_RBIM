<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('action', function (Blueprint $table) {
            $table->integer('action_id')->autoIncrement()->primary();
            $table->string('action', 45);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('action');
    }
};

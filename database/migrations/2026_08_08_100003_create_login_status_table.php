<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_status', function (Blueprint $table) {
            $table->integer('login_status_id')->autoIncrement()->primary();
            $table->string('login_status', 45);

            $table->unique('login_status', 'uq_login_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_status');
    }
};

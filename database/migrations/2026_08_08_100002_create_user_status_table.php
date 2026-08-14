<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_status', function (Blueprint $table) {
            $table->integer('user_status_id')->autoIncrement()->primary();
            $table->string('user_status', 45);
            $table->boolean('can_login');

            $table->unique('user_status', 'uq_user_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_status');
    }
};

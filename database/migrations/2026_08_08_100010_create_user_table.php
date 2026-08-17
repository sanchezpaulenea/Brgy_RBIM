<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user', function (Blueprint $table) {
            $table->integer('user_id')->autoIncrement()->primary();
            $table->string('username', 45);
            $table->string('password_hash', 255);
            $table->dateTime('created_at')->useCurrent();
            $table->integer('user_status_id')->default(1);
            $table->integer('personnel_id')->nullable();
            $table->boolean('must_change_password')->default(true);

            $table->unique('username', 'uq_username');
            $table->unique('personnel_id', 'uq_personnel');

            $table->foreign('personnel_id', 'brgy_user')
                ->references('personnel_id')
                ->on('barangay_personnel')
                ->restrictOnDelete()
                ->restrictOnUpdate();

            $table->foreign('user_status_id', 'user_status')
                ->references('user_status_id')
                ->on('user_status')
                ->restrictOnDelete()
                ->restrictOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_permission', function (Blueprint $table) {
            $table->integer('role_permission_id')->autoIncrement()->primary();
            $table->integer('role_id');
            $table->integer('permission_id');

            $table->unique(['role_id', 'permission_id'], 'uq_permission_assignment');

            $table->foreign('permission_id', 'permission_role')
                ->references('permission_id')
                ->on('permission')
                ->restrictOnDelete()
                ->restrictOnUpdate();

            $table->foreign('role_id', 'role_permission')
                ->references('role_id')
                ->on('role')
                ->restrictOnDelete()
                ->restrictOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permission');
    }
};

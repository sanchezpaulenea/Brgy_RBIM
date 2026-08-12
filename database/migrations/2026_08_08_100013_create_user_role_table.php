<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_role', function (Blueprint $table) {
            $table->integer('user_role_id')->autoIncrement()->primary();
            $table->integer('user_id');
            $table->integer('role_id');
            $table->dateTime('assigned_at')->useCurrent();
            $table->integer('assigned_by');
            $table->boolean('enable');

            $table->unique(['user_id', 'role_id'], 'uq_role_assignment');
            $table->index('assigned_by', 'role_assignor');

            $table->foreign('assigned_by', 'role_assignor')
                ->references('user_id')
                ->on('user')
                ->restrictOnDelete()
                ->restrictOnUpdate();

            $table->foreign('user_id', 'user_assigned')
                ->references('user_id')
                ->on('user')
                ->restrictOnDelete()
                ->restrictOnUpdate();

            $table->foreign('role_id', 'user_role')
                ->references('role_id')
                ->on('role')
                ->restrictOnDelete()
                ->restrictOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_role');
    }
};

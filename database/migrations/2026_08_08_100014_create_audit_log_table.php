<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_log', function (Blueprint $table) {
            $table->integer('audit_id')->autoIncrement()->primary();
            $table->integer('user_id');
            $table->integer('action_id');
            $table->integer('record_id');
            $table->string('description', 255);
            $table->string('old_value', 45)->nullable();
            $table->string('new_value', 45);
            $table->dateTime('performed_at')->useCurrent();
            $table->string('target', 45);
            $table->string('entity', 45);

            $table->foreign('action_id', 'action')
                ->references('action_id')
                ->on('action')
                ->restrictOnDelete()
                ->restrictOnUpdate();

            $table->foreign('user_id', 'audit_user')
                ->references('user_id')
                ->on('user')
                ->restrictOnDelete()
                ->restrictOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_log');
    }
};

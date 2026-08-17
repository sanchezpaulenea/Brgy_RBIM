<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_log', function (Blueprint $table) {
            $table->integer('user_log_id')->autoIncrement()->primary();
            $table->integer('user_id');
            $table->dateTime('login_time')->useCurrent();
            $table->dateTime('logout_time');
            $table->integer('login_status_id');
            $table->string('ip_address', 45);
            $table->string('device', 45);

            $table->foreign('login_status_id', 'login_status')
                ->references('login_status_id')
                ->on('login_status')
                ->restrictOnDelete()
                ->restrictOnUpdate();

            $table->foreign('user_id', 'user_log')
                ->references('user_id')
                ->on('user')
                ->restrictOnDelete()
                ->restrictOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_log');
    }
};

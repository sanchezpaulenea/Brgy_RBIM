<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_log_archive', function (Blueprint $table) {
            $table->integer('user_log_id')->primary();
            $table->integer('user_id')->nullable();
            $table->dateTime('login_time');
            $table->dateTime('logout_time')->nullable();
            $table->integer('login_status_id');
            $table->string('ip_address', 45);
            $table->string('device', 45);
            $table->dateTime('archived_at')->useCurrent();
            $table->integer('archived_by');

            $table->index('login_time');

            $table->foreign('user_id', 'user_log_archive_user')
                ->references('user_id')
                ->on('user')
                ->restrictOnDelete()
                ->restrictOnUpdate();

            $table->foreign('login_status_id', 'user_log_archive_status')
                ->references('login_status_id')
                ->on('login_status')
                ->restrictOnDelete()
                ->restrictOnUpdate();

            $table->foreign('archived_by', 'user_log_archive_archived_by')
                ->references('user_id')
                ->on('user')
                ->restrictOnDelete()
                ->restrictOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_log_archive');
    }
};

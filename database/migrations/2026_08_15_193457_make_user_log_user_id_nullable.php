<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_log', function (Blueprint $table) {
            $table->dropForeign('user_log');
        });

        Schema::table('user_log', function (Blueprint $table) {
            $table->integer('user_id')->nullable()->change();

            $table->foreign('user_id', 'user_log')
                ->references('user_id')
                ->on('user')
                ->restrictOnDelete()
                ->restrictOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('user_log', function (Blueprint $table) {
            $table->dropForeign('user_log');
        });

        Schema::table('user_log', function (Blueprint $table) {
            $table->integer('user_id')->nullable(false)->change();

            $table->foreign('user_id', 'user_log')
                ->references('user_id')
                ->on('user')
                ->restrictOnDelete()
                ->restrictOnUpdate();
        });
    }
};

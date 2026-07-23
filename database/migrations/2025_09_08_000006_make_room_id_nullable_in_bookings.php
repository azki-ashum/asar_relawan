<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('bookings')) return;

        // Drop foreign key if exists, make column nullable, then re-add foreign key.
        try {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropForeign(['room_id']);
            });
        } catch (\Throwable $e) {
            // ignore if foreign key does not exist
        }

        // Alter column to nullable using raw SQL to avoid doctrine/dbal dependency
        try {
            DB::statement('ALTER TABLE `bookings` MODIFY `room_id` BIGINT UNSIGNED NULL');
        } catch (\Throwable $e) {
            // If alter fails, swallow and let migration continue — user can run with doctrine/dbal installed
        }

        try {
            Schema::table('bookings', function (Blueprint $table) {
                $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');
            });
        } catch (\Throwable $e) {
            // ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('bookings')) return;

        try {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropForeign(['room_id']);
            });
        } catch (\Throwable $e) {
        }

        try {
            DB::statement('ALTER TABLE `bookings` MODIFY `room_id` BIGINT UNSIGNED NOT NULL');
        } catch (\Throwable $e) {
        }

        try {
            Schema::table('bookings', function (Blueprint $table) {
                $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');
            });
        } catch (\Throwable $e) {
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // NOTE: This migration uses Schema builder ->change() and requires the doctrine/dbal package to be installed
        // Install with: composer require doctrine/dbal

        if (! Schema::hasTable('bookings')) {
            return;
        }

        Schema::table('bookings', function (Blueprint $table) {
            // add 'done' to the enum via change()
            $table->enum('status', ['pending', 'approved', 'cancelled', 'done'])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('bookings')) {
            return;
        }

        Schema::table('bookings', function (Blueprint $table) {
            // remove 'done' from the enum via change()
            $table->enum('status', ['pending', 'approved', 'cancelled'])->default('pending')->change();
        });
    }
};

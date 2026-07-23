<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Use Schema enum change (requires doctrine/dbal to change column types)
        Schema::table('bookings', function (Blueprint $table) {
            // Make status enum include 'in_use' and be nullable (default NULL)
            $table->enum('status', ['pending', 'approved', 'in_use', 'cancelled', 'done'])->nullable()->change();
        });
    }

    public function down(): void
    {
        // Revert enum to previous set and keep nullable (default NULL)
        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'cancelled', 'done'])->nullable()->change();
        });
    }
};

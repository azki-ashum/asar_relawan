<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Expand the status enum to include 'revision'
            $table->enum('status', ['pending', 'approved', 'in_use', 'cancelled', 'done', 'revision'])->nullable()->change();

            // Notes from admin explaining why the booking was set to revision
            $table->text('revision_notes')->nullable()->after('status');

            // Track how many times the booking was revised
            $table->unsignedInteger('revision_count')->default(0)->after('revision_notes');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'in_use', 'cancelled', 'done'])->nullable()->change();
            $table->dropColumn(['revision_notes', 'revision_count']);
        });
    }
};

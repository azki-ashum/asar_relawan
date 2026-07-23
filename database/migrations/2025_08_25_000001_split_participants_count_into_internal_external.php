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
        Schema::table('bookings', function (Blueprint $table) {
            $table->integer('participants_internal')->nullable()->after('partner');
            $table->integer('participants_external')->nullable()->after('participants_internal');
        });

        // Migrate existing participants_count into participants_internal (best-effort)
        DB::table('bookings')->whereNotNull('participants_count')->update([
            'participants_internal' => DB::raw('COALESCE(participants_count, 0)'),
            'participants_external' => 0,
        ]);

        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'participants_count')) {
                $table->dropColumn('participants_count');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->integer('participants_count')->nullable()->after('partner');
        });

        // restore combined count
        DB::table('bookings')->update([
            'participants_count' => DB::raw('COALESCE(participants_internal,0) + COALESCE(participants_external,0)')
        ]);

        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'participants_internal')) {
                $table->dropColumn(['participants_internal','participants_external']);
            }
        });
    }
};

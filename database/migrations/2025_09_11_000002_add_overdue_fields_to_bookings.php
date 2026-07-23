<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // track actual usage and overdue state
            $table->timestamp('checked_out_at')->nullable()->after('end_at');
            $table->timestamp('returned_at')->nullable()->after('checked_out_at');
            $table->timestamp('finished_at')->nullable()->after('returned_at');
            $table->boolean('is_overdue')->default(false)->after('finished_at');
            $table->timestamp('overdue_at')->nullable()->after('is_overdue');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['checked_out_at', 'returned_at', 'finished_at', 'is_overdue', 'overdue_at']);
        });
    }
};

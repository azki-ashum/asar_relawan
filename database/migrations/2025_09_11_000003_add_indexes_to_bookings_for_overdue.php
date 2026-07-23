<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['asset_id', 'status', 'returned_at'], 'bookings_asset_status_returned_idx');
            $table->index(['end_at', 'status', 'is_overdue'], 'bookings_end_status_overdue_idx');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_asset_status_returned_idx');
            $table->dropIndex('bookings_end_status_overdue_idx');
        });
    }
};

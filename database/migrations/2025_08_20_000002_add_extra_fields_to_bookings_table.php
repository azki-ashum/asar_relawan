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
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('division')->nullable()->after('user_id');
            $table->string('directorate')->nullable()->after('division');
            $table->string('partner')->nullable()->after('directorate');
            $table->integer('participants_count')->nullable()->after('partner');
            $table->text('facilities')->nullable()->after('participants_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['division', 'directorate', 'partner', 'participants_count', 'facilities']);
        });
    }
};

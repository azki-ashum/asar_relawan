<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            // drop foreign key if exists (be defensive)
            if (Schema::hasColumn('bookings', 'pic_id')) {
                // Some DB drivers require dropping the foreign key constraint first; attempt safely
                try { $table->dropForeign(['pic_id']); } catch (\Throwable $e) { /* ignore */ }
                try { $table->dropColumn('pic_id'); } catch (\Throwable $e) { /* ignore */ }
            }

            // add pic_name as simple varchar to store the PIC display name
            if (!Schema::hasColumn('bookings', 'pic_name')) {
                $table->string('pic_name')->nullable()->after('user_id');
            }
        });
    }

    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'pic_name')) {
                $table->dropColumn('pic_name');
            }

            // restore pic_id as nullable foreign key; cannot know constraint name reliably so use foreignId
            if (!Schema::hasColumn('bookings', 'pic_id')) {
                $table->foreignId('pic_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            }
        });
    }
};

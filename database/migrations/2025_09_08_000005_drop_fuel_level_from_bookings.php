<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('bookings', 'fuel_level_percent')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('fuel_level_percent');
            });
        }
    }

    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedTinyInteger('fuel_level_percent')->nullable()->after('personnel');
        });
    }
};

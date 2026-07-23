<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('assets', 'current_fuel_percent')) {
            Schema::table('assets', function (Blueprint $table) {
                $table->dropColumn('current_fuel_percent');
            });
        }
    }

    public function down()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->integer('current_fuel_percent')->nullable();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->boolean('is_admin_only')->default(false)->after('is_active');
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->boolean('is_admin_only')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('is_admin_only');
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn('is_admin_only');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            DB::statement("ALTER TABLE kebutuhan_relawan MODIFY jenis_relawan VARCHAR(255) NOT NULL");
        } catch (\Throwable $e) {
            Schema::table('kebutuhan_relawan', function (Blueprint $table) {
                $table->string('jenis_relawan')->change();
            });
        }
    }

    public function down(): void
    {
        // No rollback needed
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('relawan', function (Blueprint $table) {
            // Enum lama tidak menampung bidang Rescue / Psikososial / Filantropi
            // yang ada pada database relawan Ksatria.
            $table->string('jenis', 50)->default('lainnya')->change();

            $table->unsignedSmallInteger('tahun_bergabung')->nullable()->after('domisili');
            $table->string('provinsi')->nullable()->after('tahun_bergabung');
            $table->string('kota')->nullable()->after('provinsi');

            $table->index('kontak');
            $table->index('tahun_bergabung');
        });
    }

    public function down(): void
    {
        Schema::table('relawan', function (Blueprint $table) {
            $table->dropIndex(['kontak']);
            $table->dropIndex(['tahun_bergabung']);
            $table->dropColumn(['tahun_bergabung', 'provinsi', 'kota']);
            $table->enum('jenis', ['driver', 'medis', 'implementasi', 'media_dokumentasi', 'canvassing_booth', 'lainnya'])
                ->default('lainnya')->change();
        });
    }
};

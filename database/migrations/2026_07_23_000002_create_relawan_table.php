<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('relawan', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            // Jenis relawan mengikuti Form Pengajuan Relawan Ksatria
            $table->enum('jenis', ['driver', 'medis', 'implementasi', 'media_dokumentasi', 'canvassing_booth', 'lainnya'])
                  ->default('lainnya');
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('kontak')->nullable();        // no. HP / WA
            $table->string('email')->nullable();
            $table->string('domisili')->nullable();
            // Bidang opsional (pengelompokan sekunder / keahlian)
            $table->foreignId('bidang_relawan_id')->nullable()
                  ->constrained('bidang_relawan')->nullOnDelete();
            $table->text('keahlian')->nullable();
            $table->enum('status', ['tersedia', 'ditugaskan', 'nonaktif'])->default('tersedia');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('relawan');
    }
};

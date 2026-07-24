<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // pengaju

            // ---- Header (mengikuti Form Pengajuan Relawan Ksatria) ----
            $table->string('direktorat')->nullable();
            $table->string('divisi')->nullable();
            $table->string('nama_pic')->nullable();       // Nama PIC / Pengaju
            $table->string('judul');                      // Nama Kegiatan
            $table->dateTime('waktu_mulai')->nullable();  // Waktu Mulai Pelaksanaan
            $table->dateTime('waktu_selesai')->nullable();
            $table->string('lokasi')->nullable();         // Lokasi Kegiatan
            $table->text('keterangan')->nullable();
            $table->integer('jumlah_relawan')->default(1); // Jumlah relawan yang diajukan

            // ---- Status mengikuti SOP (Approval → Penugasan → Deployment) ----
            $table->enum('status', ['diajukan', 'revisi', 'disetujui', 'ditugaskan', 'selesai', 'ditolak'])
                  ->default('diajukan');
            $table->text('catatan_revisi')->nullable();   // catatan admin saat verifikasi / minta revisi
            $table->integer('revisi_count')->default(0);

            // ---- Deployment / Evaluasi & Pelaporan (SOP Bagian 3) ----
            $table->json('bukti_implementasi')->nullable();
            $table->text('laporan')->nullable();          // evaluasi & pelaporan oleh pengaju
            $table->timestamp('selesai_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan');
    }
};

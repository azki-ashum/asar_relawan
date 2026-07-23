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
            $table->foreignId('relawan_id')->nullable()
                  ->constrained('relawan')->nullOnDelete();                        // diisi saat assign
            $table->string('judul');
            $table->text('kebutuhan');                                             // deskripsi kebutuhan SDM
            $table->foreignId('bidang_relawan_id')->nullable()
                  ->constrained('bidang_relawan')->nullOnDelete();
            $table->integer('jumlah_relawan')->default(1);
            $table->date('tanggal_kegiatan')->nullable();
            $table->string('lokasi')->nullable();
            $table->enum('status', ['diajukan', 'dicari', 'ditugaskan', 'selesai', 'ditolak', 'revisi'])
                  ->default('diajukan');
            $table->json('bukti_implementasi')->nullable();                        // path foto
            $table->text('catatan_revisi')->nullable();
            $table->integer('revisi_count')->default(0);
            $table->timestamp('selesai_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan');
    }
};

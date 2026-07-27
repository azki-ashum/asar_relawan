<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Baris "Kebutuhan Relawan 1..N" pada satu pengajuan.
    public function up(): void
    {
        Schema::create('kebutuhan_relawan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('pengajuan')->cascadeOnDelete();

            // ---- Diisi pengaju ----
            $table->string('jenis_relawan');
            $table->enum('jenis_kelamin', ['L', 'P', 'LP'])->default('LP'); // LP = laki-laki atau perempuan
            $table->text('detail_tugas')->nullable();
            $table->unsignedBigInteger('nominal_apresiasi')->nullable(); // rupiah

            // ---- Diisi Personal Volunteer Management (admin) ----
            $table->foreignId('relawan_id')->nullable()->constrained('relawan')->nullOnDelete();
            $table->string('relawan_nama')->nullable();      // snapshot / entri manual
            $table->string('relawan_kontak')->nullable();
            $table->string('relawan_domisili')->nullable();
            $table->timestamp('assigned_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kebutuhan_relawan');
    }
};

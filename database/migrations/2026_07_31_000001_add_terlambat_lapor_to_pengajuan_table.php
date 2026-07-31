<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan', function (Blueprint $table) {
            // Ditandai oleh command pengajuan:mark-terlambat saat waktu_selesai
            // terlewat tapi laporan belum masuk (status masih "ditugaskan").
            $table->timestamp('terlambat_at')->nullable()->after('selesai_at');
            // Kapan pengingat terakhir dikirim, supaya email tidak dobel.
            $table->timestamp('terlambat_notified_at')->nullable()->after('terlambat_at');

            $table->index(['status', 'waktu_selesai'], 'pengajuan_status_waktu_selesai_index');
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan', function (Blueprint $table) {
            $table->dropIndex('pengajuan_status_waktu_selesai_index');
            $table->dropColumn(['terlambat_at', 'terlambat_notified_at']);
        });
    }
};

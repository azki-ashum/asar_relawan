<?php

namespace App\Console\Commands;

use App\Models\Pengajuan;
use App\Services\PengajuanNotifier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * SOP Bagian 3: menandai pengajuan yang sudah melewati waktu selesai tetapi
 * bukti & laporannya belum masuk (status masih "ditugaskan"), lalu mengirim
 * pengingat ke pengaju (admin di-cc).
 */
class MarkTerlambatPengajuan extends Command
{
    protected $signature = 'pengajuan:mark-terlambat {--dry-run : Tampilkan kandidat tanpa menandai / mengirim email}';

    protected $description = 'Tandai pengajuan yang lewat waktu selesai tapi laporannya belum masuk, lalu kirim pengingat';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $grace = Pengajuan::graceMenit();
        $this->info('Toleransi keterlambatan: ' . $grace . ' menit' . ($dry ? ' (dry-run)' : ''));

        $ditandai = $this->tandaiYangBaruTerlambat($dry);
        $diingatkan = $this->kirimPengingatUlang($dry);
        $dibersihkan = $this->bersihkanPenandaKedaluwarsa($dry);

        $this->info("Ditandai terlambat: {$ditandai} · Pengingat ulang: {$diingatkan} · Penanda dibersihkan: {$dibersihkan}");

        return self::SUCCESS;
    }

    /** Baru pertama kali lewat batas -> tandai + kirim pengingat. */
    protected function tandaiYangBaruTerlambat(bool $dry): int
    {
        $query = Pengajuan::terlambatLapor()->whereNull('terlambat_at');
        $total = 0;

        $query->with('user')->chunkById(100, function ($rows) use ($dry, &$total) {
            foreach ($rows as $p) {
                $total++;
                $this->line("  · #{$p->id} {$p->judul} — terlambat {$p->terlambatSelama()}");

                if ($dry) {
                    continue;
                }

                try {
                    $p->terlambat_at = now();
                    $p->terlambat_notified_at = now();
                    $p->saveQuietly();
                    PengajuanNotifier::terlambatLapor($p);
                    Log::info('Pengajuan ditandai terlambat lapor', ['pengajuan_id' => $p->id]);
                } catch (\Throwable $e) {
                    Log::warning('Gagal menandai pengajuan terlambat', ['pengajuan_id' => $p->id, 'error' => $e->getMessage()]);
                }
            }
        });

        return $total;
    }

    /** Masih terlambat setelah sekian jam -> ingatkan lagi (bila diaktifkan di config). */
    protected function kirimPengingatUlang(bool $dry): int
    {
        $interval = max(0, (int) config('pengajuan.terlambat.reminder_every_hours', 0));
        if ($interval === 0) {
            return 0;
        }

        $query = Pengajuan::terlambatLapor()
            ->whereNotNull('terlambat_at')
            ->where(function ($q) use ($interval) {
                $q->whereNull('terlambat_notified_at')
                  ->orWhere('terlambat_notified_at', '<=', now()->subHours($interval));
            });
        $total = 0;

        $query->with('user')->chunkById(100, function ($rows) use ($dry, &$total) {
            foreach ($rows as $p) {
                $total++;
                if ($dry) {
                    $this->line("  · pengingat ulang #{$p->id} {$p->judul}");
                    continue;
                }

                try {
                    $p->terlambat_notified_at = now();
                    $p->saveQuietly();
                    PengajuanNotifier::terlambatLapor($p);
                } catch (\Throwable $e) {
                    Log::warning('Gagal kirim pengingat ulang', ['pengajuan_id' => $p->id, 'error' => $e->getMessage()]);
                }
            }
        });

        return $total;
    }

    /**
     * Jadwal kegiatan digeser ke depan setelah sempat ditandai -> penanda dicabut
     * supaya statusnya kembali sesuai dan bisa diingatkan lagi bila lewat lagi.
     * Pengajuan yang sudah selesai tidak disentuh (terlambat_at = riwayat).
     */
    protected function bersihkanPenandaKedaluwarsa(bool $dry): int
    {
        $query = Pengajuan::where('status', 'ditugaskan')
            ->whereNotNull('terlambat_at')
            ->where(function ($q) {
                $q->whereNull('waktu_selesai')
                  ->orWhere('waktu_selesai', '>', now()->subMinutes(Pengajuan::graceMenit()));
            });

        if ($dry) {
            return $query->count();
        }

        return $query->update(['terlambat_at' => null, 'terlambat_notified_at' => null]);
    }
}

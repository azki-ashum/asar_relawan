<?php

namespace App\Services;

use App\Mail\PengajuanBaruMail;
use App\Mail\PengajuanDisetujuiMail;
use App\Mail\PengajuanDitolakMail;
use App\Mail\PengajuanDitugaskanMail;
use App\Mail\PengajuanRevisiMail;
use App\Models\Pengajuan;
use App\Models\User;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Satu pintu untuk seluruh notifikasi email seputar pengajuan relawan.
 *
 * Semua pengiriman memakai queue (lihat ShouldQueue pada tiap Mailable), jadi
 * `php artisan queue:work` wajib jalan. Kegagalan kirim tidak pernah membatalkan
 * aksi admin/pengaju — cukup dicatat ke log.
 */
class PengajuanNotifier
{
    /** Pengajuan baru masuk -> seluruh admin (SOP Bagian 1: Review & Verifikasi). */
    public static function pengajuanBaru(Pengajuan $pengajuan): void
    {
        self::send(self::adminEmails(), new PengajuanBaruMail($pengajuan), 'pengajuan baru', $pengajuan);
    }

    /** Admin menyetujui -> pengaju. */
    public static function disetujui(Pengajuan $pengajuan): void
    {
        self::send(self::pengajuEmail($pengajuan), new PengajuanDisetujuiMail($pengajuan), 'pengajuan disetujui', $pengajuan);
    }

    /** Admin menolak -> pengaju. */
    public static function ditolak(Pengajuan $pengajuan): void
    {
        self::send(self::pengajuEmail($pengajuan), new PengajuanDitolakMail($pengajuan), 'pengajuan ditolak', $pengajuan);
    }

    /**
     * Admin minta perbaikan -> pengaju.
     *
     * @param 'pengajuan'|'laporan' $tahap
     */
    public static function revisi(Pengajuan $pengajuan, string $tahap = 'pengajuan'): void
    {
        self::send(self::pengajuEmail($pengajuan), new PengajuanRevisiMail($pengajuan, $tahap), "permintaan revisi $tahap", $pengajuan);
    }

    /** Seluruh kebutuhan sudah terisi relawan -> pengaju (SOP Bagian 2 -> 3). */
    public static function ditugaskan(Pengajuan $pengajuan): void
    {
        self::send(self::pengajuEmail($pengajuan), new PengajuanDitugaskanMail($pengajuan), 'relawan ditugaskan', $pengajuan);
    }

    /** @return array<int, string> */
    protected static function adminEmails(): array
    {
        return User::where('role', 'like', 'admin%')->whereNotNull('email')
            ->pluck('email')->filter()->unique()->values()->all();
    }

    /** @return array<int, string> */
    protected static function pengajuEmail(Pengajuan $pengajuan): array
    {
        $email = $pengajuan->loadMissing('user')->user->email ?? null;

        return $email ? [$email] : [];
    }

    /** @param array<int, string> $to */
    protected static function send(array $to, Mailable $mailable, string $konteks, Pengajuan $pengajuan): void
    {
        if (empty($to)) {
            Log::warning("Notifikasi email dilewati ($konteks): tidak ada penerima", ['pengajuan_id' => $pengajuan->id]);
            return;
        }

        try {
            Mail::to($to)->queue($mailable);
        } catch (\Throwable $e) {
            Log::warning("Gagal kirim email $konteks", ['pengajuan_id' => $pengajuan->id, 'error' => $e->getMessage()]);
        }
    }
}

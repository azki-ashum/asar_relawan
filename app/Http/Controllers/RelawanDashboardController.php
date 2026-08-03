<?php

namespace App\Http\Controllers;

use App\Models\Pengajuan;
use App\Support\JadwalPengajuan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RelawanDashboardController extends Controller
{
    // Beranda (area Pengaju): ringkasan organisasi (semua pengajuan) + notifikasi
    // aksi yang khusus milik akun yang login (hanya pemilik yang bisa bertindak).
    public function index(Request $request)
    {
        $userId = Auth::id();

        $counts = [
            'total'      => Pengajuan::count(),
            'menunggu'   => Pengajuan::whereIn('status', ['diajukan', 'disetujui'])->count(),
            'ditugaskan' => Pengajuan::where('status', 'ditugaskan')->count(),
            'selesai'    => Pengajuan::where('status', 'selesai')->count(),
            'ditolak'    => Pengajuan::where('status', 'ditolak')->count(),
        ];

        // "Butuh aksi" khusus pengajuan milik akun ini, karena hanya pemilik
        // yang bisa memperbaiki revisi / mengunggah laporan.
        $needAction = Pengajuan::withCount('kebutuhan')
            ->where('user_id', $userId)
            ->whereIn('status', ['revisi', 'ditugaskan'])
            ->orderByDesc('updated_at')
            ->get();

        // Seluruh pengajuan berjadwal (semua pengaju): sumber tunggal untuk kalender
        // (tanda titik + modal per tanggal) dan daftar "Pengajuan Minggu Ini".
        $withSchedule = JadwalPengajuan::items();
        $calendarDates = JadwalPengajuan::tanggalBertanda($withSchedule);

        // Pengajuan Minggu Ini: 7 hari mulai hari ini, dikelompokkan per hari.
        $weekStart = Carbon::today()->startOfDay();
        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $weekStart->copy()->addDays($i);
            $dayStart = $day->copy()->startOfDay();
            $dayEnd = $day->copy()->endOfDay();

            $items = $withSchedule->filter(function ($item) use ($dayStart, $dayEnd) {
                $start = Carbon::parse($item['waktu_mulai']);
                $end = $item['waktu_selesai'] ? Carbon::parse($item['waktu_selesai']) : $start;

                return $start->lte($dayEnd) && $end->gte($dayStart);
            })->values();

            $weekDays[$day->format('Y-m-d')] = [
                'label' => $day->translatedFormat('l, d F Y'),
                'items' => $items,
            ];
        }

        return view('relawan.dashboard', compact('counts', 'needAction', 'weekDays', 'withSchedule', 'calendarDates'));
    }
}

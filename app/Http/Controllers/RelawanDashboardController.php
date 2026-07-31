<?php

namespace App\Http\Controllers;

use App\Models\Pengajuan;
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
        $withSchedule = Pengajuan::with('user')->withCount('kebutuhan')
            ->whereNotIn('status', ['ditolak'])
            ->whereNotNull('waktu_mulai')
            ->orderBy('waktu_mulai')
            ->get()
            ->map(function (Pengajuan $p) {
                $meta = $p->statusMeta();

                return [
                    'id'            => $p->id,
                    'judul'         => $p->judul,
                    'pengaju'       => $p->nama_pic ?? ($p->user->name ?? null),
                    'divisi'        => $p->divisi,
                    'direktorat'    => $p->direktorat,
                    'lokasi'        => $p->lokasi,
                    'kebutuhan'     => $p->kebutuhan_count,
                    'waktu_mulai'   => $p->waktu_mulai->toIso8601String(),
                    'waktu_selesai' => optional($p->waktu_selesai)->toIso8601String(),
                    'status'        => $p->status,
                    'status_label'  => $meta['label'],
                    'status_class'  => $meta['class'],
                    'status_icon'   => $meta['icon'],
                    'url'           => route('pengajuan.show', $p),
                ];
            });

        // Tanggal yang ditandai titik di kalender: setiap hari yang dilingkupi
        // rentang waktu_mulai..waktu_selesai suatu pengajuan.
        $calendarDates = [];
        foreach ($withSchedule as $item) {
            $day = Carbon::parse($item['waktu_mulai'])->startOfDay();
            $end = $item['waktu_selesai'] ? Carbon::parse($item['waktu_selesai'])->startOfDay() : $day->copy();
            while ($day->lte($end)) {
                $calendarDates[$day->format('Y-m-d')] = true;
                $day->addDay();
            }
        }

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

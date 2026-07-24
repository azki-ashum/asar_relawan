<?php

namespace App\Http\Controllers;

use App\Models\Pengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RelawanDashboardController extends Controller
{
    // Beranda: ringkasan status seluruh pengajuan organisasi + notifikasi aksi milik pengaju sendiri.
    public function index(Request $request)
    {
        $userId = Auth::id();

        // Ringkasan & daftar terbaru mencakup pengajuan dari semua pengaju.
        $counts = [
            'total'      => Pengajuan::count(),
            'menunggu'   => Pengajuan::whereIn('status', ['diajukan', 'disetujui'])->count(),
            'ditugaskan' => Pengajuan::where('status', 'ditugaskan')->count(),
            'selesai'    => Pengajuan::where('status', 'selesai')->count(),
        ];

        // "Butuh aksi" tetap khusus pengajuan milik akun ini, karena hanya pemilik
        // yang bisa memperbaiki revisi / mengunggah laporan.
        $needAction = Pengajuan::withCount('kebutuhan')
            ->where('user_id', $userId)
            ->whereIn('status', ['revisi', 'ditugaskan'])
            ->orderByDesc('updated_at')
            ->get();

        $recent = Pengajuan::with('user')->withCount('kebutuhan')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('relawan.dashboard', compact('counts', 'needAction', 'recent'));
    }
}

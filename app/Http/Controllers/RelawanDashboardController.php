<?php

namespace App\Http\Controllers;

use App\Models\Pengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RelawanDashboardController extends Controller
{
    // Beranda (area Pengaju): ringkasan & daftar terbaru khusus pengajuan milik akun yang login.
    public function index(Request $request)
    {
        $userId = Auth::id();

        $counts = [
            'total'      => Pengajuan::where('user_id', $userId)->count(),
            'menunggu'   => Pengajuan::where('user_id', $userId)->whereIn('status', ['diajukan', 'disetujui'])->count(),
            'ditugaskan' => Pengajuan::where('user_id', $userId)->where('status', 'ditugaskan')->count(),
            'selesai'    => Pengajuan::where('user_id', $userId)->where('status', 'selesai')->count(),
        ];

        // "Butuh aksi" khusus pengajuan milik akun ini, karena hanya pemilik
        // yang bisa memperbaiki revisi / mengunggah laporan.
        $needAction = Pengajuan::withCount('kebutuhan')
            ->where('user_id', $userId)
            ->whereIn('status', ['revisi', 'ditugaskan'])
            ->orderByDesc('updated_at')
            ->get();

        $recent = Pengajuan::with('user')->withCount('kebutuhan')
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('relawan.dashboard', compact('counts', 'needAction', 'recent'));
    }
}

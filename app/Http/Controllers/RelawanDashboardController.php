<?php

namespace App\Http\Controllers;

use App\Models\Pengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RelawanDashboardController extends Controller
{
    // Beranda pengaju: ringkasan status pengajuan + notifikasi penugasan.
    public function index(Request $request)
    {
        $userId = Auth::id();

        $counts = [
            'total'      => Pengajuan::where('user_id', $userId)->count(),
            'diajukan'   => Pengajuan::where('user_id', $userId)->whereIn('status', ['diajukan', 'dicari'])->count(),
            'ditugaskan' => Pengajuan::where('user_id', $userId)->where('status', 'ditugaskan')->count(),
            'selesai'    => Pengajuan::where('user_id', $userId)->where('status', 'selesai')->count(),
        ];

        // Notifikasi: pengajuan yang butuh aksi pengaju (upload bukti / revisi)
        $needAction = Pengajuan::with('relawan')
            ->where('user_id', $userId)
            ->whereIn('status', ['ditugaskan', 'revisi'])
            ->orderByDesc('updated_at')
            ->get();

        $recent = Pengajuan::with(['relawan', 'bidang'])
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('relawan.dashboard', compact('counts', 'needAction', 'recent'));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengajuan;
use App\Models\Relawan;
use App\Models\BidangRelawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PengajuanController extends Controller
{
    protected function authorizeAdmin(): void
    {
        $role = optional(auth()->user())->role ?? '';
        if (!str_starts_with($role, 'admin')) {
            abort(403, 'Unauthorized');
        }
    }

    // Daftar semua pengajuan untuk admin
    public function index(Request $request)
    {
        $this->authorizeAdmin();

        $query = Pengajuan::with(['user', 'relawan', 'bidang'])->orderByDesc('created_at');

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%$search%")
                  ->orWhere('kebutuhan', 'like', "%$search%")
                  ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%$search%"));
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $pengajuan = $query->paginate(15)->withQueryString();
        $bidangs = BidangRelawan::orderBy('nama')->get();

        return view('admin.pengajuan.index', compact('pengajuan', 'bidangs'));
    }

    // Detail admin (lihat bukti, minta revisi, tolak)
    public function show(Pengajuan $pengajuan)
    {
        $this->authorizeAdmin();
        $pengajuan->load(['user', 'relawan.bidang', 'bidang']);
        return view('admin.pengajuan.show', compact('pengajuan'));
    }

    // Layar "Cari & Assign Relawan"
    public function assignForm(Request $request, Pengajuan $pengajuan)
    {
        $this->authorizeAdmin();

        if (in_array($pengajuan->status, ['selesai', 'ditolak'])) {
            return redirect()->route('admin.pengajuan.show', $pengajuan)
                ->with('error', 'Pengajuan ini sudah ' . $pengajuan->status . ', tidak bisa ditugaskan.');
        }

        // Saat admin mulai mencari, tandai status menjadi "dicari"
        if ($pengajuan->status === 'diajukan') {
            $pengajuan->update(['status' => 'dicari']);
        }

        // Daftar relawan yang tersedia, difilter sesuai kebutuhan pengajuan
        $query = Relawan::with('bidang')->where('status', 'tersedia');

        // Default: sarankan relawan sesuai bidang pengajuan (kecuali admin memilih 'semua')
        $filterBidang = $request->get('bidang_relawan_id', $pengajuan->bidang_relawan_id);
        if ($filterBidang) {
            $query->where('bidang_relawan_id', $filterBidang);
        }

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%$search%")
                  ->orWhere('keahlian', 'like', "%$search%")
                  ->orWhere('domisili', 'like', "%$search%");
            });
        }

        $relawanTersedia = $query->orderBy('nama')->paginate(10)->withQueryString();
        $bidangs = BidangRelawan::orderBy('nama')->get();
        $pengajuan->load(['user', 'relawan', 'bidang']);

        return view('admin.pengajuan.assign', compact('pengajuan', 'relawanTersedia', 'bidangs', 'filterBidang'));
    }

    // Assign relawan ke pengajuan (INTI SISTEM)
    public function assign(Request $request, Pengajuan $pengajuan)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'relawan_id' => 'required|exists:relawan,id',
        ]);

        // Bebaskan relawan sebelumnya jika sedang di-reassign
        if ($pengajuan->relawan_id && $pengajuan->relawan_id != $data['relawan_id']) {
            Relawan::where('id', $pengajuan->relawan_id)->update(['status' => 'tersedia']);
        }

        $pengajuan->update([
            'relawan_id' => $data['relawan_id'],
            'status'     => 'ditugaskan',
        ]);
        Relawan::where('id', $data['relawan_id'])->update(['status' => 'ditugaskan']);

        Log::info('Relawan ditugaskan', ['pengajuan_id' => $pengajuan->id, 'relawan_id' => $data['relawan_id'], 'admin_id' => Auth::id()]);

        return redirect()->route('admin.pengajuan.show', $pengajuan)
            ->with('success', 'Relawan berhasil ditugaskan. Pengaju akan diberi tahu di dashboard-nya.');
    }

    // Admin meminta revisi bukti implementasi (selesai -> revisi)
    public function revisi(Request $request, Pengajuan $pengajuan)
    {
        $this->authorizeAdmin();

        if ($pengajuan->status !== 'selesai') {
            return back()->with('error', 'Hanya pengajuan berstatus "Selesai" yang bisa diminta revisi.');
        }

        $data = $request->validate([
            'catatan_revisi' => 'nullable|string|max:1000',
        ]);

        $pengajuan->update([
            'status'         => 'revisi',
            'catatan_revisi' => $data['catatan_revisi'] ?? null,
            'revisi_count'   => ($pengajuan->revisi_count ?? 0) + 1,
        ]);

        // Relawan kembali dianggap bertugas sampai bukti perbaikan diunggah
        if ($pengajuan->relawan) {
            $pengajuan->relawan->update(['status' => 'ditugaskan']);
        }

        Log::info('Pengajuan diminta revisi', ['pengajuan_id' => $pengajuan->id, 'admin_id' => Auth::id()]);
        return redirect()->route('admin.pengajuan.show', $pengajuan)
            ->with('success', 'Pengajuan dikembalikan untuk revisi. Pengaju harus mengunggah ulang bukti.');
    }

    // Admin membatalkan permintaan revisi (revisi -> selesai, bukti lama tetap)
    public function cancelRevision(Pengajuan $pengajuan)
    {
        $this->authorizeAdmin();

        if ($pengajuan->status !== 'revisi') {
            return back()->with('error', 'Pengajuan ini tidak dalam status revisi.');
        }

        $pengajuan->update([
            'status'         => 'selesai',
            'catatan_revisi' => null,
            'revisi_count'   => max(0, ($pengajuan->revisi_count ?? 1) - 1),
            'selesai_at'     => $pengajuan->selesai_at ?? now(),
        ]);

        if ($pengajuan->relawan) {
            $pengajuan->relawan->update(['status' => 'tersedia']);
        }

        return redirect()->route('admin.pengajuan.show', $pengajuan)
            ->with('success', 'Permintaan revisi dibatalkan. Pengajuan kembali berstatus selesai.');
    }

    // Admin menolak pengajuan (sebelum selesai)
    public function reject(Request $request, Pengajuan $pengajuan)
    {
        $this->authorizeAdmin();

        if (in_array($pengajuan->status, ['selesai'])) {
            return back()->with('error', 'Pengajuan yang sudah selesai tidak bisa ditolak.');
        }

        // Bebaskan relawan bila sebelumnya ditugaskan
        if ($pengajuan->relawan) {
            $pengajuan->relawan->update(['status' => 'tersedia']);
        }

        $pengajuan->update([
            'status'     => 'ditolak',
            'relawan_id' => null,
        ]);

        Log::info('Pengajuan ditolak', ['pengajuan_id' => $pengajuan->id, 'admin_id' => Auth::id()]);
        return redirect()->route('admin.pengajuan.index')->with('success', 'Pengajuan ditolak.');
    }
}

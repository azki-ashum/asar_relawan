<?php

namespace App\Http\Controllers;

use App\Models\Pengajuan;
use App\Models\BidangRelawan;
use App\Models\User;
use App\Mail\PengajuanBaruMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PengajuanController extends Controller
{
    /** Pastikan pengaju hanya mengakses pengajuan miliknya. */
    protected function authorizeOwner(Pengajuan $pengajuan): void
    {
        if ($pengajuan->user_id !== Auth::id()) {
            abort(403, 'Anda tidak berhak mengakses pengajuan ini.');
        }
    }

    // Daftar pengajuan milik pengaju saat ini
    public function index(Request $request)
    {
        $query = Pengajuan::with(['relawan', 'bidang'])
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at');

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%$search%")
                  ->orWhere('kebutuhan', 'like', "%$search%")
                  ->orWhere('lokasi', 'like', "%$search%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $pengajuan = $query->paginate(10)->withQueryString();

        return view('pengajuan.index', compact('pengajuan'));
    }

    public function create()
    {
        $bidangs = BidangRelawan::orderBy('nama')->get();
        return view('pengajuan.create', compact('bidangs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'judul'             => 'required|string|max:255',
            'kebutuhan'         => 'required|string',
            'bidang_relawan_id' => 'nullable|exists:bidang_relawan,id',
            'jumlah_relawan'    => 'required|integer|min:1|max:1000',
            'tanggal_kegiatan'  => 'nullable|date',
            'lokasi'            => 'nullable|string|max:255',
        ]);

        $data['user_id'] = Auth::id();
        $data['status']  = 'diajukan';

        $pengajuan = Pengajuan::create($data);

        // Notifikasi email ke admin (Fase 5). Dibungkus try/catch agar kegagalan
        // pengiriman email tidak menggagalkan pembuatan pengajuan.
        try {
            $adminEmails = User::where('role', 'like', 'admin%')
                ->whereNotNull('email')
                ->pluck('email')
                ->filter()
                ->unique()
                ->values();

            if ($adminEmails->isNotEmpty()) {
                Mail::to($adminEmails->all())->queue(new PengajuanBaruMail($pengajuan));
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal mengirim email pengajuan baru', ['pengajuan_id' => $pengajuan->id, 'error' => $e->getMessage()]);
        }

        Log::info('Pengajuan dibuat', ['pengajuan_id' => $pengajuan->id, 'user_id' => Auth::id()]);
        return redirect()->route('pengajuan.show', $pengajuan)->with('success', 'Pengajuan berhasil dibuat. Admin akan mencarikan relawan.');
    }

    public function show(Pengajuan $pengajuan)
    {
        $this->authorizeOwner($pengajuan);
        $pengajuan->load(['relawan.bidang', 'bidang', 'user']);
        return view('pengajuan.show', compact('pengajuan'));
    }

    public function edit(Pengajuan $pengajuan)
    {
        $this->authorizeOwner($pengajuan);

        if (!in_array($pengajuan->status, ['diajukan', 'dicari'])) {
            return redirect()->route('pengajuan.show', $pengajuan)
                ->with('error', 'Pengajuan hanya bisa diedit sebelum relawan ditugaskan.');
        }

        $bidangs = BidangRelawan::orderBy('nama')->get();
        return view('pengajuan.edit', compact('pengajuan', 'bidangs'));
    }

    public function update(Request $request, Pengajuan $pengajuan)
    {
        $this->authorizeOwner($pengajuan);

        if (!in_array($pengajuan->status, ['diajukan', 'dicari'])) {
            return redirect()->route('pengajuan.show', $pengajuan)
                ->with('error', 'Pengajuan hanya bisa diedit sebelum relawan ditugaskan.');
        }

        $data = $request->validate([
            'judul'             => 'required|string|max:255',
            'kebutuhan'         => 'required|string',
            'bidang_relawan_id' => 'nullable|exists:bidang_relawan,id',
            'jumlah_relawan'    => 'required|integer|min:1|max:1000',
            'tanggal_kegiatan'  => 'nullable|date',
            'lokasi'            => 'nullable|string|max:255',
        ]);

        $pengajuan->update($data);

        return redirect()->route('pengajuan.show', $pengajuan)->with('success', 'Pengajuan diperbarui.');
    }

    // Pengaju membatalkan/menghapus pengajuan (hanya sebelum ditugaskan)
    public function destroy(Pengajuan $pengajuan)
    {
        $this->authorizeOwner($pengajuan);

        if (!in_array($pengajuan->status, ['diajukan', 'dicari'])) {
            return back()->with('error', 'Pengajuan yang sudah diproses tidak dapat dibatalkan.');
        }

        $pengajuan->delete();
        return redirect()->route('pengajuan.index')->with('success', 'Pengajuan dibatalkan.');
    }

    // ----- Fase 4: Bukti implementasi -----

    // Pengaju menutup pengajuan dengan foto bukti implementasi
    public function selesai(Request $request, Pengajuan $pengajuan)
    {
        $this->authorizeOwner($pengajuan);

        if ($pengajuan->status !== 'ditugaskan') {
            return back()->with('error', 'Bukti hanya bisa diunggah saat pengajuan berstatus "Ditugaskan".');
        }

        $request->validate([
            'bukti_file' => 'required|image|max:5120', // <= 5MB
        ]);

        $f = $request->file('bukti_file');
        $path = $f->store('bukti_implementasi', 'public');

        $pengajuan->update([
            'bukti_implementasi' => [
                'path'        => $path,
                'original'    => $f->getClientOriginalName(),
                'uploaded_by' => Auth::id(),
                'uploaded_at' => now()->toDateTimeString(),
            ],
            'status'     => 'selesai',
            'selesai_at' => now(),
        ]);

        // Bebaskan relawan agar bisa ditugaskan lagi
        if ($pengajuan->relawan) {
            $pengajuan->relawan->update(['status' => 'tersedia']);
        }

        Log::info('Pengajuan diselesaikan', ['pengajuan_id' => $pengajuan->id, 'user_id' => Auth::id()]);
        return redirect()->route('pengajuan.show', $pengajuan)->with('success', 'Bukti terunggah. Pengajuan selesai. Terima kasih!');
    }

    // Pengaju mengunggah ulang bukti setelah admin meminta revisi
    public function resubmit(Request $request, Pengajuan $pengajuan)
    {
        $this->authorizeOwner($pengajuan);

        if ($pengajuan->status !== 'revisi') {
            return back()->with('error', 'Pengajuan ini tidak dalam status revisi.');
        }

        $request->validate([
            'bukti_file' => 'required|image|max:5120',
        ]);

        $f = $request->file('bukti_file');
        $path = $f->store('bukti_implementasi', 'public');

        $pengajuan->update([
            'bukti_implementasi' => [
                'path'        => $path,
                'original'    => $f->getClientOriginalName(),
                'uploaded_by' => Auth::id(),
                'uploaded_at' => now()->toDateTimeString(),
            ],
            'status'         => 'selesai',
            'selesai_at'     => now(),
            'catatan_revisi' => null,
        ]);

        if ($pengajuan->relawan) {
            $pengajuan->relawan->update(['status' => 'tersedia']);
        }

        Log::info('Pengajuan resubmit setelah revisi', ['pengajuan_id' => $pengajuan->id, 'user_id' => Auth::id()]);
        return redirect()->route('pengajuan.show', $pengajuan)->with('success', 'Bukti berhasil diunggah ulang. Pengajuan ditandai selesai.');
    }
}

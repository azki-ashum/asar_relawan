<?php

namespace App\Http\Controllers;

use App\Models\Pengajuan;
use App\Models\KebutuhanRelawan;
use App\Models\User;
use App\Mail\PengajuanBaruMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class PengajuanController extends Controller
{
    protected function authorizeOwner(Pengajuan $pengajuan): void
    {
        if ($pengajuan->user_id !== Auth::id()) {
            abort(403, 'Anda tidak berhak mengakses pengajuan ini.');
        }
    }

    /** Aturan validasi header + baris kebutuhan (dipakai store & update). */
    protected function rules(): array
    {
        return [
            'direktorat'                    => 'nullable|string|max:255',
            'divisi'                        => 'nullable|string|max:255',
            'nama_pic'                      => 'nullable|string|max:255',
            'judul'                         => 'required|string|max:255',
            'waktu_mulai'                   => 'nullable|date',
            'waktu_selesai'                 => 'nullable|date|after_or_equal:waktu_mulai',
            'lokasi'                        => 'nullable|string|max:255',
            'keterangan'                    => 'nullable|string',
            'kebutuhan'                     => 'required|array|min:1',
            'kebutuhan.*.jenis_relawan'     => 'required|string|max:255',
            'kebutuhan.*.jenis_kelamin'     => ['required', Rule::in(array_keys(KebutuhanRelawan::JENIS_KELAMIN))],
            'kebutuhan.*.detail_tugas'      => 'nullable|string',
            'kebutuhan.*.nominal_apresiasi' => 'nullable|integer|min:0|max:1000000000',
        ];
    }

    protected function messages(): array
    {
        return [
            'kebutuhan.required' => 'Minimal satu baris kebutuhan relawan harus diisi.',
            'kebutuhan.*.jenis_relawan.required' => 'Jenis relawan wajib dipilih.',
            'kebutuhan.*.jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
        ];
    }

    // Daftar seluruh pengajuan organisasi (bukan hanya milik pengaju saat ini)
    public function index(Request $request)
    {
        $query = Pengajuan::with('user')->withCount('kebutuhan')
            ->orderByDesc('created_at');

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%$search%")
                  ->orWhere('lokasi', 'like', "%$search%")
                  ->orWhere('divisi', 'like', "%$search%")
                  ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%$search%"));
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
        return view('pengajuan.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules(), $this->messages());

        $pengajuan = DB::transaction(function () use ($data) {
            $pengajuan = Pengajuan::create([
                'user_id'        => Auth::id(),
                'direktorat'     => $data['direktorat'] ?? null,
                'divisi'         => $data['divisi'] ?? null,
                'nama_pic'       => $data['nama_pic'] ?? Auth::user()->name,
                'judul'          => $data['judul'],
                'waktu_mulai'    => $data['waktu_mulai'] ?? null,
                'waktu_selesai'  => $data['waktu_selesai'] ?? null,
                'lokasi'         => $data['lokasi'] ?? null,
                'keterangan'     => $data['keterangan'] ?? null,
                'jumlah_relawan' => count($data['kebutuhan']),
                'status'         => 'diajukan',
            ]);
            $this->syncKebutuhan($pengajuan, $data['kebutuhan']);
            return $pengajuan;
        });

        // Notifikasi email ke admin (SOP Bagian 1: Review & Verifikasi)
        try {
            $adminEmails = User::where('role', 'like', 'admin%')->whereNotNull('email')
                ->pluck('email')->filter()->unique()->values();
            if ($adminEmails->isNotEmpty()) {
                Mail::to($adminEmails->all())->queue(new PengajuanBaruMail($pengajuan));
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal kirim email pengajuan baru', ['id' => $pengajuan->id, 'error' => $e->getMessage()]);
        }

        return redirect()->route('pengajuan.show', $pengajuan)
            ->with('success', 'Pengajuan terkirim. Menunggu verifikasi Tim Ksatria.');
    }

    // Detail pengajuan bisa dilihat siapa saja yang login (read-only untuk yang bukan pemilik).
    // Aksi ubah/batalkan/selesaikan tetap dibatasi hanya untuk pemilik, lihat authorizeOwner().
    public function show(Pengajuan $pengajuan)
    {
        $pengajuan->load(['kebutuhan.relawan', 'user']);
        return view('pengajuan.show', compact('pengajuan'));
    }

    public function edit(Pengajuan $pengajuan)
    {
        $this->authorizeOwner($pengajuan);
        if (!in_array($pengajuan->status, ['diajukan', 'revisi'])) {
            return redirect()->route('pengajuan.show', $pengajuan)
                ->with('error', 'Pengajuan hanya bisa diedit selagi menunggu verifikasi atau saat diminta revisi.');
        }
        $pengajuan->load('kebutuhan');
        return view('pengajuan.edit', compact('pengajuan'));
    }

    public function update(Request $request, Pengajuan $pengajuan)
    {
        $this->authorizeOwner($pengajuan);
        if (!in_array($pengajuan->status, ['diajukan', 'revisi'])) {
            return redirect()->route('pengajuan.show', $pengajuan)
                ->with('error', 'Pengajuan sudah diproses dan tidak bisa diedit.');
        }

        $data = $request->validate($this->rules(), $this->messages());
        $wasRevisi = $pengajuan->status === 'revisi';

        DB::transaction(function () use ($pengajuan, $data, $wasRevisi) {
            $pengajuan->update([
                'direktorat'     => $data['direktorat'] ?? null,
                'divisi'         => $data['divisi'] ?? null,
                'nama_pic'       => $data['nama_pic'] ?? $pengajuan->nama_pic,
                'judul'          => $data['judul'],
                'waktu_mulai'    => $data['waktu_mulai'] ?? null,
                'waktu_selesai'  => $data['waktu_selesai'] ?? null,
                'lokasi'         => $data['lokasi'] ?? null,
                'keterangan'     => $data['keterangan'] ?? null,
                'jumlah_relawan' => count($data['kebutuhan']),
                // Kirim ulang setelah revisi → kembali ke antrean verifikasi
                'status'         => $wasRevisi ? 'diajukan' : $pengajuan->status,
                'catatan_revisi' => $wasRevisi ? null : $pengajuan->catatan_revisi,
            ]);
            // Belum ada penugasan pada tahap ini → aman hapus & buat ulang baris
            $pengajuan->kebutuhan()->delete();
            $this->syncKebutuhan($pengajuan, $data['kebutuhan']);
        });

        $msg = $wasRevisi ? 'Pengajuan diperbaiki & dikirim ulang untuk verifikasi.' : 'Pengajuan diperbarui.';
        return redirect()->route('pengajuan.show', $pengajuan)->with('success', $msg);
    }

    public function destroy(Pengajuan $pengajuan)
    {
        $this->authorizeOwner($pengajuan);
        if (!in_array($pengajuan->status, ['diajukan', 'revisi', 'ditolak'])) {
            return back()->with('error', 'Pengajuan yang sudah diproses tidak dapat dibatalkan.');
        }
        $pengajuan->delete();
        return redirect()->route('pengajuan.index')->with('success', 'Pengajuan dibatalkan.');
    }

    // ---- SOP Bagian 3: Deployment → Evaluasi & Pelaporan (selesai) ----
    public function selesai(Request $request, Pengajuan $pengajuan)
    {
        $this->authorizeOwner($pengajuan);
        if ($pengajuan->status !== 'ditugaskan') {
            return back()->with('error', 'Laporan hanya bisa dikirim setelah relawan ditugaskan.');
        }

        $data = $request->validate([
            'bukti_file' => 'required|image|max:5120',
            'laporan'    => 'nullable|string',
        ]);

        $f = $request->file('bukti_file');
        $path = $f->store('bukti_implementasi', 'public');

        $pengajuan->update([
            'bukti_implementasi' => [
                'path' => $path, 'original' => $f->getClientOriginalName(),
                'uploaded_by' => Auth::id(), 'uploaded_at' => now()->toDateTimeString(),
            ],
            'laporan'    => $data['laporan'] ?? $pengajuan->laporan,
            'status'     => 'selesai',
            'selesai_at' => now(),
        ]);
        $this->freeRelawan($pengajuan);

        return redirect()->route('pengajuan.show', $pengajuan)
            ->with('success', 'Laporan terkirim. Pengajuan selesai. Terima kasih!');
    }

    // Kirim ulang bukti/laporan setelah admin meminta revisi laporan
    public function resubmit(Request $request, Pengajuan $pengajuan)
    {
        $this->authorizeOwner($pengajuan);
        if ($pengajuan->status !== 'ditugaskan' || !$pengajuan->catatan_revisi) {
            return back()->with('error', 'Tidak ada permintaan revisi laporan yang aktif.');
        }
        return $this->selesai($request, $pengajuan);
    }

    // ---- Helpers ----
    protected function syncKebutuhan(Pengajuan $pengajuan, array $rows): void
    {
        foreach ($rows as $row) {
            $pengajuan->kebutuhan()->create([
                'jenis_relawan'     => $row['jenis_relawan'],
                'jenis_kelamin'     => $row['jenis_kelamin'],
                'detail_tugas'      => $row['detail_tugas'] ?? null,
                'nominal_apresiasi' => $row['nominal_apresiasi'] ?? null,
            ]);
        }
    }

    protected function freeRelawan(Pengajuan $pengajuan): void
    {
        $pengajuan->loadMissing('kebutuhan.relawan');
        foreach ($pengajuan->kebutuhan as $k) {
            if ($k->relawan) {
                $k->relawan->update(['status' => 'tersedia']);
            }
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengajuan;
use App\Models\KebutuhanRelawan;
use App\Models\Relawan;
use App\Services\PengajuanNotifier;
use App\Support\JadwalPengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    public function index(Request $request)
    {
        $this->authorizeAdmin();

        $query = Pengajuan::with('user')->withCount('kebutuhan')->orderByDesc('created_at');

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%$search%")
                    ->orWhere('divisi', 'like', "%$search%")
                    ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%$search%"));
            });
        }
        // "terlambat" & "terlewat" bukan status di database, melainkan turunan
        // dari jadwal yang lewat — lihat Pengajuan::FILTER_TURUNAN.
        $query->filterStatus($request->get('status'))
            ->dalamRentang($request->get('dari'), $request->get('sampai'));

        $pengajuan = $query->paginate(15)->withQueryString();

        // Ringkasan seluruh tahap alur + penanda di luar jadwal.
        $counts = [
            'diajukan'   => Pengajuan::where('status', 'diajukan')->count(),
            'revisi'     => Pengajuan::where('status', 'revisi')->count(),
            'disetujui'  => Pengajuan::where('status', 'disetujui')->count(),
            'ditugaskan' => Pengajuan::where('status', 'ditugaskan')->count(),
            'selesai'    => Pengajuan::where('status', 'selesai')->count(),
            'ditolak'    => Pengajuan::where('status', 'ditolak')->count(),
            'terlambat'  => Pengajuan::terlambatLapor()->count(),
            'terlewat'   => Pengajuan::terlewatVerifikasi()->count(),
        ];

        // Kalender ringkas di kolom kiri — sumber data sama dengan Beranda Pengaju.
        $withSchedule = JadwalPengajuan::items();
        $calendarDates = JadwalPengajuan::tanggalBertanda($withSchedule);

        return view('admin.pengajuan.index', compact('pengajuan', 'counts', 'withSchedule', 'calendarDates'));
    }

    public function show(Pengajuan $pengajuan)
    {
        $this->authorizeAdmin();
        $pengajuan->load(['user', 'kebutuhan.relawan']);
        return view('admin.pengajuan.show', compact('pengajuan'));
    }

    // ===== SOP Bagian 1: Review & Verifikasi =====
    public function approve(Pengajuan $pengajuan)
    {
        $this->authorizeAdmin();
        if ($pengajuan->status !== 'diajukan') {
            return back()->with('error', 'Hanya pengajuan yang menunggu verifikasi yang bisa disetujui.');
        }
        $pengajuan->update(['status' => 'disetujui', 'catatan_revisi' => null]);
        Log::info('Pengajuan disetujui', ['id' => $pengajuan->id, 'admin' => Auth::id()]);
        PengajuanNotifier::disetujui($pengajuan);
        return redirect()->route('admin.pengajuan.assign_form', $pengajuan)
            ->with('success', 'Pengajuan disetujui. Silakan cari & tugaskan relawan.');
    }

    public function revisi(Request $request, Pengajuan $pengajuan)
    {
        $this->authorizeAdmin();
        if ($pengajuan->status !== 'diajukan') {
            return back()->with('error', 'Hanya pengajuan yang menunggu verifikasi yang bisa diminta revisi.');
        }
        $data = $request->validate(['catatan_revisi' => 'required|string|max:2000']);
        $pengajuan->update([
            'status'         => 'revisi',
            'catatan_revisi' => $data['catatan_revisi'],
            'revisi_count'   => ($pengajuan->revisi_count ?? 0) + 1,
        ]);
        Log::info('Pengajuan diminta revisi', ['id' => $pengajuan->id, 'admin' => Auth::id()]);
        PengajuanNotifier::revisi($pengajuan);
        return redirect()->route('admin.pengajuan.index')
            ->with('success', 'Pengajuan dikembalikan ke pengaju untuk revisi.');
    }

    public function reject(Request $request, Pengajuan $pengajuan)
    {
        $this->authorizeAdmin();
        if (in_array($pengajuan->status, ['selesai'])) {
            return back()->with('error', 'Pengajuan yang sudah selesai tidak bisa ditolak.');
        }
        $data = $request->validate(['catatan_revisi' => 'nullable|string|max:2000']);
        $this->freeRelawan($pengajuan);
        $pengajuan->kebutuhan()->update(['relawan_id' => null, 'assigned_at' => null]);
        $pengajuan->update(['status' => 'ditolak', 'catatan_revisi' => $data['catatan_revisi'] ?? null]);
        Log::info('Pengajuan ditolak', ['id' => $pengajuan->id, 'admin' => Auth::id()]);
        PengajuanNotifier::ditolak($pengajuan);
        return redirect()->route('admin.pengajuan.index')->with('success', 'Pengajuan ditolak.');
    }

    // ===== SOP Bagian 2: Penugasan (per baris kebutuhan) =====
    public function assignForm(Pengajuan $pengajuan)
    {
        $this->authorizeAdmin();
        if (!in_array($pengajuan->status, ['disetujui', 'ditugaskan'])) {
            return redirect()->route('admin.pengajuan.show', $pengajuan)
                ->with('error', 'Penugasan hanya untuk pengajuan yang sudah disetujui.');
        }
        $pengajuan->load(['user', 'kebutuhan.relawan']);

        // Kandidat relawan per baris: jenis cocok, tersedia (+ yang sudah dipilih), gender sesuai
        $candidates = [];
        foreach ($pengajuan->kebutuhan as $k) {
            // Jenis di luar taksonomi Relawan (teks bebas dari "Lainnya") tidak bisa
            // dicocokkan by jenis, jadi tampilkan semua relawan tersedia (difilter gender saja).
            $isKnownJenis = array_key_exists($k->jenis_relawan, Relawan::JENIS);

            $q = Relawan::where(function ($qq) use ($k) {
                $qq->where('status', 'tersedia');
                if ($k->relawan_id) $qq->orWhere('id', $k->relawan_id);
            });
            if ($isKnownJenis) {
                $q->where(function ($qq) use ($k) {
                    $qq->where('jenis', $k->jenis_relawan)
                        ->orWhere('jenis', 'lainnya');
                });
            }
            if (in_array($k->jenis_kelamin, ['L', 'P'])) {
                $q->where(function ($qq) use ($k) {
                    $qq->where('jenis_kelamin', $k->jenis_kelamin)->orWhereNull('jenis_kelamin');
                });
            }
            $list = $q->orderBy('nama')->get();

            // Fallback: jenis dikenal tapi tak ada kandidat sama sekali -> perluas ke semua jenis.
            if ($isKnownJenis && $list->isEmpty()) {
                $fq = Relawan::where(function ($qq) use ($k) {
                    $qq->where('status', 'tersedia');
                    if ($k->relawan_id) $qq->orWhere('id', $k->relawan_id);
                });
                if (in_array($k->jenis_kelamin, ['L', 'P'])) {
                    $fq->where(function ($qq) use ($k) {
                        $qq->where('jenis_kelamin', $k->jenis_kelamin)->orWhereNull('jenis_kelamin');
                    });
                }
                $list = $fq->orderBy('nama')->get();
            }
            $candidates[$k->id] = $list;
        }

        return view('admin.pengajuan.assign', compact('pengajuan', 'candidates'));
    }

    /**
     * Penugasan sekaligus (bulk) untuk seluruh baris kebutuhan dalam satu submit.
     * Payload: assign[<kebutuhan_id>] = <relawan_id>
     */
    public function assignBulk(Request $request, Pengajuan $pengajuan)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'assign'   => 'required|array',
            'assign.*' => 'nullable|exists:relawan,id',
        ], [
            'assign.required' => 'Belum ada kebutuhan yang bisa ditugaskan.',
            'assign.*.exists' => 'Relawan yang dipilih tidak ditemukan.',
        ]);

        // Buang baris yang tidak diisi
        $selected = collect($data['assign'])->filter(fn($v) => !empty($v))->map(fn($v) => (int) $v);

        if ($selected->isEmpty()) {
            return back()->with('error', 'Pilih minimal satu relawan terlebih dahulu.');
        }

        if ($selected->duplicates()->isNotEmpty()) {
            return back()->with('error', 'Relawan yang sama tidak boleh ditugaskan pada lebih dari satu kebutuhan.');
        }

        // Hanya kebutuhan milik pengajuan ini yang boleh diproses
        $rows = $pengajuan->kebutuhan()->whereIn('id', $selected->keys()->all())->get();
        if ($rows->count() !== $selected->count()) {
            abort(404);
        }

        $relawan = Relawan::whereIn('id', $selected->values()->all())->get()->keyBy('id');

        DB::transaction(function () use ($rows, $selected, $relawan) {
            foreach ($rows as $k) {
                $newId = $selected[$k->id];

                // Bebaskan relawan lama bila diganti
                if ($k->relawan_id && $k->relawan_id != $newId) {
                    Relawan::where('id', $k->relawan_id)->update(['status' => 'tersedia']);
                }

                $r = $relawan[$newId];
                $r->update(['status' => 'ditugaskan']);
                $k->update([
                    'relawan_id'       => $r->id,
                    'relawan_nama'     => $r->nama,
                    'relawan_kontak'   => $r->kontak,
                    'relawan_domisili' => $r->domisili,
                    'assigned_at'      => now(),
                ]);
            }
        });

        return redirect()->route('admin.pengajuan.assign_form', $pengajuan)
            ->with('success', $rows->count() . ' relawan berhasil ditugaskan.');
    }

    public function assignKebutuhan(Request $request, Pengajuan $pengajuan, KebutuhanRelawan $kebutuhan)
    {
        $this->authorizeAdmin();
        abort_unless((int)$kebutuhan->pengajuan_id === (int)$pengajuan->id, 404);

        $data = $request->validate([
            'relawan_id'       => 'nullable|exists:relawan,id',
            'relawan_nama'     => 'nullable|string|max:255',
            'relawan_kontak'   => 'nullable|string|max:100',
            'relawan_domisili' => 'nullable|string|max:255',
        ]);

        if (empty($data['relawan_id']) && empty($data['relawan_nama'])) {
            return back()->with('error', 'Pilih relawan dari daftar atau isi nama relawan secara manual.');
        }

        // Bebaskan relawan lama bila diganti
        if ($kebutuhan->relawan_id && $kebutuhan->relawan_id != ($data['relawan_id'] ?? null)) {
            Relawan::where('id', $kebutuhan->relawan_id)->update(['status' => 'tersedia']);
        }

        if (!empty($data['relawan_id'])) {
            $r = Relawan::findOrFail($data['relawan_id']);
            $r->update(['status' => 'ditugaskan']);
            $kebutuhan->update([
                'relawan_id'       => $r->id,
                'relawan_nama'     => $r->nama,
                'relawan_kontak'   => $r->kontak,
                'relawan_domisili' => $r->domisili,
                'assigned_at'      => now(),
            ]);
        } else {
            // Entri manual (Personal Volunteer Management)
            $kebutuhan->update([
                'relawan_id'       => null,
                'relawan_nama'     => $data['relawan_nama'],
                'relawan_kontak'   => $data['relawan_kontak'] ?? null,
                'relawan_domisili' => $data['relawan_domisili'] ?? null,
                'assigned_at'      => now(),
            ]);
        }

        return redirect()->route('admin.pengajuan.assign_form', $pengajuan)
            ->with('success', 'Relawan ditugaskan untuk kebutuhan ini.');
    }

    public function unassignKebutuhan(Pengajuan $pengajuan, KebutuhanRelawan $kebutuhan)
    {
        $this->authorizeAdmin();
        abort_unless((int)$kebutuhan->pengajuan_id === (int)$pengajuan->id, 404);

        if ($kebutuhan->relawan_id) {
            Relawan::where('id', $kebutuhan->relawan_id)->update(['status' => 'tersedia']);
        }
        $kebutuhan->update(['relawan_id' => null, 'relawan_nama' => null, 'relawan_kontak' => null, 'relawan_domisili' => null, 'assigned_at' => null]);

        return back()->with('success', 'Penugasan dibatalkan.');
    }

    // Finalisasi penugasan → relawan siap ditugaskan (SOP Bagian 2 → 3)
    public function tugaskan(Pengajuan $pengajuan)
    {
        $this->authorizeAdmin();
        $pengajuan->load('kebutuhan');
        if ($pengajuan->status !== 'disetujui') {
            return back()->with('error', 'Pengajuan belum siap ditugaskan.');
        }
        if (!$pengajuan->allAssigned()) {
            return back()->with('error', 'Masih ada kebutuhan yang belum diisi relawan.');
        }
        $pengajuan->update(['status' => 'ditugaskan']);
        Log::info('Pengajuan ditugaskan (siap deploy)', ['id' => $pengajuan->id, 'admin' => Auth::id()]);
        PengajuanNotifier::ditugaskan($pengajuan);
        return redirect()->route('admin.pengajuan.show', $pengajuan)
            ->with('success', 'Relawan siap ditugaskan. Pengaju diberi tahu untuk deployment.');
    }

    // ===== SOP Bagian 3: minta revisi laporan (selesai → ditugaskan) =====
    public function revisiLaporan(Request $request, Pengajuan $pengajuan)
    {
        $this->authorizeAdmin();
        if ($pengajuan->status !== 'selesai') {
            return back()->with('error', 'Hanya pengajuan selesai yang bisa diminta revisi laporan.');
        }
        $data = $request->validate(['catatan_revisi' => 'nullable|string|max:2000']);
        $pengajuan->update([
            'status'         => 'ditugaskan',
            'catatan_revisi' => $data['catatan_revisi'] ?? 'Mohon perbaiki bukti/laporan.',
            'revisi_count'   => ($pengajuan->revisi_count ?? 0) + 1,
            'selesai_at'     => null,
        ]);
        // Relawan kembali dianggap bertugas
        $pengajuan->loadMissing('kebutuhan.relawan');
        foreach ($pengajuan->kebutuhan as $k) {
            if ($k->relawan) $k->relawan->update(['status' => 'ditugaskan']);
        }
        PengajuanNotifier::revisi($pengajuan, 'laporan');
        return redirect()->route('admin.pengajuan.show', $pengajuan)
            ->with('success', 'Pengajuan dikembalikan ke pengaju untuk revisi laporan.');
    }

    public function destroy(Pengajuan $pengajuan)
    {
        $this->authorizeAdmin();
        $this->freeRelawan($pengajuan);
        $pengajuan->delete();
        Log::info('Pengajuan dihapus oleh admin', ['id' => $pengajuan->id, 'admin' => Auth::id()]);
        return redirect()->route('admin.pengajuan.index')->with('success', 'Pengajuan berhasil dihapus.');
    }

    protected function freeRelawan(Pengajuan $pengajuan): void
    {
        $pengajuan->loadMissing('kebutuhan.relawan');
        foreach ($pengajuan->kebutuhan as $k) {
            if ($k->relawan) $k->relawan->update(['status' => 'tersedia']);
        }
    }
}

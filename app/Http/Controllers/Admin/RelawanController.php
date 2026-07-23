<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Relawan;
use App\Models\BidangRelawan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RelawanController extends Controller
{
    /** Hanya admin (super atau per-bidang) yang boleh mengelola SDM relawan. */
    protected function authorizeAdmin(): void
    {
        $role = optional(auth()->user())->role ?? '';
        if (!str_starts_with($role, 'admin')) {
            abort(403, 'Unauthorized');
        }
    }

    // Index: tampilkan daftar bidang + daftar relawan (mirip halaman assets)
    public function index(Request $request)
    {
        $this->authorizeAdmin();

        $bidangs = BidangRelawan::orderBy('nama')->get();

        $query = Relawan::with('bidang')->orderBy('nama');

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%$search%")
                  ->orWhere('keahlian', 'like', "%$search%")
                  ->orWhere('domisili', 'like', "%$search%")
                  ->orWhere('kontak', 'like', "%$search%");
            });
        }

        if ($bidang = $request->get('bidang_relawan_id')) {
            $query->where('bidang_relawan_id', $bidang);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $relawan = $query->paginate(20)->withQueryString();

        return view('admin.relawan.index', compact('bidangs', 'relawan'));
    }

    public function create()
    {
        $this->authorizeAdmin();
        $bidangs = BidangRelawan::orderBy('nama')->get();
        return view('admin.relawan.create', compact('bidangs'));
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'nama'              => 'required|string|max:255',
            'kontak'            => 'nullable|string|max:100',
            'email'             => 'nullable|email|max:255',
            'domisili'          => 'nullable|string|max:255',
            'bidang_relawan_id' => 'nullable|exists:bidang_relawan,id',
            'keahlian'          => 'nullable|string',
            'status'            => ['nullable', Rule::in(array_keys(Relawan::STATUSES))],
            'catatan'           => 'nullable|string',
        ]);
        $data['status'] = $data['status'] ?? 'tersedia';

        Relawan::create($data);

        return redirect()->route('admin.relawan.index')->with('success', 'Data relawan ditambahkan.');
    }

    public function edit(Relawan $relawan)
    {
        $this->authorizeAdmin();
        $bidangs = BidangRelawan::orderBy('nama')->get();
        return view('admin.relawan.edit', compact('relawan', 'bidangs'));
    }

    public function update(Request $request, Relawan $relawan)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'nama'              => 'required|string|max:255',
            'kontak'            => 'nullable|string|max:100',
            'email'             => 'nullable|email|max:255',
            'domisili'          => 'nullable|string|max:255',
            'bidang_relawan_id' => 'nullable|exists:bidang_relawan,id',
            'keahlian'          => 'nullable|string',
            'status'            => ['nullable', Rule::in(array_keys(Relawan::STATUSES))],
            'catatan'           => 'nullable|string',
        ]);
        $data['status'] = $data['status'] ?? $relawan->status;

        $relawan->update($data);

        return redirect()->route('admin.relawan.index')->with('success', 'Data relawan diperbarui.');
    }

    public function destroy(Relawan $relawan)
    {
        $this->authorizeAdmin();
        $relawan->delete();
        return redirect()->route('admin.relawan.index')->with('success', 'Data relawan dihapus.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BidangRelawan;
use Illuminate\Http\Request;

class BidangRelawanController extends Controller
{
    protected function authorizeAdmin(): void
    {
        $role = optional(auth()->user())->role ?? '';
        if (!str_starts_with($role, 'admin')) {
            abort(403, 'Unauthorized');
        }
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'nama'      => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        BidangRelawan::create($data);

        return redirect()->route('admin.relawan.index')->with('success', 'Bidang relawan ditambahkan.');
    }

    public function update(Request $request, BidangRelawan $bidang)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'nama'      => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        $bidang->update($data);

        return redirect()->route('admin.relawan.index')->with('success', 'Bidang relawan diperbarui.');
    }

    public function destroy(BidangRelawan $bidang)
    {
        $this->authorizeAdmin();
        $bidang->delete();
        return redirect()->route('admin.relawan.index')->with('success', 'Bidang relawan dihapus.');
    }
}

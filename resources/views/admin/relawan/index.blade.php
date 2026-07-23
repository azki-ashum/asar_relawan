@extends('layouts.relawan')

@section('title', 'Admin - Data Relawan')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h3 class="mb-0">Data Relawan (SDM)</h3>
        <div class="text-muted small">Kelola daftar relawan yang bisa ditugaskan ke pengajuan.</div>
    </div>
    <div class="main-actions">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#bidangModal">
            <i class="bi bi-tags me-1"></i>Bidang Relawan
        </button>
        <a href="{{ route('admin.relawan.create') }}" class="btn btn-success btn-sm"><i class="bi bi-plus-lg me-1"></i>Relawan</a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="get" class="row g-2 mb-3">
            <div class="col-12 col-md-5">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Cari nama / keahlian / domisili / kontak...">
            </div>
            <div class="col-6 col-md-3">
                <select name="bidang_relawan_id" class="form-select form-select-sm">
                    <option value="">Semua Bidang</option>
                    @foreach($bidangs as $b)
                        <option value="{{ $b->id }}" @selected(request('bidang_relawan_id') == $b->id)>{{ $b->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    @foreach(\App\Models\Relawan::STATUSES as $key => $meta)
                        <option value="{{ $key }}" @selected(request('status') === $key)>{{ $meta['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2 d-grid">
                <button class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i>Filter</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-muted">Nama</th>
                        <th class="text-muted">Bidang</th>
                        <th class="text-muted">Domisili</th>
                        <th class="text-muted">Kontak</th>
                        <th class="text-muted">Status</th>
                        <th class="text-muted text-center" style="min-width:110px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($relawan as $r)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $r->nama }}</div>
                            @if($r->keahlian)<div class="small text-muted">{{ \Illuminate\Support\Str::limit($r->keahlian, 60) }}</div>@endif
                        </td>
                        <td>@if($r->bidang)<span class="badge bg-light text-dark border">{{ $r->bidang->nama }}</span>@else <span class="text-muted">—</span>@endif</td>
                        <td>{{ $r->domisili ?? '—' }}</td>
                        <td>{{ $r->kontak ?? '—' }}</td>
                        <td>
                            @php $s = \App\Models\Relawan::STATUSES[$r->status] ?? ['label' => ucfirst($r->status), 'class' => 'badge-soft-secondary']; @endphp
                            <span class="badge {{ $s['class'] }}">{{ $s['label'] }}</span>
                        </td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('admin.relawan.edit', $r) }}" class="btn btn-sm btn-light border" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('admin.relawan.destroy', $r) }}" method="post" class="d-inline swal-confirm" data-confirm="Hapus relawan {{ $r->nama }}?">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-light border text-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data relawan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($relawan->hasPages())
        <div class="card-footer bg-white border-0 d-flex justify-content-end">
            {{ $relawan->links() }}
        </div>
    @endif
</div>

<!-- Modal: Kelola Bidang Relawan -->
<div class="modal fade" id="bidangModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-tags me-1"></i>Bidang Relawan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.bidang_relawan.store') }}" method="post" class="row g-2 mb-3">
                    @csrf
                    <div class="col-12 col-md-4">
                        <input type="text" name="nama" class="form-control form-control-sm" placeholder="Nama bidang (mis. Medis)" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <input type="text" name="deskripsi" class="form-control form-control-sm" placeholder="Deskripsi (opsional)">
                    </div>
                    <div class="col-12 col-md-2 d-grid">
                        <button class="btn btn-success btn-sm"><i class="bi bi-plus-lg me-1"></i>Tambah</button>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>Nama</th><th>Deskripsi</th><th class="text-center" style="min-width:100px">Aksi</th></tr></thead>
                        <tbody>
                            @forelse($bidangs as $b)
                            <tr>
                                <td class="fw-semibold">{{ $b->nama }}</td>
                                <td class="small text-muted">{{ $b->deskripsi ?? '—' }}</td>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <button type="button" class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#editBidang{{ $b->id }}" title="Edit"><i class="bi bi-pencil"></i></button>
                                        <form action="{{ route('admin.bidang_relawan.destroy', $b) }}" method="post" class="d-inline swal-confirm" data-confirm="Hapus bidang {{ $b->nama }}? Relawan/pengajuan terkait akan kehilangan bidang ini.">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-light border text-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">Belum ada bidang.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Per-bidang edit modals -->
@foreach($bidangs as $b)
<div class="modal fade" id="editBidang{{ $b->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.bidang_relawan.update', $b) }}" method="post">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Bidang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label small">Nama</label>
                        <input type="text" name="nama" class="form-control" value="{{ $b->nama }}" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="2">{{ $b->deskripsi }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection

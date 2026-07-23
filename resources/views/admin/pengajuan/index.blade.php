@extends('layouts.relawan')

@section('title', 'Admin - Pengajuan')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h3 class="mb-0">Pengajuan Masuk</h3>
        <div class="text-muted small">Cari relawan yang cocok lalu tugaskan ke setiap pengajuan.</div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="get" class="row g-2 mb-3">
            <div class="col-12 col-md-8">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Cari judul / kebutuhan / nama pengaju...">
            </div>
            <div class="col-8 col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    @foreach(\App\Models\Pengajuan::STATUSES as $key => $meta)
                        <option value="{{ $key }}" @selected(request('status') === $key)>{{ $meta['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-4 col-md-2 d-grid">
                <button class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i>Filter</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr>
                    <th class="text-muted">Judul</th>
                    <th class="text-muted">Pengaju</th>
                    <th class="text-muted">Bidang</th>
                    <th class="text-muted">Jml</th>
                    <th class="text-muted">Relawan</th>
                    <th class="text-muted">Status</th>
                    <th class="text-muted text-end">Aksi</th>
                </tr></thead>
                <tbody>
                    @forelse($pengajuan as $p)
                    <tr>
                        <td class="wrap fw-semibold">{{ $p->judul }}<div class="small text-muted">{{ $p->created_at->format('d M Y') }}</div></td>
                        <td>{{ $p->user->name ?? '—' }}</td>
                        <td>{{ $p->bidang->nama ?? '—' }}</td>
                        <td>{{ $p->jumlah_relawan }}</td>
                        <td>{{ $p->relawan->nama ?? '—' }}</td>
                        <td>@include('pengajuan._status', ['status' => $p->status])</td>
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                @if(in_array($p->status, ['diajukan', 'dicari', 'ditugaskan']))
                                    <a href="{{ route('admin.pengajuan.assign_form', $p) }}" class="btn btn-sm btn-success" title="Cari & Assign"><i class="bi bi-person-plus me-1"></i>{{ $p->status === 'ditugaskan' ? 'Ganti' : 'Assign' }}</a>
                                @endif
                                <a href="{{ route('admin.pengajuan.show', $p) }}" class="btn btn-sm btn-light border">Detail</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Belum ada pengajuan masuk.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($pengajuan->hasPages())
        <div class="card-footer bg-white border-0 d-flex justify-content-end">{{ $pengajuan->links() }}</div>
    @endif
</div>
@endsection

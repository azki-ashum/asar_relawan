@extends('layouts.relawan')

@section('title', 'Semua Pengajuan')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h3 class="mb-0">Semua Pengajuan</h3>
        <div class="text-muted small">Seluruh pengajuan kebutuhan relawan dari semua pengaju.</div>
    </div>
    <a href="{{ route('pengajuan.create') }}" class="btn btn-success head-actions"><i class="bi bi-plus-lg me-1"></i>Buat Pengajuan</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="get" class="row g-2 mb-3">
            <div class="col-12 col-md-7">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Cari judul / kebutuhan / lokasi...">
            </div>
            <div class="col-8 col-md-3">
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
            <table class="table table-hover table-stack align-middle mb-0">
                <thead><tr>
                    <th>Kegiatan</th>
                    <th>Pengaju</th>
                    <th>Divisi</th>
                    <th>Kebutuhan</th>
                    <th>Waktu</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr></thead>
                <tbody>
                    @forelse($pengajuan as $p)
                    <tr>
                        <td class="cell-title wrap">{{ $p->judul }}</td>
                        <td data-label="Pengaju">{{ $p->nama_pic ?? ($p->user->name ?? '—') }}</td>
                        <td data-label="Divisi">{{ $p->divisi ?? '—' }}</td>
                        <td data-label="Kebutuhan">{{ $p->kebutuhan_count }} baris</td>
                        <td data-label="Waktu">{{ optional($p->waktu_mulai)->format('d M Y') ?? '—' }}</td>
                        <td data-label="Status">@include('pengajuan._status', ['status' => $p->status])</td>
                        <td class="cell-actions text-end"><a href="{{ route('pengajuan.show', $p) }}" class="btn btn-sm btn-light border">Detail</a></td>
                    </tr>
                    @empty
                    <tr class="no-card"><td colspan="7"><div class="empty-state"><i class="bi bi-inbox"></i>Belum ada pengajuan.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($pengajuan->hasPages())
        <div class="card-footer bg-white border-0 d-flex justify-content-end">
            {{ $pengajuan->links() }}
        </div>
    @endif
</div>
@endsection

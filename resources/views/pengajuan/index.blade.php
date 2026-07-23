@extends('layouts.relawan')

@section('title', 'Pengajuan Saya')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h3 class="mb-0">Pengajuan Saya</h3>
    <a href="{{ route('pengajuan.create') }}" class="btn btn-success"><i class="bi bi-plus-lg me-1"></i>Buat Pengajuan</a>
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
            <table class="table table-hover align-middle mb-0">
                <thead><tr>
                    <th class="text-muted">Judul</th>
                    <th class="text-muted">Bidang</th>
                    <th class="text-muted">Jml</th>
                    <th class="text-muted">Tanggal</th>
                    <th class="text-muted">Relawan</th>
                    <th class="text-muted">Status</th>
                    <th class="text-muted text-end">Aksi</th>
                </tr></thead>
                <tbody>
                    @forelse($pengajuan as $p)
                    <tr>
                        <td class="wrap fw-semibold">{{ $p->judul }}</td>
                        <td>{{ $p->bidang->nama ?? '—' }}</td>
                        <td>{{ $p->jumlah_relawan }}</td>
                        <td>{{ optional($p->tanggal_kegiatan)->format('d M Y') ?? '—' }}</td>
                        <td>{{ $p->relawan->nama ?? '—' }}</td>
                        <td>@include('pengajuan._status', ['status' => $p->status])</td>
                        <td class="text-end"><a href="{{ route('pengajuan.show', $p) }}" class="btn btn-sm btn-light border">Detail</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Belum ada pengajuan.</td></tr>
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

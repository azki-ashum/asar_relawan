@extends('layouts.relawan')

@section('title', 'Admin - Pengajuan')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h3 class="mb-0">Pengajuan Relawan</h3>
        <div class="text-muted small">Verifikasi pengajuan, lalu cari &amp; tugaskan relawan sesuai kebutuhan.</div>
    </div>
</div>

{{-- Ringkasan antrean --}}
<div class="row g-2 mb-3">
    @php
        $queue = [
            ['label' => 'Menunggu Verifikasi', 'value' => $counts['diajukan'],   'color' => 'text-secondary', 'icon' => 'bi-inbox', 'status' => 'diajukan'],
            ['label' => 'Perlu Penugasan',     'value' => $counts['disetujui'],  'color' => 'text-info',      'icon' => 'bi-search', 'status' => 'disetujui'],
            ['label' => 'Sedang Berjalan',     'value' => $counts['ditugaskan'], 'color' => 'text-warning',   'icon' => 'bi-person-check', 'status' => 'ditugaskan'],
        ];
    @endphp
    @foreach($queue as $q)
    <div class="col-12 col-md-4">
        <a href="{{ route('admin.pengajuan.index', ['status' => $q['status']]) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm stat-card">
                <div class="card-body d-flex align-items-center justify-content-between py-3">
                    <div>
                        <div class="text-muted small">{{ $q['label'] }}</div>
                        <div class="h3 mb-0 {{ $q['color'] }}">{{ $q['value'] }}</div>
                    </div>
                    <i class="bi {{ $q['icon'] }} fs-3 {{ $q['color'] }}"></i>
                </div>
            </div>
        </a>
    </div>
    @endforeach
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="get" class="row g-2 mb-3">
            <div class="col-12 col-md-8">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Cari kegiatan / divisi / pengaju...">
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
            <table class="table table-hover table-stack align-middle mb-0">
                <thead><tr>
                    <th>Kegiatan</th>
                    <th>Pengaju</th>
                    <th>Divisi</th>
                    <th>Kebutuhan</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr></thead>
                <tbody>
                    @forelse($pengajuan as $p)
                    <tr>
                        <td class="cell-title wrap">{{ $p->judul }}<div class="small text-muted fw-normal">{{ $p->created_at->format('d M Y') }}</div></td>
                        <td data-label="Pengaju">{{ $p->user->name ?? '—' }}</td>
                        <td data-label="Divisi">{{ $p->divisi ?? '—' }}</td>
                        <td data-label="Kebutuhan">{{ $p->jumlah_relawan }} Relawan</td>
                        <td data-label="Status">@include('pengajuan._status', ['status' => $p->status])</td>
                        <td class="cell-actions text-end">
                            <div class="d-flex gap-1 justify-content-end flex-wrap">
                                @if($p->status === 'diajukan')
                                    <a href="{{ route('admin.pengajuan.show', $p) }}" class="btn btn-sm btn-primary"><i class="bi bi-clipboard-check me-1"></i>Verifikasi</a>
                                @elseif(in_array($p->status, ['disetujui', 'ditugaskan']))
                                    <a href="{{ route('admin.pengajuan.assign_form', $p) }}" class="btn btn-sm btn-success"><i class="bi bi-person-plus me-1"></i>Penugasan</a>
                                    <a href="{{ route('admin.pengajuan.show', $p) }}" class="btn btn-sm btn-light border"><i class="bi bi-eye me-1"></i>Detail</a>
                                @else
                                    <a href="{{ route('admin.pengajuan.show', $p) }}" class="btn btn-sm btn-light border"><i class="bi bi-eye me-1"></i>Detail</a>
                                @endif
                                <form action="{{ route('admin.pengajuan.destroy', $p) }}" method="post" class="swal-confirm" data-confirm="Hapus pengajuan ini secara permanen dari database?">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i>Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="no-card"><td colspan="6"><div class="empty-state"><i class="bi bi-inboxes"></i>Belum ada pengajuan masuk.</div></td></tr>
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

@extends('layouts.relawan')

@section('title', 'Beranda')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h3 class="mb-0">Halo, {{ \Illuminate\Support\Str::title(auth()->user()->name ?? 'Pengaju') }} 👋</h3>
        <div class="text-muted small">Ringkasan pengajuan kebutuhan relawan Anda.</div>
    </div>
    <a href="{{ route('pengajuan.create') }}" class="btn btn-success"><i class="bi bi-plus-lg me-1"></i>Buat Pengajuan</a>
</div>

{{-- Notifikasi aksi yang diperlukan --}}
@foreach($needAction as $p)
    @if($p->status === 'ditugaskan')
    <div class="alert alert-warning d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <i class="bi bi-person-check me-1"></i>
            Pengajuan <strong>{{ $p->judul }}</strong> sudah ditugaskan ke relawan
            <strong>{{ $p->relawan->nama ?? '-' }}</strong>. Unggah foto bukti implementasi untuk menyelesaikannya.
        </div>
        <a href="{{ route('pengajuan.show', $p) }}" class="btn btn-sm btn-warning">Unggah Bukti</a>
    </div>
    @else
    <div class="alert alert-danger d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <i class="bi bi-arrow-counterclockwise me-1"></i>
            Pengajuan <strong>{{ $p->judul }}</strong> diminta <strong>revisi</strong> oleh admin.
            @if($p->catatan_revisi)<span class="d-block small mt-1">Catatan: {{ $p->catatan_revisi }}</span>@endif
        </div>
        <a href="{{ route('pengajuan.show', $p) }}" class="btn btn-sm btn-danger">Perbaiki</a>
    </div>
    @endif
@endforeach

<div class="row g-3 mb-4">
    @php
        $tiles = [
            ['label' => 'Total Pengajuan', 'value' => $counts['total'],      'icon' => 'bi-collection', 'color' => 'text-primary'],
            ['label' => 'Sedang Dicari',   'value' => $counts['diajukan'],   'icon' => 'bi-search',     'color' => 'text-info'],
            ['label' => 'Ditugaskan',      'value' => $counts['ditugaskan'], 'icon' => 'bi-person-check','color' => 'text-warning'],
            ['label' => 'Selesai',         'value' => $counts['selesai'],    'icon' => 'bi-check-circle','color' => 'text-success'],
        ];
    @endphp
    @foreach($tiles as $t)
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm stat-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <span class="text-muted small">{{ $t['label'] }}</span>
                    <i class="bi {{ $t['icon'] }} {{ $t['color'] }}"></i>
                </div>
                <div class="display-6 {{ $t['color'] }}">{{ $t['value'] }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Pengajuan Terbaru</h5>
        <a href="{{ route('pengajuan.index') }}" class="btn btn-sm btn-link text-decoration-none">Lihat semua</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr>
                    <th class="text-muted">Judul</th>
                    <th class="text-muted">Bidang</th>
                    <th class="text-muted">Relawan</th>
                    <th class="text-muted">Status</th>
                    <th class="text-muted text-end">Aksi</th>
                </tr></thead>
                <tbody>
                    @forelse($recent as $p)
                    <tr>
                        <td class="wrap fw-semibold">{{ $p->judul }}</td>
                        <td>{{ $p->bidang->nama ?? '—' }}</td>
                        <td>{{ $p->relawan->nama ?? '—' }}</td>
                        <td>@include('pengajuan._status', ['status' => $p->status])</td>
                        <td class="text-end"><a href="{{ route('pengajuan.show', $p) }}" class="btn btn-sm btn-light border">Detail</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Belum ada pengajuan. <a href="{{ route('pengajuan.create') }}">Buat sekarang</a>.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

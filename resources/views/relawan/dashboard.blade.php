@extends('layouts.relawan')

@section('title', 'Beranda')

@section('content')
{{-- Hero sapaan --}}
<div class="card hero-card border-0 mb-4 overflow-hidden"
    style="background:linear-gradient(120deg,#0f7a46 0%,#16a34a 55%,#22c55e 100%);">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3 text-white"
        style="padding:1.6rem 1.75rem;">
        <div>
            <div class="opacity-75 small mb-1"><i class="bi bi-calendar3 me-1"></i>{{ now()->translatedFormat('l, d F
                Y') }}</div>
            <h3 class="mb-1 text-white">Halo, {{ \Illuminate\Support\Str::title(auth()->user()->name ?? 'Pengaju') }} 👋
            </h3>
            <div class="opacity-75">Ringkasan seluruh pengajuan kebutuhan relawan.</div>
        </div>
        <a href="{{ route('pengajuan.create') }}" class="btn btn-light fw-semibold"><i
                class="bi bi-plus-lg me-1"></i>Buat Pengajuan</a>
    </div>
</div>

{{-- Notifikasi aksi yang diperlukan --}}
@foreach($needAction as $p)
@if($p->status === 'revisi')
<div class="alert alert-warning d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div class="d-flex align-items-start gap-2">
        <i class="bi bi-arrow-counterclockwise mt-1"></i>
        <div>Pengajuan <strong>{{ $p->judul }}</strong> diminta <strong>revisi</strong> oleh Tim Ksatria.
            @if($p->catatan_revisi)<span class="d-block small mt-1 opacity-75">Catatan: {{ $p->catatan_revisi
                }}</span>@endif</div>
    </div>
    <a href="{{ route('pengajuan.edit', $p) }}" class="btn btn-sm btn-warning">Perbaiki</a>
</div>
@else
<div class="alert alert-info d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div class="d-flex align-items-start gap-2">
        <i class="bi bi-person-check mt-1"></i>
        <div>Relawan untuk <strong>{{ $p->judul }}</strong> sudah ditugaskan.
            {{ $p->catatan_revisi ? 'Admin meminta revisi laporan.' : 'Unggah bukti & laporan untuk menyelesaikannya.'
            }}</div>
    </div>
    <a href="{{ route('pengajuan.show', $p) }}" class="btn btn-sm btn-success">{{ $p->catatan_revisi ? 'Perbaiki
        Laporan' : 'Kirim Laporan' }}</a>
</div>
@endif
@endforeach

<div class="row g-3">
    {{-- Kartu statistik: kolom kiri, ditumpuk 1 kolom ke bawah --}}
    <div class="col-12 col-lg-3">
        <div class="row g-3">
            @php
            $tiles = [
            ['label' => 'Total Pengajuan', 'value' => $counts['total'], 'icon' => 'bi-collection', 'color' =>
            'text-primary'],
            ['label' => 'Diproses', 'value' => $counts['menunggu'], 'icon' => 'bi-hourglass-split', 'color' =>
            'text-info'],
            ['label' => 'Ditugaskan', 'value' => $counts['ditugaskan'], 'icon' => 'bi-person-check', 'color' =>
            'text-warning'],
            ['label' => 'Selesai', 'value' => $counts['selesai'], 'icon' => 'bi-flag', 'color' => 'text-success'],
            ];
            @endphp
            @foreach($tiles as $t)
            <div class="col-6 col-lg-12">
                <div class="card shadow-sm stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="text-muted small fw-semibold">{{ $t['label'] }}</span>
                            <i class="bi {{ $t['icon'] }} {{ $t['color'] }}"></i>
                        </div>
                        <div class="display-6 {{ $t['color'] }}">{{ $t['value'] }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Pengajuan terbaru: kolom kanan --}}
    <div class="col-12 col-lg-9">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Pengajuan Terbaru</h5>
                <a href="{{ route('pengajuan.index') }}" class="btn btn-sm btn-link text-decoration-none">Lihat semua <i
                        class="bi bi-arrow-right ms-1"></i></a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-stack align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Kegiatan</th>
                                <th>Pengaju</th>
                                <th>Kebutuhan</th>
                                <th>Waktu</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recent as $p)
                            <tr>
                                <td class="cell-title wrap">{{ $p->judul }}</td>
                                <td data-label="Pengaju">{{ $p->nama_pic ?? ($p->user->name ?? '—') }}</td>
                                <td data-label="Kebutuhan">{{ $p->kebutuhan_count }} Relawan</td>
                                <td data-label="Waktu">{{ optional($p->waktu_mulai)->format('d M Y') ?? '—' }}</td>
                                <td data-label="Status">@include('pengajuan._status', ['status' => $p->status])</td>
                                <td class="cell-actions text-end"><a href="{{ route('pengajuan.show', $p) }}"
                                        class="btn btn-sm btn-light">Detail</a></td>
                            </tr>
                            @empty
                            <tr class="no-card">
                                <td colspan="6">
                                    <div class="empty-state"><i class="bi bi-inbox"></i>Belum ada pengajuan. <a
                                            href="{{ route('pengajuan.create') }}">Buat sekarang</a>.</div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
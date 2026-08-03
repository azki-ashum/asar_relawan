@extends('layouts.relawan')

@section('title', 'Pengajuan Saya')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h3 class="mb-0">Pengajuan Saya</h3>
        <div class="text-muted small">Seluruh pengajuan kebutuhan relawan yang Anda buat.</div>
    </div>
    <a href="{{ route('pengajuan.create') }}" class="btn btn-success head-actions"><i
            class="bi bi-plus-lg me-1"></i>Buat Pengajuan</a>
</div>

{{-- Selalu tampil selama masih ada pengajuan yang lewat waktu selesai,
     termasuk saat filter "Terlambat Lapor" sedang aktif. --}}
@if(($terlambatCount ?? 0) > 0)
@php $filterTerlambatAktif = request('status') === \App\Models\Pengajuan::FILTER_TERLAMBAT; @endphp
<div class="alert alert-danger d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div class="d-flex align-items-start gap-2">
        <i class="bi bi-clock-history mt-1"></i>
        <div><strong>{{ $terlambatCount }} pengajuan</strong> sudah lewat waktu selesai tapi laporannya belum masuk.</div>
    </div>
    @unless($filterTerlambatAktif)
    <a href="{{ route('pengajuan.index', ['status' => \App\Models\Pengajuan::FILTER_TERLAMBAT]) }}"
        class="btn btn-sm btn-danger ms-auto">Lihat</a>
    @endunless
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="get" class="row g-2 mb-3">
            <div class="col-12 col-md-7">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                    placeholder="Cari judul / kebutuhan / lokasi...">
            </div>
            <div class="col-8 col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    @foreach(\App\Models\Pengajuan::STATUSES as $key => $meta)
                    <option value="{{ $key }}" @selected(request('status')===$key)>{{ $meta['label'] }}</option>
                    @endforeach
                    @foreach(\App\Models\Pengajuan::FILTER_TURUNAN as $key => $label)
                    <option value="{{ $key }}" @selected(request('status')===$key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-4 col-md-2 d-grid">
                <button class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i>Filter</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-stack align-middle mb-0">
                <thead>
                    <tr>
                        <th>Kegiatan</th>
                        <th>Pengaju</th>
                        <th>Divisi</th>
                        <th>Kebutuhan</th>
                        <th>Waktu</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengajuan as $p)
                    @php
                    $canEdit = ($p->user_id === auth()->id() || (auth()->check() && auth()->user()->isAdmin()))
                    && in_array($p->status, ['diajukan', 'revisi']);
                    @endphp
                    <tr>
                        <td class="cell-title wrap">{{ $p->judul }}</td>
                        <td data-label="Pengaju">{{ $p->nama_pic ?? ($p->user->name ?? '—') }}</td>
                        <td data-label="Divisi">{{ $p->divisi ?? '—' }}</td>
                        <td data-label="Kebutuhan">{{ $p->jumlah_relawan }} Relawan</td>
                        <td data-label="Waktu">{{ optional($p->waktu_mulai)->format('d M Y') ?? '—' }}</td>
                        <td data-label="Status">
                            @include('pengajuan._status_akhir', ['pengajuan' => $p])
                        </td>
                        <td class="cell-actions text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('pengajuan.show', $p) }}"
                                    class="btn btn-sm btn-light border">Detail</a>
                                @if($canEdit)
                                <a href="{{ route('pengajuan.edit', $p) }}" class="btn btn-sm btn-outline-primary"
                                    title="{{ $p->status === 'revisi' ? 'Perbaiki pengajuan' : 'Edit pengajuan' }}">
                                    <i class="bi bi-pencil me-1"></i>{{ $p->status === 'revisi' ? 'Perbaiki' : 'Edit' }}
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="no-card">
                        <td colspan="7">
                            <div class="empty-state"><i class="bi bi-inbox"></i>Belum ada pengajuan.</div>
                        </td>
                    </tr>
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
@extends('layouts.relawan')

@section('title', 'Detail Pengajuan')

@php 
    $isOwner = $pengajuan->user_id === auth()->id();
    $canManage = $isOwner || (auth()->check() && auth()->user()->isAdmin());
@endphp

@section('content')
<div class="page-header mb-3">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
        <div class="d-flex align-items-center gap-2 page-header-meta">
            <a href="{{ route('pengajuan.index') }}" class="btn-back"><i class="bi bi-arrow-left"></i></a>
            @include('pengajuan._status', ['status' => $pengajuan->status])
            @unless($canManage)
                <span class="badge badge-soft-secondary"><i class="bi bi-eye me-1"></i>Lihat saja</span>
            @endunless
        </div>
        @if($canManage && in_array($pengajuan->status, ['diajukan', 'revisi']))
        <div class="d-flex gap-2 head-actions">
            <a href="{{ route('pengajuan.edit', $pengajuan) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil me-1"></i>{{ $pengajuan->status === 'revisi' ? 'Perbaiki' : 'Edit' }}</a>
            <form action="{{ route('pengajuan.destroy', $pengajuan) }}" method="post" class="swal-confirm" data-confirm="Batalkan pengajuan ini?">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger w-100"><i class="bi bi-x-lg me-1"></i>Batalkan</button>
            </form>
        </div>
        @endif
    </div>
    <h3 class="mb-0">{{ $pengajuan->judul }}</h3>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        @include('pengajuan._timeline')
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-0"><h5 class="mb-0">Informasi Kegiatan</h5></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">Direktorat / Divisi</dt>
                    <dd class="col-sm-8">{{ $pengajuan->direktorat ?? '—' }}{{ $pengajuan->divisi ? ' / '.$pengajuan->divisi : '' }}</dd>
                    <dt class="col-sm-4 text-muted">PIC / Pengaju</dt><dd class="col-sm-8">{{ $pengajuan->nama_pic ?? $pengajuan->user->name }}</dd>
                    <dt class="col-sm-4 text-muted">Waktu Pelaksanaan</dt>
                    <dd class="col-sm-8">
                        {{ optional($pengajuan->waktu_mulai)->format('d M Y, H:i') ?? '—' }}
                        @if($pengajuan->waktu_selesai) &ndash; {{ $pengajuan->waktu_selesai->format('d M Y, H:i') }}@endif
                    </dd>
                    <dt class="col-sm-4 text-muted">Lokasi</dt><dd class="col-sm-8">{{ $pengajuan->lokasi ?? '—' }}</dd>
                    @if($pengajuan->keterangan)
                    <dt class="col-12 text-muted mt-2">Keterangan</dt>
                    <dd class="col-12" style="white-space:pre-line">{{ $pengajuan->keterangan }}</dd>
                    @endif
                </dl>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-people me-1"></i>Kebutuhan Relawan</h5>
                <span class="badge bg-light text-dark border">{{ $pengajuan->assignedCount() }}/{{ $pengajuan->kebutuhan->count() }} terisi</span>
            </div>
            <div class="card-body">
                @foreach($pengajuan->kebutuhan as $idx => $k)
                <div class="border rounded p-3 mb-2">
                    <div class="d-flex flex-wrap justify-content-between gap-2">
                        <div>
                            <span class="fw-semibold">#{{ $idx + 1 }} · {{ $k->jenisLabel() }}</span>
                            <span class="badge badge-soft-secondary ms-1">{{ $k->jenisKelaminLabel() }}</span>
                            {{-- @if($k->nominal_apresiasi)<span class="badge badge-soft-info ms-1">Rp {{ number_format($k->nominal_apresiasi, 0, ',', '.') }}</span>@endif --}}
                        </div>
                        @if($k->isAssigned())
                            <span class="badge badge-soft-success"><i class="bi bi-check-circle me-1"></i>Terisi</span>
                        @else
                            <span class="badge badge-soft-warning"><i class="bi bi-hourglass-split me-1"></i>Dicari</span>
                        @endif
                    </div>
                    @if($k->detail_tugas)<div class="small text-muted mt-1" style="white-space:pre-line">{{ $k->detail_tugas }}</div>@endif
                    @if($k->isAssigned())
                    <div class="mt-2 pt-2 border-top small">
                        <div><i class="bi bi-person-badge me-1 text-success"></i><strong>{{ $k->assignedName() }}</strong></div>
                        @if($k->relawan_kontak)<div class="mt-1"><i class="bi bi-telephone me-1"></i>{{ $k->relawan_kontak }}</div>@endif
                        @if($k->relawan_domisili)<div class="mt-1"><i class="bi bi-geo-alt me-1"></i>{{ $k->relawan_domisili }}</div>@endif
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="bi bi-clipboard-check me-1"></i>Evaluasi & Pelaporan</h5></div>
            <div class="card-body">
                @if($pengajuan->status === 'ditugaskan')
                    @if($pengajuan->catatan_revisi)
                        <div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-triangle me-1"></i><strong>Revisi laporan:</strong> {{ $pengajuan->catatan_revisi }}</div>
                    @else
                        <p class="text-muted small">Relawan sudah ditugaskan. Setelah kegiatan selesai, {{ $canManage ? 'unggah foto bukti & laporan singkat untuk menutup pengajuan.' : 'pengaju perlu mengunggah foto bukti & laporan untuk menutup pengajuan.' }}</p>
                    @endif
                    @if($canManage)
                    <form action="{{ route('pengajuan.selesai', $pengajuan) }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <label class="form-label small mb-1">Foto Bukti Implementasi <span class="text-danger">*</span></label>
                        <input type="file" name="bukti_file" accept="image/*" class="form-control mb-2" required>
                        <label class="form-label small mb-1">Laporan / Evaluasi</label>
                        <textarea name="laporan" class="form-control mb-2" rows="3" placeholder="Ringkasan pelaksanaan, jumlah relawan hadir, catatan apresiasi, dsb.">{{ old('laporan', $pengajuan->laporan) }}</textarea>
                        <button class="btn btn-success w-100"><i class="bi bi-send-check me-1"></i>Kirim Laporan &amp; Selesaikan</button>
                    </form>
                    @endif
                @elseif($pengajuan->status === 'selesai')
                    @if(!empty($pengajuan->bukti_implementasi['path']))
                    <a href="{{ asset('storage/'.$pengajuan->bukti_implementasi['path']) }}" target="_blank">
                        <img src="{{ asset('storage/'.$pengajuan->bukti_implementasi['path']) }}" alt="Bukti" class="img-fluid rounded border mb-2" style="max-height:260px;object-fit:cover;width:100%">
                    </a>
                    @endif
                    @if($pengajuan->laporan)<div class="small" style="white-space:pre-line"><strong>Laporan:</strong><br>{{ $pengajuan->laporan }}</div>@endif
                    <div class="small text-muted mt-2"><i class="bi bi-check-circle text-success me-1"></i>Selesai pada {{ optional($pengajuan->selesai_at)->format('d M Y H:i') }}</div>
                @else
                    <div class="text-muted small">Tahap laporan tersedia setelah relawan ditugaskan (status "Relawan Ditugaskan").</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

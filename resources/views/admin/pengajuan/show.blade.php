@extends('layouts.relawan')

@section('title', 'Admin - Detail Pengajuan')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.pengajuan.index') }}" class="btn btn-light border btn-sm"><i class="bi bi-arrow-left"></i></a>
        <h3 class="mb-0">{{ $pengajuan->judul }}</h3>
        @include('pengajuan._status', ['status' => $pengajuan->status])
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @if(in_array($pengajuan->status, ['diajukan', 'dicari', 'ditugaskan']))
            <a href="{{ route('admin.pengajuan.assign_form', $pengajuan) }}" class="btn btn-sm btn-success"><i class="bi bi-person-plus me-1"></i>{{ $pengajuan->status === 'ditugaskan' ? 'Ganti Relawan' : 'Cari & Assign' }}</a>
        @endif
        @if(!in_array($pengajuan->status, ['selesai', 'ditolak']))
            <form action="{{ route('admin.pengajuan.reject', $pengajuan) }}" method="post" class="swal-confirm" data-confirm="Tolak pengajuan ini?">
                @csrf
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle me-1"></i>Tolak</button>
            </form>
        @endif
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-0"><h5 class="mb-0">Detail Kebutuhan</h5></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">Pengaju</dt>
                    <dd class="col-sm-8">{{ $pengajuan->user->name ?? '—' }} <span class="text-muted small">({{ $pengajuan->user->email ?? '' }})</span></dd>
                    <dt class="col-sm-4 text-muted">Bidang</dt><dd class="col-sm-8">{{ $pengajuan->bidang->nama ?? '—' }}</dd>
                    <dt class="col-sm-4 text-muted">Jumlah Relawan</dt><dd class="col-sm-8">{{ $pengajuan->jumlah_relawan }} orang</dd>
                    <dt class="col-sm-4 text-muted">Tanggal Kegiatan</dt><dd class="col-sm-8">{{ optional($pengajuan->tanggal_kegiatan)->format('d M Y') ?? '—' }}</dd>
                    <dt class="col-sm-4 text-muted">Lokasi</dt><dd class="col-sm-8">{{ $pengajuan->lokasi ?? '—' }}</dd>
                    <dt class="col-12 text-muted mt-2">Deskripsi</dt>
                    <dd class="col-12" style="white-space:pre-line">{{ $pengajuan->kebutuhan }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="bi bi-person-badge me-1"></i>Relawan Ditugaskan</h5></div>
            <div class="card-body">
                @if($pengajuan->relawan)
                    <div class="fw-semibold fs-5">{{ $pengajuan->relawan->nama }}</div>
                    <div class="text-muted small mb-2">{{ $pengajuan->relawan->bidang->nama ?? '' }}</div>
                    <ul class="list-unstyled mb-0 small">
                        @if($pengajuan->relawan->kontak)<li><i class="bi bi-telephone me-2"></i>{{ $pengajuan->relawan->kontak }}</li>@endif
                        @if($pengajuan->relawan->email)<li><i class="bi bi-envelope me-2"></i>{{ $pengajuan->relawan->email }}</li>@endif
                        @if($pengajuan->relawan->domisili)<li><i class="bi bi-geo-alt me-2"></i>{{ $pengajuan->relawan->domisili }}</li>@endif
                    </ul>
                @else
                    <div class="text-muted">Belum ditugaskan.</div>
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="bi bi-camera me-1"></i>Bukti Implementasi</h5></div>
            <div class="card-body">
                @if(!empty($pengajuan->bukti_implementasi['path']))
                    <a href="{{ asset('storage/'.$pengajuan->bukti_implementasi['path']) }}" target="_blank">
                        <img src="{{ asset('storage/'.$pengajuan->bukti_implementasi['path']) }}" alt="Bukti" class="img-fluid rounded border mb-2" style="max-height:260px;object-fit:cover;width:100%">
                    </a>
                    <div class="small text-muted mb-2">Diunggah: {{ $pengajuan->bukti_implementasi['uploaded_at'] ?? '-' }}</div>
                @else
                    <div class="text-muted small mb-2">Belum ada bukti yang diunggah.</div>
                @endif

                @if($pengajuan->status === 'selesai')
                    <form action="{{ route('admin.pengajuan.revisi', $pengajuan) }}" method="post" class="border-top pt-2">
                        @csrf
                        <label class="form-label small mb-1">Minta revisi bukti (opsional catatan)</label>
                        <textarea name="catatan_revisi" class="form-control form-control-sm mb-2" rows="2" placeholder="mis. Foto kurang jelas, mohon unggah ulang."></textarea>
                        <button class="btn btn-sm btn-outline-danger w-100"><i class="bi bi-arrow-counterclockwise me-1"></i>Minta Revisi</button>
                    </form>
                @elseif($pengajuan->status === 'revisi')
                    <div class="alert alert-warning py-2 small mb-2"><i class="bi bi-hourglass-split me-1"></i>Menunggu pengaju mengunggah ulang bukti.
                        @if($pengajuan->catatan_revisi)<div class="mt-1"><strong>Catatan:</strong> {{ $pengajuan->catatan_revisi }}</div>@endif
                    </div>
                    <form action="{{ route('admin.pengajuan.cancel_revision', $pengajuan) }}" method="post">
                        @csrf
                        <button class="btn btn-sm btn-light border w-100">Batalkan Permintaan Revisi</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

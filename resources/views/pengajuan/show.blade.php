@extends('layouts.relawan')

@section('title', 'Detail Pengajuan')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('pengajuan.index') }}" class="btn btn-light border btn-sm"><i class="bi bi-arrow-left"></i></a>
        <h3 class="mb-0">{{ $pengajuan->judul }}</h3>
        @include('pengajuan._status', ['status' => $pengajuan->status])
    </div>
    @if(in_array($pengajuan->status, ['diajukan', 'dicari']))
    <div class="d-flex gap-2">
        <a href="{{ route('pengajuan.edit', $pengajuan) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
        <form action="{{ route('pengajuan.destroy', $pengajuan) }}" method="post" class="swal-confirm" data-confirm="Batalkan pengajuan ini?">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg me-1"></i>Batalkan</button>
        </form>
    </div>
    @endif
</div>

<div class="row g-3">
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0"><h5 class="mb-0">Detail Kebutuhan</h5></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">Bidang</dt>
                    <dd class="col-sm-8">{{ $pengajuan->bidang->nama ?? '—' }}</dd>
                    <dt class="col-sm-4 text-muted">Jumlah Relawan</dt>
                    <dd class="col-sm-8">{{ $pengajuan->jumlah_relawan }} orang</dd>
                    <dt class="col-sm-4 text-muted">Tanggal Kegiatan</dt>
                    <dd class="col-sm-8">{{ optional($pengajuan->tanggal_kegiatan)->format('d M Y') ?? '—' }}</dd>
                    <dt class="col-sm-4 text-muted">Lokasi</dt>
                    <dd class="col-sm-8">{{ $pengajuan->lokasi ?? '—' }}</dd>
                    <dt class="col-sm-4 text-muted">Diajukan</dt>
                    <dd class="col-sm-8">{{ $pengajuan->created_at->format('d M Y H:i') }}</dd>
                    <dt class="col-12 text-muted mt-2">Deskripsi Kebutuhan</dt>
                    <dd class="col-12" style="white-space:pre-line">{{ $pengajuan->kebutuhan }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-5">
        {{-- Relawan yang ditugaskan --}}
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
                        @if($pengajuan->relawan->keahlian)<li class="mt-1"><i class="bi bi-stars me-2"></i>{{ $pengajuan->relawan->keahlian }}</li>@endif
                    </ul>
                @else
                    <div class="text-muted"><i class="bi bi-hourglass-split me-1"></i>Belum ada relawan yang ditugaskan. Admin sedang mencarikan relawan yang cocok.</div>
                @endif
            </div>
        </div>

        {{-- Bukti implementasi / aksi penyelesaian --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="bi bi-camera me-1"></i>Bukti Implementasi</h5></div>
            <div class="card-body">
                @if($pengajuan->status === 'ditugaskan')
                    <p class="text-muted small">Unggah foto bukti pelaksanaan untuk menandai pengajuan ini selesai (maks 5MB).</p>
                    <form action="{{ route('pengajuan.selesai', $pengajuan) }}" method="post" enctype="multipart/form-data" data-no-loading="1">
                        @csrf
                        <input type="file" name="bukti_file" accept="image/*" class="form-control mb-2" required>
                        <button class="btn btn-success w-100"><i class="bi bi-cloud-upload me-1"></i>Unggah & Selesaikan</button>
                    </form>
                @elseif($pengajuan->status === 'revisi')
                    <div class="alert alert-danger py-2">
                        <i class="bi bi-exclamation-triangle me-1"></i>Admin meminta revisi bukti.
                        @if($pengajuan->catatan_revisi)<div class="small mt-1"><strong>Catatan:</strong> {{ $pengajuan->catatan_revisi }}</div>@endif
                    </div>
                    <form action="{{ route('pengajuan.resubmit', $pengajuan) }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="bukti_file" accept="image/*" class="form-control mb-2" required>
                        <button class="btn btn-danger w-100"><i class="bi bi-arrow-repeat me-1"></i>Unggah Ulang</button>
                    </form>
                @elseif($pengajuan->status === 'selesai' && !empty($pengajuan->bukti_implementasi['path']))
                    <a href="{{ asset('storage/'.$pengajuan->bukti_implementasi['path']) }}" target="_blank">
                        <img src="{{ asset('storage/'.$pengajuan->bukti_implementasi['path']) }}" alt="Bukti implementasi" class="img-fluid rounded border mb-2" style="max-height:280px;object-fit:cover;width:100%">
                    </a>
                    <div class="small text-muted"><i class="bi bi-check-circle text-success me-1"></i>Selesai pada {{ optional($pengajuan->selesai_at)->format('d M Y H:i') }}</div>
                @else
                    <div class="text-muted small">Bukti implementasi dapat diunggah setelah relawan ditugaskan.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

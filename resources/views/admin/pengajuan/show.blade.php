@extends('layouts.relawan')

@section('title', 'Admin - Detail Pengajuan')

@section('content')
<div class="page-header mb-3">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
        <div class="d-flex align-items-center gap-2 page-header-meta">
            <a href="{{ route('admin.pengajuan.index') }}" class="btn-back"><i class="bi bi-arrow-left"></i></a>
            @include('pengajuan._status', ['status' => $pengajuan->status])
        </div>
        <div class="d-flex gap-2 flex-wrap head-actions">
            @if(in_array($pengajuan->status, ['disetujui', 'ditugaskan']))
                <a href="{{ route('admin.pengajuan.assign_form', $pengajuan) }}" class="btn btn-sm btn-success"><i class="bi bi-person-plus me-1"></i>Penugasan</a>
            @endif
            @if(!in_array($pengajuan->status, ['selesai', 'ditolak']))
                <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#rejectModal"><i class="bi bi-x-circle me-1"></i>Tolak</button>
            @endif
            <form action="{{ route('admin.pengajuan.destroy', $pengajuan) }}" method="post" class="swal-confirm" data-confirm="Hapus pengajuan ini secara permanen dari database?">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i>Hapus</button>
            </form>
        </div>
    </div>
    <h3 class="mb-0">{{ $pengajuan->judul }}</h3>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">@include('pengajuan._timeline')</div>
</div>

{{-- SOP Bagian 1: Review & Verifikasi --}}
@if($pengajuan->status === 'diajukan')
<div class="card shadow-sm mb-3 border-start border-4 border-primary">
    <div class="card-body">
        <h5 class="mb-1"><i class="bi bi-clipboard-check me-1"></i>Review &amp; Verifikasi</h5>
        <p class="text-muted small mb-3">Periksa kebutuhan &amp; anggaran. Setujui untuk lanjut ke penugasan, atau kembalikan untuk revisi.</p>
        <div class="d-flex gap-2 flex-wrap">
            <form action="{{ route('admin.pengajuan.approve', $pengajuan) }}" method="post" class="swal-confirm" data-confirm="Setujui pengajuan ini dan lanjut ke penugasan?" data-confirm-title="Setujui">
                @csrf
                <button class="btn btn-success"><i class="bi bi-check2-circle me-1"></i>Setujui</button>
            </form>
            <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#revisiModal"><i class="bi bi-arrow-counterclockwise me-1"></i>Minta Revisi</button>
        </div>
    </div>
</div>
@endif

<div class="row g-3">
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-0"><h5 class="mb-0">Informasi Kegiatan</h5></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">Pengaju</dt>
                    <dd class="col-sm-8">{{ $pengajuan->nama_pic ?? $pengajuan->user->name }} <span class="text-muted small">({{ $pengajuan->user->email ?? '' }})</span></dd>
                    <dt class="col-sm-4 text-muted">Direktorat / Divisi</dt>
                    <dd class="col-sm-8">{{ $pengajuan->direktorat ?? '—' }}{{ $pengajuan->divisi ? ' / '.$pengajuan->divisi : '' }}</dd>
                    <dt class="col-sm-4 text-muted">Waktu Pelaksanaan</dt>
                    <dd class="col-sm-8">{{ optional($pengajuan->waktu_mulai)->format('d M Y, H:i') ?? '—' }}@if($pengajuan->waktu_selesai) &ndash; {{ $pengajuan->waktu_selesai->format('d M Y, H:i') }}@endif</dd>
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
                        @if($k->isAssigned())<span class="badge badge-soft-success">{{ $k->assignedName() }}</span>@else<span class="badge badge-soft-warning">Belum diisi</span>@endif
                    </div>
                    @if($k->detail_tugas)<div class="small text-muted mt-1" style="white-space:pre-line">{{ $k->detail_tugas }}</div>@endif
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="bi bi-clipboard-check me-1"></i>Laporan &amp; Bukti</h5></div>
            <div class="card-body">
                @if(!empty($pengajuan->bukti_implementasi['path']))
                    <a href="{{ asset('storage/'.$pengajuan->bukti_implementasi['path']) }}" target="_blank">
                        <img src="{{ asset('storage/'.$pengajuan->bukti_implementasi['path']) }}" alt="Bukti" class="img-fluid rounded border mb-2" style="max-height:240px;object-fit:cover;width:100%">
                    </a>
                @endif
                @if($pengajuan->laporan)<div class="small mb-2" style="white-space:pre-line"><strong>Laporan:</strong><br>{{ $pengajuan->laporan }}</div>@endif

                @if($pengajuan->status === 'selesai')
                    <form action="{{ route('admin.pengajuan.revisi_laporan', $pengajuan) }}" method="post" class="border-top pt-2">
                        @csrf
                        <label class="form-label small mb-1">Minta revisi laporan (opsional catatan)</label>
                        <textarea name="catatan_revisi" class="form-control form-control-sm mb-2" rows="2" placeholder="mis. Foto kurang jelas, mohon unggah ulang."></textarea>
                        <button class="btn btn-sm btn-outline-danger w-100"><i class="bi bi-arrow-counterclockwise me-1"></i>Minta Revisi Laporan</button>
                    </form>
                @elseif($pengajuan->status === 'ditugaskan')
                    <div class="text-muted small"><i class="bi bi-hourglass-split me-1"></i>Menunggu pengaju mengunggah bukti &amp; laporan setelah deployment.
                        @if($pengajuan->catatan_revisi)<div class="mt-1 text-danger">Revisi diminta: {{ $pengajuan->catatan_revisi }}</div>@endif
                    </div>
                @else
                    <div class="text-muted small">Belum ada laporan.</div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('modals')
{{-- Modal: Minta Revisi (Bagian 1) --}}
<div class="modal fade" id="revisiModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.pengajuan.revisi', $pengajuan) }}" method="post">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Minta Revisi Pengajuan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <label class="form-label">Catatan untuk pengaju <span class="text-danger">*</span></label>
                    <textarea name="catatan_revisi" class="form-control" rows="3" required placeholder="Jelaskan apa yang perlu diperbaiki (kebutuhan/anggaran)."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Kirim Permintaan Revisi</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Tolak --}}
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.pengajuan.reject', $pengajuan) }}" method="post">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Tolak Pengajuan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <label class="form-label">Alasan penolakan (opsional)</label>
                    <textarea name="catatan_revisi" class="form-control" rows="3" placeholder="Alasan penolakan."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Tolak Pengajuan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush
@endsection

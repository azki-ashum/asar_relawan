@extends('layouts.relawan')

@section('title', 'Penugasan Relawan')

@section('content')
<div class="page-header mb-3">
    <div class="d-flex align-items-center gap-2 mb-2">
        <a href="{{ route('admin.pengajuan.show', $pengajuan) }}" class="btn-back"><i class="bi bi-arrow-left"></i></a>
        @include('pengajuan._status', ['status' => $pengajuan->status])
    </div>
    <h3 class="mb-0">Penugasan Relawan</h3>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <div class="row g-2 small">
            <div class="col-md-4"><span class="text-muted">Kegiatan:</span> <strong>{{ $pengajuan->judul }}</strong></div>
            <div class="col-md-4"><span class="text-muted">Pengaju:</span> {{ $pengajuan->nama_pic ?? $pengajuan->user->name }}</div>
            <div class="col-md-4"><span class="text-muted">Waktu:</span> {{ optional($pengajuan->waktu_mulai)->format('d M Y H:i') ?? '—' }}</div>
            <div class="col-md-8"><span class="text-muted">Lokasi:</span> {{ $pengajuan->lokasi ?? '—' }}</div>
            <div class="col-md-4"><span class="text-muted">Progres:</span> <strong>{{ $pengajuan->assignedCount() }}/{{ $pengajuan->kebutuhan->count() }}</strong> kebutuhan terisi</div>
        </div>
    </div>
</div>

@foreach($pengajuan->kebutuhan as $idx => $k)
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white border-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <span class="fw-semibold">#{{ $idx + 1 }} · {{ $k->jenisLabel() }}</span>
            <span class="badge badge-soft-secondary ms-1">{{ $k->jenisKelaminLabel() }}</span>
            @if($k->nominal_apresiasi)<span class="badge badge-soft-info ms-1">Rp {{ number_format($k->nominal_apresiasi, 0, ',', '.') }}</span>@endif
        </div>
        @if($k->isAssigned())<span class="badge badge-soft-success"><i class="bi bi-check-circle me-1"></i>Terisi</span>@else<span class="badge badge-soft-warning">Belum diisi</span>@endif
    </div>
    <div class="card-body">
        @if($k->detail_tugas)<div class="small text-muted mb-2"><i class="bi bi-list-task me-1"></i>{{ $k->detail_tugas }}</div>@endif

        @if($k->isAssigned())
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 bg-light rounded p-2">
                <div class="small">
                    <i class="bi bi-person-badge text-success me-1"></i><strong>{{ $k->assignedName() }}</strong>
                    @if($k->relawan_kontak) · {{ $k->relawan_kontak }}@endif
                    @if($k->relawan_domisili) · {{ $k->relawan_domisili }}@endif
                    @if(!$k->relawan_id)<span class="badge badge-soft-secondary ms-1">manual</span>@endif
                </div>
                <form action="{{ route('admin.pengajuan.kebutuhan.unassign', [$pengajuan, $k]) }}" method="post" class="swal-confirm" data-confirm="Batalkan penugasan relawan ini?">
                    @csrf
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg me-1"></i>Batalkan</button>
                </form>
            </div>
        @else
            <form action="{{ route('admin.pengajuan.kebutuhan.assign', [$pengajuan, $k]) }}" method="post" class="row g-2 align-items-end">
                @csrf
                <div class="col-12 col-md-8">
                    <label class="form-label small mb-1">Pilih relawan tersedia ({{ $k->jenisLabel() }})</label>
                    <select name="relawan_id" class="form-select form-select-sm">
                        <option value="">— pilih dari data relawan —</option>
                        @foreach($candidates[$k->id] as $cand)
                            <option value="{{ $cand->id }}">{{ $cand->nama }}@if($cand->domisili) — {{ $cand->domisili }}@endif @if($cand->jenis_kelamin) ({{ $cand->jenis_kelamin }})@endif</option>
                        @endforeach
                    </select>
                    @if($candidates[$k->id]->isEmpty())
                        <div class="form-text text-danger">Tidak ada relawan tersedia untuk jenis ini. Gunakan entri manual atau <a href="{{ route('admin.relawan.create') }}">tambah relawan</a>.</div>
                    @endif
                </div>
                <div class="col-12 col-md-4 d-grid">
                    <button class="btn btn-success btn-sm"><i class="bi bi-check-lg me-1"></i>Tugaskan</button>
                </div>
                <div class="col-12">
                    <a class="small text-decoration-none" data-bs-toggle="collapse" href="#manual{{ $k->id }}" role="button"><i class="bi bi-pencil-square me-1"></i>atau isi manual (Personal Volunteer Management)</a>
                    <div class="collapse mt-2" id="manual{{ $k->id }}">
                        <div class="row g-2">
                            <div class="col-md-4"><input type="text" name="relawan_nama" class="form-control form-control-sm" placeholder="Nama relawan"></div>
                            <div class="col-md-4"><input type="text" name="relawan_kontak" class="form-control form-control-sm" placeholder="No HP"></div>
                            <div class="col-md-4"><input type="text" name="relawan_domisili" class="form-control form-control-sm" placeholder="Domisili"></div>
                        </div>
                    </div>
                </div>
            </form>
        @endif
    </div>
</div>
@endforeach

@if($pengajuan->status === 'disetujui')
<div class="card border-0 shadow-sm">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="small text-muted">
            @if($pengajuan->allAssigned())
                <i class="bi bi-check-circle text-success me-1"></i>Semua kebutuhan sudah terisi. Tandai siap ditugaskan untuk memberi tahu pengaju.
            @else
                <i class="bi bi-info-circle me-1"></i>Isi semua kebutuhan relawan terlebih dahulu untuk dapat menandai siap ditugaskan.
            @endif
        </div>
        <form action="{{ route('admin.pengajuan.tugaskan', $pengajuan) }}" method="post" class="swal-confirm" data-confirm="Tandai relawan siap ditugaskan? Pengaju akan diberi tahu untuk deployment." data-confirm-title="Konfirmasi Penugasan">
            @csrf
            <button class="btn btn-primary" @disabled(!$pengajuan->allAssigned())><i class="bi bi-send-check me-1"></i>Tandai Siap Ditugaskan</button>
        </form>
    </div>
</div>
@endif
@endsection

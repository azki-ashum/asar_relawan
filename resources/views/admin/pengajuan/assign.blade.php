@extends('layouts.relawan')

@section('title', 'Cari & Assign Relawan')

@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('admin.pengajuan.index') }}" class="btn btn-light border btn-sm"><i class="bi bi-arrow-left"></i></a>
    <h3 class="mb-0">Cari &amp; Tugaskan Relawan</h3>
    @include('pengajuan._status', ['status' => $pengajuan->status])
</div>

<div class="row g-3">
    {{-- Ringkasan kebutuhan --}}
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0"><h5 class="mb-0">{{ $pengajuan->judul }}</h5></div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted">Pengaju</dt><dd class="col-7">{{ $pengajuan->user->name ?? '—' }}</dd>
                    <dt class="col-5 text-muted">Bidang</dt><dd class="col-7">{{ $pengajuan->bidang->nama ?? '—' }}</dd>
                    <dt class="col-5 text-muted">Jumlah</dt><dd class="col-7">{{ $pengajuan->jumlah_relawan }} orang</dd>
                    <dt class="col-5 text-muted">Tanggal</dt><dd class="col-7">{{ optional($pengajuan->tanggal_kegiatan)->format('d M Y') ?? '—' }}</dd>
                    <dt class="col-5 text-muted">Lokasi</dt><dd class="col-7">{{ $pengajuan->lokasi ?? '—' }}</dd>
                    <dt class="col-12 text-muted mt-2">Kebutuhan</dt>
                    <dd class="col-12" style="white-space:pre-line">{{ $pengajuan->kebutuhan }}</dd>
                </dl>
                @if($pengajuan->relawan)
                    <div class="alert alert-warning py-2 mt-2 mb-0 small">
                        <i class="bi bi-person-check me-1"></i>Saat ini ditugaskan ke <strong>{{ $pengajuan->relawan->nama }}</strong>. Memilih relawan lain akan menggantinya.
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Daftar relawan tersedia --}}
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="bi bi-people me-1"></i>Relawan Tersedia</h5></div>
            <div class="card-body">
                <form method="get" class="row g-2 mb-3">
                    <div class="col-12 col-md-6">
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Cari nama / keahlian / domisili...">
                    </div>
                    <div class="col-8 col-md-4">
                        <select name="bidang_relawan_id" class="form-select form-select-sm">
                            <option value="">Semua Bidang</option>
                            @foreach($bidangs as $b)
                                <option value="{{ $b->id }}" @selected((string)$filterBidang === (string)$b->id)>{{ $b->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-4 col-md-2 d-grid">
                        <button class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>Cari</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr>
                            <th class="text-muted">Nama</th>
                            <th class="text-muted">Bidang</th>
                            <th class="text-muted">Domisili</th>
                            <th class="text-muted">Keahlian</th>
                            <th class="text-muted text-end">Aksi</th>
                        </tr></thead>
                        <tbody>
                            @forelse($relawanTersedia as $r)
                            <tr>
                                <td class="fw-semibold">{{ $r->nama }}<div class="small text-muted">{{ $r->kontak }}</div></td>
                                <td>{{ $r->bidang->nama ?? '—' }}</td>
                                <td>{{ $r->domisili ?? '—' }}</td>
                                <td class="wrap small">{{ \Illuminate\Support\Str::limit($r->keahlian, 50) ?: '—' }}</td>
                                <td class="text-end">
                                    <form action="{{ route('admin.pengajuan.assign', $pengajuan) }}" method="post" class="swal-confirm" data-confirm="Tugaskan {{ $r->nama }} ke pengajuan ini?" data-confirm-title="Tugaskan Relawan">
                                        @csrf
                                        <input type="hidden" name="relawan_id" value="{{ $r->id }}">
                                        <button class="btn btn-sm btn-success"><i class="bi bi-check-lg me-1"></i>Tugaskan</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada relawan tersedia sesuai filter. Coba ubah bidang/kata kunci, atau <a href="{{ route('admin.relawan.create') }}">tambah relawan</a>.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($relawanTersedia->hasPages())
                <div class="card-footer bg-white border-0 d-flex justify-content-end">{{ $relawanTersedia->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection

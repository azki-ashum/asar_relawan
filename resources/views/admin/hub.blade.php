@extends('layouts.app')

@section('title', 'Admin Panel')

@section('content')
<div class="admin-hub py-2">

    <div class="d-flex align-items-center gap-3 mb-4">
        <div style="width:44px;height:44px;display:flex;align-items:center;justify-content:center;">
            <img src="{{ asset('images/logo-dark.png') }}" alt="ASAR" style="width:44px;height:44px;object-fit:contain;border-radius:10px;" onerror="this.onerror=null;this.src='{{ asset('images/logo-white.png') }}'">
        </div>
        <div>
            <h4 class="mb-0 fw-bold">Admin Panel</h4>
            <p class="text-muted mb-0 small">Pusat pengelolaan sistem SiBook</p>
        </div>
        <a href="{{ route('choose') }}" class="btn btn-sm btn-outline-secondary ms-auto d-flex align-items-center gap-1">
            <i class="bi bi-arrow-left-circle"></i> Kembali
        </a>
    </div>

    {{-- === SECTION: OVERVIEW === --}}
    <div class="section-label text-muted small fw-semibold mb-2 text-uppercase ls-1">Gambaran Umum</div>
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-md-4">
            <a href="{{ route('admin.dashboard') }}" class="hub-card card border-0 shadow-sm text-decoration-none text-dark h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="hub-icon bg-primary text-white">
                        <i class="bi bi-bar-chart-line-fill"></i>
                    </div>
                    <div>
                        <div class="fw-semibold">Dashboard Statistik</div>
                        <div class="small text-muted">Laporan booking ruangan &amp; kendaraan</div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- === SECTION: KELOLA ASET === --}}
    <div class="section-label text-muted small fw-semibold mb-2 text-uppercase ls-1">Kelola Aset</div>
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-md-4">
            <a href="{{ route('admin.rooms.index') }}" class="hub-card card border-0 shadow-sm text-decoration-none text-dark h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="hub-icon bg-indigo text-white">
                        <i class="bi bi-building-up"></i>
                    </div>
                    <div>
                        <div class="fw-semibold">Manage Ruangan</div>
                        <div class="small text-muted">Tambah, ubah, hapus data ruangan</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-sm-6 col-md-4">
            <a href="{{ route('admin.assets.index') }}" class="hub-card card border-0 shadow-sm text-decoration-none text-dark h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="hub-icon bg-success text-white">
                        <i class="bi bi-car-front-fill"></i>
                    </div>
                    <div>
                        <div class="fw-semibold">Manage Kendaraan</div>
                        <div class="small text-muted">Tambah, ubah, hapus data kendaraan</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-sm-6 col-md-4">
            <a href="{{ route('admin.asset_types.create') }}" class="hub-card card border-0 shadow-sm text-decoration-none text-dark h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="hub-icon bg-teal text-white">
                        <i class="bi bi-tags-fill"></i>
                    </div>
                    <div>
                        <div class="fw-semibold">Tipe Kendaraan</div>
                        <div class="small text-muted">Kelola jenis / kategori kendaraan</div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- === SECTION: KELOLA BOOKING === --}}
    <div class="section-label text-muted small fw-semibold mb-2 text-uppercase ls-1">Kelola Booking</div>
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-md-4">
            <a href="{{ route('admin.bookings.index') }}" class="hub-card card border-0 shadow-sm text-decoration-none text-dark h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="hub-icon bg-warning text-dark">
                        <i class="bi bi-calendar2-check-fill"></i>
                    </div>
                    <div>
                        <div class="fw-semibold">Booking Ruangan</div>
                        <div class="small text-muted">Lihat &amp; kelola semua booking ruangan</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-sm-6 col-md-4">
            <a href="{{ route('asset.admin.bookings.index') }}" class="hub-card card border-0 shadow-sm text-decoration-none text-dark h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="hub-icon bg-cyan text-dark">
                        <i class="bi bi-calendar2-week-fill"></i>
                    </div>
                    <div>
                        <div class="fw-semibold">Booking Kendaraan</div>
                        <div class="small text-muted">Lihat &amp; kelola semua booking kendaraan</div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- === SECTION: KELOLA PENGGUNA === --}}
    <div class="section-label text-muted small fw-semibold mb-2 text-uppercase ls-1">Kelola Pengguna</div>
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-md-4">
            <a href="{{ route('admin.users.index') }}" class="hub-card card border-0 shadow-sm text-decoration-none text-dark h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="hub-icon bg-secondary text-white">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <div class="fw-semibold">Manage Users</div>
                        <div class="small text-muted">Kelola akun &amp; role pengguna</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-sm-6 col-md-4">
            <a href="{{ route('admin.blocked_users.index') }}" class="hub-card card border-0 shadow-sm text-decoration-none text-dark h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="hub-icon bg-danger text-white">
                        <i class="bi bi-slash-circle-fill"></i>
                    </div>
                    <div>
                        <div class="fw-semibold">Blocked Users</div>
                        <div class="small text-muted">Daftar pengguna yang diblokir</div>
                    </div>
                </div>
            </a>
        </div>
    </div>

</div>
@endsection

@push('head')
<style>
    .admin-hub .section-label {
        letter-spacing: .06em;
        border-left: 3px solid #dee2e6;
        padding-left: .5rem;
    }
    .hub-card {
        border-radius: 12px !important;
        transition: transform .15s cubic-bezier(.2,.8,.2,1), box-shadow .15s;
    }
    .hub-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 40px rgba(22,28,37,0.10) !important;
    }
    .hub-icon {
        width: 46px;
        height: 46px;
        min-width: 46px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
    /* custom colors not in Bootstrap */
    .bg-indigo  { background-color: #6610f2 !important; }
    .bg-teal    { background-color: #20c997 !important; }
    .bg-cyan    { background-color: #0dcaf0 !important; }
</style>
@endpush

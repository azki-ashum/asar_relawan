<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Asar Relawan'))</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-white.png') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">

    <style>
        body.bg-light { background-color: #f6f7f9 !important; }
        .app-container { max-width: 1040px; }
        .table-responsive { -webkit-overflow-scrolling: touch; overflow-x: auto; }
        .table th, .table td { vertical-align: middle; }
        .table td.wrap { white-space: normal; word-break: break-word; }
        .main-actions { display:flex; gap:.5rem; align-items:center; flex-wrap:wrap; }

        /* badge-soft helpers used across the volunteer views */
        .badge-soft-success{ background:#d1e7dd; color:#0f5132; font-weight:600; }
        .badge-soft-warning{ background:#fff3cd; color:#664d03; font-weight:600; }
        .badge-soft-danger{ background:#f8d7da; color:#842029; font-weight:600; }
        .badge-soft-info{ background:#cff4fc; color:#055160; font-weight:600; }
        .badge-soft-secondary{ background:#e2e3e5; color:#41464b; font-weight:600; }
        .badge-status, .badge-soft-success, .badge-soft-warning, .badge-soft-danger, .badge-soft-info, .badge-soft-secondary {
            display:inline-flex; align-items:center; justify-content:center; gap:.25rem;
            padding:.35rem .6rem; border-radius:.5rem; line-height:1;
        }
        .brand-badge { font-size:.65rem; border-radius:6px; line-height:1; box-shadow:0 1px 3px rgba(0,0,0,.08); }
        .stat-card .display-6 { font-weight:700; }
        .card { border-radius: .9rem; }

        @media (max-width: 576px) {
            .navbar-brand { font-size: 1rem; }
            .table th, .table td { font-size: .88rem; padding: .45rem .5rem; }
            .btn-sm { padding: .25rem .5rem; }
            .card { margin-bottom: .5rem; }
        }
    </style>
    @stack('head')
</head>
<body class="bg-light">
    @php
        $u = auth()->user();
        $isAdmin = $u && str_starts_with($u->role ?? '', 'admin');
        $isAdminArea = $isAdmin && request()->is('admin*');
    @endphp

    @auth
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
        <div class="container app-container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2"
               href="{{ $isAdminArea ? route('admin.pengajuan.index') : route('relawan.dashboard') }}">
                <span>Asar Relawan</span>
                @if($isAdminArea)
                    <span class="badge bg-dark text-white p-1 px-2 brand-badge">Admin</span>
                @else
                    <span class="badge bg-success text-white p-1 px-2 brand-badge">Pengaju</span>
                @endif
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNav">
                @if($isAdminArea)
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.pengajuan.*') ? 'active fw-semibold' : '' }}" href="{{ route('admin.pengajuan.index') }}">
                            <i class="bi bi-inboxes me-1"></i>Pengajuan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.relawan.*') ? 'active fw-semibold' : '' }}" href="{{ route('admin.relawan.index') }}">
                            <i class="bi bi-people me-1"></i>Data Relawan
                        </a>
                    </li>
                    @if(($u->role ?? '') === 'admin')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active fw-semibold' : '' }}" href="{{ route('admin.users.index') }}">
                            <i class="bi bi-person-gear me-1"></i>Pengguna
                        </a>
                    </li>
                    @endif
                </ul>
                @else
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('relawan.dashboard') ? 'active fw-semibold' : '' }}" href="{{ route('relawan.dashboard') }}">
                            <i class="bi bi-house-door me-1"></i>Beranda
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('pengajuan.index') ? 'active fw-semibold' : '' }}" href="{{ route('pengajuan.index') }}">
                            <i class="bi bi-list-check me-1"></i>Pengajuan Saya
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('pengajuan.create') ? 'active fw-semibold' : '' }}" href="{{ route('pengajuan.create') }}">
                            <i class="bi bi-plus-circle me-1"></i>Buat Pengajuan
                        </a>
                    </li>
                </ul>
                @endif

                <ul class="navbar-nav ms-auto">
                    @if($isAdmin)
                        <li class="nav-item">
                            <a class="nav-link" href="{{ $isAdminArea ? route('relawan.dashboard') : route('admin.pengajuan.index') }}">
                                <i class="bi bi-arrow-left-right me-1"></i>{{ $isAdminArea ? 'Mode Pengaju' : 'Mode Admin' }}
                            </a>
                        </li>
                    @endif
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i>{{ $u->name ? \Illuminate\Support\Str::title($u->name) : $u->email }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                            <li><span class="dropdown-item-text small text-muted">{{ $u->email }}</span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="post" class="px-1">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    @endauth

    <main class="pb-5">
        <div class="container app-container mt-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Periksa kembali isian Anda:</strong>
                    <ul class="mb-0 mt-1">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Konfirmasi hapus / aksi berbahaya
            document.querySelectorAll('form.swal-confirm').forEach(function (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    Swal.fire({
                        title: form.dataset.confirmTitle || 'Konfirmasi',
                        text: form.dataset.confirm || 'Yakin melanjutkan?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#dc3545'
                    }).then(function (r) { if (r.isConfirmed) form.submit(); });
                });
            });
            // Flatpickr untuk input tanggal
            if (typeof flatpickr === 'function') {
                document.querySelectorAll('input.flatpickr-date').forEach(function (el) {
                    flatpickr(el, { dateFormat: 'Y-m-d', altInput: true, altFormat: 'd M Y', disableMobile: true,
                        defaultDate: el.value || null });
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>

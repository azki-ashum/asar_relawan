<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Asar Relawan'))</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-white.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">

    <style>
        :root {
            /* Brand & tokens */
            --brand:#16a34a; --brand-600:#16a34a; --brand-700:#15803d; --brand-800:#166534;
            --brand-50:#ecfdf3; --brand-100:#d1fadf;
            --ink:#0f172a; --ink-soft:#334155; --muted:#64748b; --faint:#94a3b8;
            --line:#e8ecf2; --line-soft:#f1f4f8; --surface:#ffffff; --bg:#f4f7f5;
            --radius-xl:20px; --radius-lg:16px; --radius:12px; --radius-sm:10px;
            --shadow-xs:0 1px 2px rgba(16,24,40,.05);
            --shadow-sm:0 1px 3px rgba(16,24,40,.04), 0 1px 2px rgba(16,24,40,.03);
            --shadow:0 6px 20px rgba(16,24,40,.06), 0 2px 6px rgba(16,24,40,.04);
            --shadow-lg:0 16px 40px rgba(16,24,40,.12);
            /* Bootstrap variable overrides */
            --bs-primary:#16a34a; --bs-primary-rgb:22,163,74;
            --bs-success:#16a34a; --bs-success-rgb:22,163,74;
            --bs-danger:#e5484d; --bs-danger-rgb:229,72,77;
            --bs-warning:#f59e0b; --bs-warning-rgb:245,158,11;
            --bs-info:#0ea5e9; --bs-info-rgb:14,165,233;
            --bs-body-color:#0f172a;
            --bs-body-font-family:'Plus Jakarta Sans', system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
            --bs-border-radius:12px; --bs-border-radius-sm:8px; --bs-border-radius-lg:16px;
        }

        html { scroll-behavior: smooth; }
        body { font-family: var(--bs-body-font-family); color: var(--ink); -webkit-font-smoothing:antialiased; letter-spacing:-.005em; }
        body.bg-light { background: var(--bg) !important; }
        h1,h2,h3,h4,h5,h6 { font-weight:700; letter-spacing:-.02em; color:var(--ink); }
        a { text-decoration: none; }
        .text-muted { color: var(--muted) !important; }

        /* ---- Layout helpers ---- */
        .table-responsive { -webkit-overflow-scrolling: touch; overflow-x: auto; }
        .table th, .table td { vertical-align: middle; }
        .table td.wrap { white-space: normal; word-break: break-word; }
        .main-actions { display:flex; gap:.5rem; align-items:center; flex-wrap:wrap; }

        /* ---- Navbar ---- */
        .navbar { background: rgba(255,255,255,.82)!important; backdrop-filter: saturate(180%) blur(10px);
            border-bottom:1px solid var(--line)!important; box-shadow: var(--shadow-xs)!important; padding-top:.6rem; padding-bottom:.6rem; }
        .brand-mark { width:34px; height:34px; border-radius:11px; display:inline-flex; align-items:center; justify-content:center;
            background:linear-gradient(135deg, var(--brand-600), var(--brand-800)); color:#fff; font-size:1.05rem; box-shadow:0 4px 10px rgba(22,163,74,.28); }
        .navbar-brand { font-size:1.12rem; font-weight:800; letter-spacing:-.02em; color:var(--ink); }
        .brand-badge { font-size:.62rem; font-weight:700; letter-spacing:.03em; text-transform:uppercase;
            border-radius:999px; padding:.2rem .55rem; line-height:1; }
        .navbar .nav-link { border-radius:999px; padding:.45rem .9rem!important; color:var(--muted); font-weight:600; font-size:.92rem;
            display:inline-flex; align-items:center; transition:background .15s ease, color .15s ease; }
        .navbar .nav-link:hover { background:var(--line-soft); color:var(--ink); }
        .navbar .nav-link.active { background:var(--brand-50); color:var(--brand-700)!important; }
        .navbar .dropdown-toggle::after { margin-left:.4rem; opacity:.5; }
        .avatar-chip { width:30px; height:30px; border-radius:50%; background:var(--brand-100); color:var(--brand-700);
            display:inline-flex; align-items:center; justify-content:center; font-weight:700; font-size:.82rem; }

        /* ---- Cards ---- */
        .card { border-radius: var(--radius-lg); border:1px solid var(--line); box-shadow: var(--shadow-sm);
            transition: box-shadow .18s ease, transform .18s ease; }
        .card.shadow-sm:hover { box-shadow: var(--shadow) !important; }
        /* inset shadow supaya pembatas tetap tampil walau view memakai .border-0 */
        .card-header { background: transparent!important; border-bottom:1px solid var(--line); box-shadow: inset 0 -1px 0 var(--line); padding:1rem 1.25rem; font-weight:700; }
        .card-header.border-0 { box-shadow: inset 0 -1px 0 var(--line); }
        .card-header h5 { font-size:1.02rem; margin:0; }
        .card-body { padding:1.25rem; }
        .card-footer { background:transparent!important; border-top:1px solid var(--line); }

        /* ---- Buttons ---- */
        .btn { border-radius: var(--radius-sm); font-weight:600; padding:.55rem 1rem; transition: all .15s ease; }
        .btn-sm { border-radius:9px; padding:.35rem .7rem; font-size:.85rem; }
        .btn-lg { border-radius: var(--radius); }
        .btn-success, .btn-primary { border:none; box-shadow:0 2px 6px rgba(22,163,74,.22); }
        .btn-success:hover, .btn-primary:hover { transform: translateY(-1px); box-shadow:0 6px 16px rgba(22,163,74,.28); }
        .btn-danger { border:none; }
        .btn-light { background:#fff; border:1px solid var(--line); color:var(--ink-soft); }
        .btn-light:hover { background:var(--line-soft); border-color:var(--line); }
        .btn-outline-primary { color:var(--brand-700); border-color:var(--brand-600); }
        .btn-outline-primary:hover { background:var(--brand-600); border-color:var(--brand-600); }
        .btn-link { color:var(--brand-700); font-weight:600; }

        /* ---- Forms ---- */
        .form-label { font-weight:600; font-size:.85rem; color:var(--ink-soft); margin-bottom:.35rem; }
        .form-control, .form-select { border-radius: var(--radius-sm); border-color:var(--line); padding:.6rem .85rem; color:var(--ink);
            background-color:#fff; transition: border-color .15s ease, box-shadow .15s ease; }
        .form-control-sm, .form-select-sm { border-radius:9px; padding:.4rem .7rem; }
        .form-control::placeholder { color:#aab4c2; }
        .form-control:focus, .form-select:focus { border-color:var(--brand-600); box-shadow:0 0 0 .22rem rgba(22,163,74,.14); }
        .input-group-text { background:#fff; border-color:var(--line); color:var(--faint); border-radius:var(--radius-sm); }

        /* ---- Tables ---- */
        .table { --bs-table-hover-bg:#f5f9f6; margin-bottom:0; }
        .table thead th { font-size:.72rem; text-transform:uppercase; letter-spacing:.045em; color:var(--faint);
            font-weight:700; border-bottom:1px solid var(--line); padding:.8rem 1rem; white-space:nowrap; }
        .table tbody td { padding:.85rem 1rem; border-color:var(--line-soft); color:var(--ink-soft); }
        .table tbody tr:last-child td { border-bottom:0; }
        .table-hover tbody tr { transition: background-color .12s ease; }

        /* ---- Badges / status pills ---- */
        .badge { font-weight:600; }
        .badge-soft-success{ background:#dcfce7; color:#15803d; }
        .badge-soft-warning{ background:#fef3c7; color:#b45309; }
        .badge-soft-danger{ background:#fee2e2; color:#b91c1c; }
        .badge-soft-info{ background:#e0f2fe; color:#0369a1; }
        .badge-soft-secondary{ background:#eef1f6; color:#475569; }
        .badge-status, .badge-soft-success, .badge-soft-warning, .badge-soft-danger, .badge-soft-info, .badge-soft-secondary {
            display:inline-flex; align-items:center; justify-content:center; gap:.3rem;
            padding:.38rem .7rem; border-radius:999px; line-height:1; font-size:.78rem; font-weight:600; }

        /* ---- Stat cards ---- */
        .stat-card .card-body { padding:1.15rem 1.25rem; }
        .stat-card .bi { font-size:1.1rem; width:40px; height:40px; display:inline-flex; align-items:center; justify-content:center;
            border-radius:12px; background:var(--line-soft); }
        .stat-card .bi.text-primary{ background:#eef2ff; }
        .stat-card .bi.text-info{ background:#e0f2fe; }
        .stat-card .bi.text-warning{ background:#fef3c7; }
        .stat-card .bi.text-success{ background:#dcfce7; }
        .stat-card .display-6 { font-weight:800; font-size:2.1rem; letter-spacing:-.03em; margin-top:.4rem; line-height:1.1; }

        /* ---- Alerts (toast-like) ---- */
        .alert { border:1px solid transparent; border-radius: var(--radius); box-shadow: var(--shadow-sm); font-size:.92rem; padding:.85rem 1.05rem; }
        .alert-success { background:#ecfdf3; border-color:#a6f4c5; color:#067647; }
        .alert-danger { background:#fef3f2; border-color:#fecdca; color:#b42318; }
        .alert-warning { background:#fffaeb; border-color:#fedf89; color:#b54708; }
        .alert-info { background:#eff8ff; border-color:#b2ddff; color:#175cd3; }
        .alert-secondary { background:#f8fafc; border-color:var(--line); color:var(--ink-soft); }

        /* ---- Global Icon & Alignment Fixes ---- */
        .bi {
            line-height: 1;
            vertical-align: -0.125em;
        }
        .step-circle {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        .step-circle .bi {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            line-height: 1 !important;
            margin: 0 !important;
            vertical-align: 0 !important;
        }
        .step-circle .bi-send {
            transform: translate(-0.5px, 0.5px);
        }
        .alert .bi {
            line-height: 1;
            vertical-align: middle;
        }
        .btn .bi, .badge .bi, .nav-link .bi {
            line-height: 1;
            vertical-align: -0.1em;
        }

        /* ---- Dropdown / modal ---- */
        .dropdown-menu { border-radius:14px; border:1px solid var(--line); box-shadow: var(--shadow-lg); padding:.4rem; }
        .dropdown-item { border-radius:9px; padding:.5rem .7rem; font-weight:500; }
        .dropdown-item:active { background: var(--brand-600); }
        .modal-content { border-radius: var(--radius-xl); border:none; box-shadow: var(--shadow-lg); }
        .modal-header, .modal-footer { border-color: var(--line); padding:1.1rem 1.35rem; }
        .modal-body { padding:1.1rem 1.35rem; }

        /* ---- Misc ---- */
        .brand-mark, .avatar-chip { flex-shrink:0; }
        .empty-state { text-align:center; padding:2.75rem 1rem; color:var(--muted); }
        .empty-state .bi { font-size:2.2rem; color:var(--faint); display:block; margin-bottom:.5rem; }

        /* Tombol kembali bulat + header halaman detail */
        .btn-back { width:36px; height:36px; padding:0; border-radius:50%; flex-shrink:0;
            display:inline-flex; align-items:center; justify-content:center; background:#fff; border:1px solid var(--line); color:var(--ink-soft); }
        .btn-back:hover { background:var(--line-soft); color:var(--ink); }
        .page-header-meta { min-width:0; } /* izinkan flex child menyusut agar badge tak mendorong overflow */
        .page-header h3 { font-size:1.4rem; line-height:1.3; }

        /* Hanya animasikan opacity agar #page-content tidak membentuk containing-block baru
           yang mengacaukan position:fixed milik modal Bootstrap. */
        #page-content { animation: fadeIn .3s ease both; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            #page-content, .card, .btn { animation: none !important; transition: none !important; }
        }

        /* ================= RESPONSIVE / MOBILE ================= */
        /* Navbar (collapsed di bawah lg = 992px) */
        @media (max-width: 991.98px) {
            .navbar .navbar-collapse { margin-top:.5rem; padding-top:.5rem; border-top:1px solid var(--line); }
            .navbar-nav { gap:.1rem; }
            .navbar .nav-link { padding:.6rem .9rem!important; border-radius:999px; }
            .navbar .dropdown-menu { box-shadow:none; border:1px solid var(--line); margin-top:.25rem; }
            .navbar-nav.ms-auto { margin-top:.35rem; padding-top:.35rem; border-top:1px dashed var(--line); }
        }

        /* Tabel menumpuk jadi kartu di layar kecil */
        @media (max-width: 767.98px) {
            .table-stack, .table-stack tbody, .table-stack tr, .table-stack td { display:block; width:100%; }
            .table-stack thead { display:none; }
            .table-stack { background:transparent; }
            .table-stack tbody tr {
                border:1px solid var(--line); border-radius:16px; margin:.15rem .35rem 1rem;
                background:#fff; box-shadow:var(--shadow-sm); overflow:hidden;
                transition:transform .12s ease;
            }
            .table-stack tbody tr:active { transform:scale(.99); }
            /* baris label–nilai */
            .table-stack tbody td {
                display:flex; justify-content:space-between; align-items:center; gap:1rem;
                padding:.72rem 1.05rem !important; border:0 !important;
                text-align:right; color:var(--ink); font-weight:600; font-size:.92rem;
            }
            .table-stack tbody td + td:not(.cell-actions) { border-top:1px solid var(--line-soft) !important; }
            .table-stack tbody td[data-label]::before {
                content:attr(data-label); font-weight:600; color:var(--faint);
                font-size:.7rem; text-transform:uppercase; letter-spacing:.04em;
                text-align:left; flex:0 0 auto; align-self:center;
            }
            /* judul kartu */
            .table-stack tbody td.cell-title {
                display:block; text-align:left; padding:.95rem 1.05rem .8rem !important;
                font-weight:700; font-size:1.06rem; line-height:1.3; color:var(--ink);
                border-bottom:1px solid var(--line) !important;
            }
            .table-stack tbody td.cell-title::before { display:none; }
            .table-stack tbody td.cell-title .small { font-weight:500; }
            /* footer aksi: tombol full-width */
            .table-stack tbody td.cell-actions {
                display:block; text-align:center; padding:.8rem 1.05rem !important;
                background:#fafbfc; border-top:1px solid var(--line) !important;
            }
            .table-stack tbody td.cell-actions::before { display:none; }
            .table-stack tbody td.cell-actions .btn { width:100%; }
            .table-stack tbody td.cell-actions .d-flex { display:flex !important; gap:.5rem; justify-content:stretch !important; }
            .table-stack tbody td.cell-actions .d-flex > * { flex:1 1 0; }
            .table-stack tbody td.wrap { white-space:normal; }
            /* baris kosong tetap satu blok polos */
            .table-stack tbody tr.no-card { border:0; box-shadow:none; background:transparent; margin:0; }
            .table-stack tbody tr.no-card td { display:block; text-align:center; }
            .table-stack tbody tr.no-card td::before { display:none; }
        }

        @media (max-width: 575.98px) {
            .navbar-brand { font-size: 1rem; }
            .table:not(.table-stack) th, .table:not(.table-stack) td { font-size:.86rem; padding:.6rem .55rem; }
            .btn-sm { padding:.32rem .6rem; }
            .card { margin-bottom:.5rem; }
            .stat-card .display-6 { font-size:1.7rem; }
            .card-body { padding:1rem; }
            h3 { font-size:1.35rem; }
            .modal-dialog:not(.modal-dialog-centered) { margin:.6rem; }
            /* CTA utama pada header halaman jadi full-width */
            .head-actions { width:100%; }
            .head-actions > .btn, .head-actions > form, .head-actions > form > .btn { width:100%; }
            .main-actions { width:100%; }
            .main-actions > .btn, .main-actions > button { flex:1 1 auto; }
            .hero-card .card-body { padding:1.25rem 1.25rem!important; }
            .hero-card .btn { width:100%; }
            /* Detail: badge status & tombol aksi header tak berdesakan */
            dl.row dt { margin-bottom:.1rem; }
            dl.row dd { margin-bottom:.6rem; }
            /* Timeline SOP: sedikit lebih ringkas di layar sempit */
            .sop-timeline .step-circle { width:34px!important; height:34px!important; font-size:.8rem!important; }
            .sop-timeline .step-label { font-size:.68rem!important; }
            .sop-timeline .step-line { top:16px!important; }
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
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2"
               href="{{ $isAdminArea ? route('admin.pengajuan.index') : route('relawan.dashboard') }}">
                <span class="brand-mark"><i class="bi bi-heart-fill"></i></span>
                <span class="d-flex align-items-center gap-2">
                    Asar Relawan
                    @if($isAdminArea)
                        <span class="brand-badge" style="background:#1e293b;color:#fff;">Admin</span>
                    @else
                        <span class="brand-badge" style="background:var(--brand-100);color:var(--brand-800);">Pengaju</span>
                    @endif
                </span>
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

                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                    @if($isAdmin)
                        <li class="nav-item">
                            <a class="nav-link" href="{{ $isAdminArea ? route('relawan.dashboard') : route('admin.pengajuan.index') }}">
                                <i class="bi bi-arrow-left-right me-1"></i>{{ $isAdminArea ? 'Mode Pengaju' : 'Mode Admin' }}
                            </a>
                        </li>
                    @endif
                    @php $initial = \Illuminate\Support\Str::of($u->name ?: $u->email)->trim()->substr(0,1)->upper(); @endphp
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" id="userMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="avatar-chip">{{ $initial }}</span>
                            <span class="text-truncate" style="max-width:160px;">{{ $u->name ? \Illuminate\Support\Str::title($u->name) : $u->email }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu" style="min-width:220px;">
                            <li class="px-2 py-1">
                                <div class="fw-semibold text-truncate">{{ $u->name ? \Illuminate\Support\Str::title($u->name) : '—' }}</div>
                                <div class="small text-muted text-truncate">{{ $u->email }}</div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="post">
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

    <main id="page-content" class="pb-5">
        <div class="container mt-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-start gap-2" role="alert">
                    <i class="bi bi-check-circle-fill mt-1"></i><div>{{ session('success') }}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-start gap-2" role="alert">
                    <i class="bi bi-exclamation-triangle-fill mt-1"></i><div>{{ session('error') }}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-start gap-2" role="alert">
                    <i class="bi bi-exclamation-octagon-fill mt-1"></i>
                    <div>
                        <strong>Periksa kembali isian Anda:</strong>
                        <ul class="mb-0 mt-1">
                            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                        </ul>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    {{-- Modal Bootstrap didorong ke sini (bukan di dalam #page-content) agar tidak terpengaruh
         animasi/transform ancestor manapun yang bisa mengacaukan position:fixed miliknya. --}}
    @stack('modals')

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
                    }).then(function (r) {
                        if (r.isConfirmed) {
                            if (window.__appLoading) window.__appLoading.show();
                            form.submit();
                        }
                    });
                });
            });
            // Flatpickr untuk input tanggal
            if (typeof flatpickr === 'function') {
                document.querySelectorAll('input.flatpickr-date').forEach(function (el) {
                    flatpickr(el, { dateFormat: 'Y-m-d', altInput: true, altFormat: 'd M Y', disableMobile: true,
                        defaultDate: el.value || null });
                });
            }

            // Auto-dismiss notifikasi sukses (biarkan daftar error validasi tetap tampil)
            document.querySelectorAll('.alert-success, .alert-danger').forEach(function (el) {
                if (el.querySelector('ul')) return;
                setTimeout(function () {
                    try { bootstrap.Alert.getOrCreateInstance(el).close(); } catch (e) { el.style.display = 'none'; }
                }, 4500);
            });

            // Pratinjau gambar sebelum diunggah
            document.querySelectorAll('input[type=file][accept*="image"]').forEach(function (input) {
                input.addEventListener('change', function () {
                    var file = input.files && input.files[0];
                    if (!file) return;
                    var prev = input.parentNode.querySelector('img.file-preview');
                    if (!prev) {
                        prev = document.createElement('img');
                        prev.className = 'file-preview img-fluid rounded border mt-2 mb-2 d-block';
                        prev.style.maxHeight = '220px';
                        input.parentNode.insertBefore(prev, input.nextSibling);
                    }
                    prev.src = URL.createObjectURL(file);
                });
            });
        });
    </script>
    {{-- Overlay loading global agar navigasi & submit terasa mulus --}}
    <div id="global-loading-overlay" class="d-none" aria-hidden="true"
         style="position:fixed;inset:0;z-index:1035;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,0.7);backdrop-filter:blur(2px);">
        <div class="text-center">
            <div class="spinner-border text-success" role="status" style="width:3rem;height:3rem;">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="mt-2 text-muted">Memuat...</div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var overlay = document.getElementById('global-loading-overlay');
            if (!overlay) return;
            var lastShown = 0;
            function showLoading() {
                overlay.classList.remove('d-none');
                overlay.setAttribute('aria-hidden', 'false');
                lastShown = Date.now();
                setTimeout(function () { if (Date.now() - lastShown > 5000) hideLoading(); }, 6000);
            }
            function hideLoading() {
                overlay.classList.add('d-none');
                overlay.setAttribute('aria-hidden', 'true');
            }
            function modalIsOpen() { return !!document.querySelector('.modal.show'); }
            window.__appLoading = { show: showLoading, hide: hideLoading };
            // Tampilkan saat submit form biasa (non-AJAX), kecuali yang opt-out / konfirmasi swal
            document.querySelectorAll('form').forEach(function (f) {
                if (f.dataset && f.dataset.noLoading === '1') return;
                if (f.classList.contains('swal-confirm')) return; // overlay muncul setelah user klik "Ya"
                f.addEventListener('submit', function (e) {
                    if (e.defaultPrevented) return;
                    showLoading();
                });
            });
            // Form konfirmasi swal: tampilkan overlay tepat sebelum benar-benar submit
            document.querySelectorAll('form.swal-confirm').forEach(function (f) {
                f.addEventListener('submit', function () { /* handled by swal then real submit */ });
            });
            // Tampilkan saat klik link internal (pindah halaman) — kecuali sedang ada modal terbuka
            document.addEventListener('click', function (e) {
                var a = e.target.closest('a');
                if (!a) return;
                if (a.target === '_blank' || a.hasAttribute('download')) return;
                var href = a.getAttribute('href') || '';
                if (!href || href.startsWith('#')) return;
                if (/^(mailto:|tel:|javascript:)/i.test(href)) return;
                if (e.metaKey || e.ctrlKey || e.shiftKey) return;
                if (a.dataset && a.dataset.noLoading === '1') return;
                if (modalIsOpen()) return;
                showLoading();
            }, { capture: true });
            // Jaring pengaman: overlay tidak boleh pernah menutupi modal yang sedang terbuka
            document.addEventListener('show.bs.modal', hideLoading);
            document.addEventListener('shown.bs.modal', hideLoading);
            // Sembunyikan bila halaman dikembalikan dari cache (tombol Back)
            window.addEventListener('pageshow', function () { hideLoading(); });
        });
    </script>
    @stack('scripts')
</body>
</html>

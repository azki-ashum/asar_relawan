<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', config('app.name', 'Laravel'))</title>
    <!-- Favicon: use custom logo (PNG) with ICO fallback -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo-white.png') }}">
    {{-- <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" /> --}}

    <!-- Bootstrap 5 (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- FullCalendar CSS (used by dashboard widgets) -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.7/index.global.min.css" rel="stylesheet">

    <!-- Flatpickr CSS -->
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">

    <!-- App CSS (kept for existing project styles) -->
    {{-- <link rel="stylesheet" href="{{ asset('css/app.css') }}"> --}}

    <!-- Small layout helpers to improve mobile/tablet rendering -->
    <style>
        /* page background and central container width */
        body.bg-light { background-color: #f6f7f9 !important; }
        .app-container { max-width: 980px; }

        /* make tables horizontally scrollable and touch friendly */
        .table-responsive { -webkit-overflow-scrolling: touch; overflow-x: auto; }

        /* tighter table cell spacing and nicer wrapping for long titles */
        .table th, .table td { vertical-align: middle; white-space: nowrap; }
        .table td.wrap { white-space: normal; word-break: break-word; max-width: 220px; }

        /* compact badges for status labels */
        .badge-status { font-weight: 600; padding: .35rem .6rem; border-radius: .375rem; }

        /* Center all badges' content vertically and horizontally */
        .badge { display: inline-flex; align-items: center; justify-content: center; line-height: 1; }
        .badge .bi { line-height: 1; }

        /* header action alignment */
        .main-actions { display:flex; gap:.5rem; align-items:center; flex-wrap:wrap; }

        @media (max-width: 576px) {
            .navbar-brand { font-size: 1rem; }
            .table th, .table td { font-size: .88rem; padding: .45rem .5rem; }
            .btn-sm { padding: .25rem .5rem; }
            .card { margin-bottom: .5rem; }
        }
    </style>

    @stack('head')
</head>
<body>
    {{-- Navigation (hidden on login page, root path, or choose page) --}}
    @unless(request()->routeIs('login') || request()->is('/') || request()->routeIs('choose'))
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            @php
                // Admin area: only super-admin (role === 'admin') on admin-scoped URLs
                $isSuperAdmin = auth()->check() && auth()->user()->role === 'admin';
                $isAdminArea  = $isSuperAdmin && (
                                    request()->is('admin*')
                                 || request()->is('room/admin*')
                                 || request()->is('asset/admin*')
                                );
                // Asset area: only non-admin /asset/* pages (dashboard, bookings, etc.)
                $isAssetArea = !$isAdminArea && request()->is('asset*');

                // --- admin area vars ---
                $adminHubActive = request()->routeIs('admin.hub');

                // --- room / asset area vars (unchanged logic) ---
                $dashboardUrl = $isAssetArea ? url('/asset/dashboard') : route('dashboard');
                $bookingsUrl = $isAssetArea ? url('/asset/bookings') : route('bookings.index');
                $adminRoomsUrl = $isAssetArea ? url('/asset/admin/assets') : route('admin.rooms.index');
                $adminRoomsLabel = $isAssetArea ? 'Manage Kendaraan' : 'Manage Ruangan';
                $adminBookingsUrl = $isAssetArea ? url('/asset/admin/bookings') : route('admin.bookings.index');
                $dashboardActive = ($isAssetArea && request()->is('asset/dashboard*')) || request()->routeIs('dashboard');
                $bookingsActive = ($isAssetArea && request()->is('asset/bookings*')) || request()->routeIs('bookings.*');
            @endphp

            @if($isAdminArea)
                {{-- === ADMIN AREA BRAND === --}}
                <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('admin.hub') }}">
                    <span>SiBook</span>
                    <span class="badge bg-dark text-white p-1 px-2 d-inline-block" style="font-size:0.65rem; border-radius:6px; line-height:1; box-shadow:0 1px 3px rgba(0,0,0,0.08);">Admin</span>
                </a>
            @else
                {{-- === ROOM / ASSET AREA BRAND === --}}
                <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ $dashboardUrl }}">
                    <span>SiBook</span>
                    @php
                        $areaLabel = $isAssetArea ? 'Kendaraan' : 'Ruangan';
                        $areaBadgeClass = $isAssetArea ? 'bg-success text-white' : 'bg-primary text-white';
                    @endphp
                    <span class="badge {{ $areaBadgeClass }} p-1 px-2 d-inline-block" style="font-size:0.65rem; border-radius:6px; line-height:1; box-shadow:0 1px 3px rgba(0,0,0,0.08);" title="Current area: {{ $areaLabel }}" aria-label="Current area: {{ $areaLabel }}">{{ $areaLabel }}</span>
                </a>
            @endif

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">

                @if($isAdminArea)
                {{-- === ADMIN AREA NAV LINKS === --}}
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ (request()->is('admin/users*') || request()->is('admin/blocked*') || request()->is('room/admin*') || request()->is('asset/admin*')) ? 'active' : '' }}"
                           href="#" id="adminKelola" role="button" data-bs-toggle="dropdown" aria-expanded="false">Kelola</a>
                        <ul class="dropdown-menu" aria-labelledby="adminKelola">
                            <li><h6 class="dropdown-header">Aset</h6></li>
                            <li><a class="dropdown-item {{ request()->routeIs('admin.rooms.*') ? 'active' : '' }}" href="{{ route('admin.rooms.index') }}"><i class="bi bi-building-up me-2 text-primary"></i>Manage Ruangan</a></li>
                            <li><a class="dropdown-item {{ request()->routeIs('admin.assets.*') ? 'active' : '' }}" href="{{ route('admin.assets.index') }}"><i class="bi bi-car-front-fill me-2 text-success"></i>Manage Kendaraan</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header">Booking</h6></li>
                            <li><a class="dropdown-item {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}" href="{{ route('admin.bookings.index') }}"><i class="bi bi-calendar2-check-fill me-2 text-warning"></i>Booking Ruangan</a></li>
                            <li><a class="dropdown-item {{ request()->routeIs('asset.admin.bookings.*') ? 'active' : '' }}" href="{{ route('asset.admin.bookings.index') }}"><i class="bi bi-calendar2-week-fill me-2 text-info"></i>Booking Kendaraan</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header">Pengguna</h6></li>
                            <li><a class="dropdown-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}"><i class="bi bi-people-fill me-2 text-secondary"></i>Manage Users</a></li>
                            <li><a class="dropdown-item {{ request()->routeIs('admin.blocked_users.*') ? 'active' : '' }}" href="{{ route('admin.blocked_users.index') }}"><i class="bi bi-slash-circle-fill me-2 text-danger"></i>Blocked Users</a></li>
                        </ul>
                    </li>
                </ul>

                @else
                {{-- === ROOM / ASSET AREA NAV LINKS === --}}
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ $dashboardActive ? 'active' : '' }}" href="{{ $dashboardUrl }}">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $bookingsActive ? 'active' : '' }}" href="{{ $bookingsUrl }}">Bookings</a>
                    </li>
                </ul>
                @endif

                <ul class="navbar-nav ms-auto">
                    @guest
                        <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
                    @else
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">{{ Auth::user()->name ? Str::title(Auth::user()->name) : Auth::user()->email }}</a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                                {{-- Switch dashboard (go to choose page where user can pick a dashboard) --}}
                                <li><a class="dropdown-item" href="{{ route('choose') }}">Switch Dashboard</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form id="logout-form" action="{{ route('logout') }}" method="post" class="px-3 py-1">
                                        @csrf
                                        <button type="submit" class="btn btn-link text-decoration-none">Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>
    @endunless

    @if(request()->routeIs('login') || request()->is('/'))
        {{-- Login/root page: render content without extra container/padding --}}
        @yield('content')
    @else
        <main class="pb-4">
            <div class="container mt-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    @endif

    <!-- Bootstrap Bundle with Popper (CDN) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

    <!-- App JS (kept for existing project scripts) -->
    {{-- <script src="{{ asset('js/app.js') }}" defer></script> --}}

    <!-- SweetAlert2 (for nicer confirm dialogs) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- FullCalendar JS (used by dashboard widgets) -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.7/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.7/locales-all.global.min.js"></script>

    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- Dashboard widgets script (calendar, rooms list) -->
    <script src="{{ asset('js/dashboard-widgets.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('form.swal-confirm').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    var msg = form.dataset.confirm || form.dataset.confirmMessage || 'Yakin?';
                    var title = form.dataset.confirmTitle || 'Konfirmasi';
                    Swal.fire({
                        title: title,
                        text: msg,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya',
                        cancelButtonText: 'Batal'
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            // submit the form normally
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>

    <script>
        // Initialize flatpickr on all inputs with .flatpickr-date
        // For compact filter inputs we avoid altInput (it injects an extra input and can shift layout).
        document.addEventListener('DOMContentLoaded', function(){
            if (typeof flatpickr !== 'function') return;
            document.querySelectorAll('input.flatpickr-date').forEach(function(el){
                // If already initialized (data-has-flatpickr) skip
                if (el.dataset.hasFlatpickr) return;
                var opts = {
                    dateFormat: 'd-m-Y', // value submitted to server (ISO)
                    altInput: false,
                    disableMobile: true
                };
                // preserve existing value as defaultDate to avoid reflow when flatpickr mounts
                if (el.value && el.value.length) opts.defaultDate = el.value;
                flatpickr(el, opts);
                el.dataset.hasFlatpickr = '1';
            });
        });
    </script>

    <!-- Global loading overlay used across the app -->
    <div id="global-loading-overlay" class="d-none" aria-hidden="true" style="position:fixed;inset:0;z-index:2500;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,0.7);backdrop-filter:blur(2px);">
        <div class="text-center">
            <div class="spinner-border text-primary" role="status" style="width:3rem;height:3rem;">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="mt-2">Memuat...</div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function(){
            var overlay = document.getElementById('global-loading-overlay');
            if (!overlay) return;
            function showLoading(){ overlay.classList.remove('d-none'); overlay.setAttribute('aria-hidden', 'false'); }
            function hideLoading(){ overlay.classList.add('d-none'); overlay.setAttribute('aria-hidden', 'true'); }

            // Show overlay for normal form submissions (non-AJAX)
            document.querySelectorAll('form').forEach(function(f){
                // don't attach to forms that explicitly opt out
                if (f.dataset && f.dataset.noLoading === '1') return;
                f.addEventListener('submit', function(e){
                    if (e.defaultPrevented) return; // other handlers prevented submit
                    showLoading();
                });
            });

            // Show overlay for standard pagination links (bootstrap pagination)
            document.addEventListener('click', function(e){
                var a = e.target.closest('a');
                if (!a) return;
                // ignore external or special links
                if (a.target === '_blank' || a.href === undefined) return;
                var href = a.getAttribute('href') || '';
                if (!href || href.startsWith('#')) return;
                if (/^(mailto:|tel:|javascript:)/i.test(href)) return;
                // allow opening in new tab via modifier keys
                if (e.metaKey || e.ctrlKey) return;
                // if link is within a form control that uses confirmation, let that run; the eventual form.submit will trigger overlay
                showLoading();
            }, { capture: true });

            // Safety: hide overlay if stuck after 6s
            var lastShown = 0;
            var origShow = showLoading;
            showLoading = function(){ origShow(); lastShown = Date.now(); setTimeout(function(){ if (Date.now() - lastShown > 5000) hideLoading(); }, 6000); };
        });
    </script>

    @stack('scripts')
</body>
</html>

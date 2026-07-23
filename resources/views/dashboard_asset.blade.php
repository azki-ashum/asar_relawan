@extends('layouts.app')

@section('title', 'Dashboard Booking Kendaraan')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
             <h3 class="mb-0">Halo, {{ Auth::user() ? Str::title(Auth::user()->name) : 'User' }}</h3>
            <div>
                @if(Route::has('asset.bookings.create'))
                    <a href="{{ route('asset.bookings.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus"></i> Book
                    </a>
                @else
                    <a href="#" class="btn btn-primary">Buat Reservasi</a>
                @endif
            </div>
        </div>

        <div class="row g-3 mb-4">
            @include('partials.dashboard-cards', [
                'colClass' => '',
                'idPrefix' => 'assets',
                'titleLabel' => 'Kendaraan Tersedia',
                'items' => ($availableAssets ?? $assets ?? collect()),
                'showSummaries' => true,
                'bookingsToday' => $bookingsToday ?? 0,
                'nextBookingTime' => $nextBookingTime ?? '-'
            ])

            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Reservasi Minggu Ini</h6>
                    </div>
                    <div class="card-body">
                        @php $hasAnyBooking = false; @endphp

                        {{-- overdue bookings are shown in a modal via the left-card 'Lihat' button --}}

                        @foreach($weekDays as $date => $day)
                            @php $bookings = $day['bookings'] ?? collect(); @endphp

                            @if(count($bookings) > 0)
                                @php $hasAnyBooking = true; @endphp

                                <div class="panel panel-default">
                                    <div class="panel-body">
                                        <h4 class="page-header">{{ $day['label'] ?? \Carbon\Carbon::parse($date)->format('l, d M Y') }}</h4>

                                        <ul class="list-unstyled">
                                            @foreach($bookings as $booking)
                                                @php
                                                    // determine overdue first so overdue items always show
                                                    $isOverdue = !empty($booking['is_overdue']) && $booking['is_overdue'];
                                                    if(!$isOverdue) {
                                                        $isOverdue = \Carbon\Carbon::parse($booking['end_at'])->lt(\Carbon\Carbon::now());
                                                    }

                                                    // hide bookings that ended more than 24 hours ago, but keep overdue ones
                                                    $bookingEnd = \Carbon\Carbon::parse($booking['end_at']);
                                                    $hideThreshold = $bookingEnd->copy()->addDay();
                                                @endphp
                                                @if(!$isOverdue && $hideThreshold->lt(\Carbon\Carbon::now()))
                                                    @continue
                                                @endif
                                                <li class="mb-2">
                                                    <div class="card shadow-sm border-0 reservation-card">
                                                        @php
                                                            $cardBodyClass = 'card-body d-flex justify-content-between align-items-center py-2';
                                                            if($isOverdue) $cardBodyClass .= ' overdue-booking';
                                                        @endphp
                                                        <div class="{{ $cardBodyClass }}">
                                                            {{-- left: flexible info column (allow wrapping) --}}
                                                            <div class="reservation-info d-flex flex-column" style="flex:1;min-width:0;">
                                                                <h5 class="fw-bold mb-1">{{ $booking['asset_name'] ?? '—' }}</h5>
                                                                <div class="text-muted small mb-0">{{ $booking['title'] ?? '-' }}</div>
                                                                @php
                                                                    $start = \Carbon\Carbon::parse($booking['start_at']);
                                                                    $end = \Carbon\Carbon::parse($booking['end_at']);
                                                                @endphp
                                                                @if($start->isSameDay($end))
                                                                    <div class="text-muted small mb-0">{{ $start->format('H:i') }} - {{ $end->format('H:i') }}</div>
                                                                @else
                                                                    <div class="text-muted small mb-0">{{ $start->locale('id')->isoFormat('D MMM YYYY HH:mm') }} &rarr; {{ $end->locale('id')->isoFormat('D MMM YYYY HH:mm') }}</div>
                                                                @endif

                                                                @php
                                                                    // build label/value meta rows for cleaner layout
                                                                    $meta = [];
                                                                    if(!empty($booking['pic_name'])) $meta[] = ['label' => 'PIC', 'value' => Str::title($booking['pic_name'])];
                                                                    elseif(!empty($booking['pic'])) $meta[] = ['label' => 'PIC', 'value' => $booking['pic']];
                                                                    
                                                                    if(!empty($booking['driver'])) $meta[] = ['label' => 'Driver', 'value' => $booking['driver']];

                                                                    if(!empty($booking['personnel'])) {
                                                                        // prefer array for personnel chips
                                                                        $personnelArr = is_array($booking['personnel']) ? $booking['personnel'] : preg_split('/[,;]\s*/', $booking['personnel']);
                                                                        // store array for rendering as chips
                                                                        $meta[] = ['label' => 'Personel', 'value' => $personnelArr, 'is_personnel' => true];
                                                                    }

                                                                    if(!empty($booking['purpose'])) $meta[] = ['label' => 'Keperluan', 'value' => $booking['purpose']];
                                                                    
                                                                    if(!empty($booking['destination_text'])) $meta[] = ['label' => 'Tujuan', 'value' => $booking['destination_text']];
                                                                    elseif(!empty($booking['destination'])) $meta[] = ['label' => 'Tujuan', 'value' => $booking['destination']];
                                                                @endphp
                                                                @if(count($meta))
                                                                    @php
                                                                        // map labels to bootstrap-icon names
                                                                        $iconMap = [
                                                                            'PIC' => 'person-circle',
                                                                            'Driver' => 'person-badge',
                                                                            'Personel' => 'people-fill',
                                                                            'Tujuan' => 'geo-alt-fill',
                                                                            'Keperluan' => 'clipboard'
                                                                        ];
                                                                    @endphp
                                                                    <div class="text-muted small mt-2">
                                                                        @foreach($meta as $idx => $m)
                                                                            @php $icon = $iconMap[$m['label']] ?? 'info-circle'; @endphp
                                                                            @if(!empty($m['is_personnel']) && is_array($m['value']))
                                                                                @php
                                                                                    $persons = $m['value'];
                                                                                    $visible = array_slice($persons, 0, 5);
                                                                                    $hidden = array_slice($persons, 5);
                                                                                    $collapseId = 'personnel-collapse-'.Str::slug($booking['id'] ?? ($booking['title'] ?? $idx));
                                                                                @endphp
                                                                                <div class="d-flex align-items-start gap-2 mb-1 meta-row">
                                                                                    <div class="meta-icon bg-light rounded-2 d-inline-flex align-items-center justify-content-center">
                                                                                        <i class="bi bi-{{ $icon }} text-muted" style="font-size:1rem"></i>
                                                                                    </div>
                                                                                    <div class="meta-body">
                                                                                        <div class="meta-label small text-uppercase fw-bold" style="letter-spacing:.02em">{{ $m['label'] }}</div>
                                                                                        <div class="meta-value">
                                                                                            <ul class="personnel-list mb-0">
                                                                                                @foreach($visible as $p)
                                                                                                    <li class="personnel-item">{{ trim($p) }}</li>
                                                                                                @endforeach
                                                                                            </ul>

                                                                                            @if(count($hidden) > 0)
                                                                                                <div class="collapse" id="{{ $collapseId }}">
                                                                                                    <ul class="personnel-list mb-0">
                                                                                                        @foreach($hidden as $p)
                                                                                                            <li class="personnel-item">{{ trim($p) }}</li>
                                                                                                        @endforeach
                                                                                                    </ul>
                                                                                                </div>

                                                                                                <div class="more-personnel-wrap">
                                                                                                    <button class="btn btn-sm btn-link more-personnel p-0" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="false" aria-controls="{{ $collapseId }}">+{{ count($hidden) }} lainnya</button>
                                                                                                </div>
                                                                                            @endif
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            @else
                                                                                <div class="d-flex align-items-start gap-2 mb-1 meta-row">
                                                                                    <div class="meta-icon bg-light rounded-2 d-inline-flex align-items-center justify-content-center">
                                                                                        <i class="bi bi-{{ $icon }} text-muted" style="font-size:1rem"></i>
                                                                                    </div>
                                                                                    <div class="meta-body">
                                                                                        <div class="meta-label small text-uppercase fw-bold" style="letter-spacing:.02em">{{ $m['label'] }}</div>
                                                                                        <div class="meta-value">
                                                                                            @if($m['label'] === 'Driver' && !empty($m['value']))
                                                                                                <div>{{ $m['value'] }}</div>

                                                                                                {{-- additional driver info: phone / mobile --}}
                                                                                                @if(!empty($booking['driver_phone']) || !empty($booking['driver_mobile']))
                                                                                                    @php $drvPhone = $booking['driver_phone'] ?? $booking['driver_mobile']; @endphp
                                                                                                    <div class="small text-muted mt-1">
                                                                                                        <i class="bi bi-telephone" style="font-size:.9rem"></i>
                                                                                                        <a href="tel:{{ preg_replace('/\s+/', '', $drvPhone) }}" class="ms-1">{{ $drvPhone }}</a>
                                                                                                    </div>
                                                                                                @endif

                                                                                                {{-- driver's license / ID if present --}}
                                                                                                @if(!empty($booking['driver_license']))
                                                                                                    <div class="small text-muted mt-1">
                                                                                                        <i class="bi bi-card-text" style="font-size:.9rem"></i>
                                                                                                        <span class="ms-1">{{ $booking['driver_license'] }}</span>
                                                                                                    </div>
                                                                                                @endif
                                                                                            @else
                                                                                                {{ $m['value'] }}
                                                                                            @endif
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            @endif
                                                                        @endforeach
                                                                    </div>
                                                                @endif
                                                            </div>

                                                            <div class="text-end pt-1 py-2">
                                                                @php
                                                                    $now = \Carbon\Carbon::now();
                                                                    $start = \Carbon\Carbon::parse($booking['start_at']);
                                                                    $end = \Carbon\Carbon::parse($booking['end_at']);
                                                                    $status = $booking['status'] ?? null;
                                                                @endphp

                                                                @if(!empty($booking['user_name']))
                                                                    <div class="small mt-1 user-name">{{ Str::title($booking['user_name']) }}</div>
                                                                @endif

                                                                {{-- Overdue badge takes precedence --}}
                                                                @if(!empty($booking['is_overdue']) && $booking['is_overdue'])
                                                                    <span class="badge bg-danger">Terlambat</span>
                                                                @elseif($status === 'in_use')
                                                                    <span class="badge bg-success">Sedang Digunakan</span>
                                                                @elseif($status === 'approved')
                                                                    @if($now->gte($start))
                                                                        <span class="badge bg-success">Sedang Digunakan</span>
                                                                    @elseif($start->gt($now))
                                                                        <span class="badge bg-secondary">Dibooking</span>
                                                                    @else
                                                                        <span class="badge bg-primary">Approved</span>
                                                                    @endif
                                                                @else
                                                                    <span class="badge bg-primary">{{ ucfirst($status ?? '—') }}</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        @if(!$hasAnyBooking)
                            <div class="panel panel-default">
                                <div class="panel-body">
                                    <p class="text-muted">Tidak ada peminjaman kendaraan dalam 7 hari dari hari ini.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

@endsection

@push('head')
    <!-- FullCalendar CSS (kept for parity with room dashboard) -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.7/index.global.min.css" rel="stylesheet">
    <style>
        /* reuse most styles from room dashboard for consistent look */
        #dashboard-calendar .fc-daygrid-day-top a,
        #dashboard-calendar .fc-daygrid-day-number,
        #dashboard-calendar .fc-daygrid-day-number a {
            text-decoration: none !important;
            color: inherit !important;
            cursor: default !important;
        }

        #dashboard-calendar .fc-col-header-cell,
        #dashboard-calendar .fc-col-header-cell * {
            color: #6c757d !important;
            text-decoration: none !important;
            cursor: default !important;
        }

        #dashboard-calendar .fc-toolbar .fc-button {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            padding: 0.25rem;
            background: #1f2937;
            color: #fff;
            border: none;
            border-radius: 6px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.08);
            margin: 0 .25rem;
        }

        #dashboard-calendar .fc-toolbar .fc-button i {
            font-size: 1rem;
            line-height: 1;
        }

        #dashboard-calendar .fc-toolbar { padding: .5rem .5rem 0 !important; }
        #dashboard-calendar .fc-toolbar .fc-toolbar-chunk:nth-child(2) {
            display: flex !important; align-items: center !important; justify-content: center !important; gap: 1rem !important; flex-wrap: nowrap !important;
        }
        #dashboard-calendar .fc-toolbar-title { margin: 0 !important; font-size: 1.4rem !important; font-weight: 600 !important; }

        #dashboard-calendar .fc-daygrid-day { min-height: 56px; }

        .booking-marker { position: absolute; top: 6px; left: 6px; width: 10px; height: 10px; background: #28a745; border-radius: 50%; box-shadow: 0 1px 2px rgba(0,0,0,0.12); pointer-events: none; z-index: 5; }

        .calendar-nav-left, .calendar-nav-right { display: inline-flex !important; align-items: center !important; justify-content: center !important; width: 36px !important; height: 36px !important; padding: 0 !important; border-radius: 6px !important; flex: 0 0 auto !important; visibility: visible !important; opacity: 1 !important; }

        @media (max-width: 576px) {
            .reservation-card .card-body.d-flex { flex-direction: column !important; align-items: flex-start !important; gap: .5rem; }
            .reservation-card .card-body .text-end { align-self: stretch; display: flex !important; flex-direction: row !important; justify-content: space-between !important; align-items: center !important; width: 100%; margin-top: .25rem; text-align: left !important; }
            .reservation-card .card-body .text-end > * { margin-left: 0; }
            .reservation-card .card-body .text-end .user-name { text-align: left !important; flex: 1; min-width: 0; overflow-wrap: anywhere; }
            .reservation-card .card-body .text-end .badge { flex-shrink: 0; margin-left: .5rem; }
            .reservation-card .text-muted.small { font-size: .82rem; }
            .reservation-card .fw-bold { font-size: 1.05rem; margin-bottom: .2rem; }
            .reservation-card { margin-bottom: .8rem; }
        }

        .reservation-card { padding: 0.25rem; }
        .reservation-card .card-body { padding: .75rem 1rem; overflow-wrap: anywhere; }
        /* keep right column narrow on desktop so it doesn't eat space */
        @media (min-width: 577px) {
            .reservation-card .card-body .text-end { flex: 0 0 150px; max-width: 180px; }
        }
        .reservation-card .user-name { font-weight: 600; color: #222; }
        @media (min-width: 768px) { .reservation-card .card-body { padding: .9rem 1.25rem; } .reservation-card .user-name { font-weight: 700; } }

        /* meta row styling for weekly bookings */
        .meta-row { margin-top: 0.125rem; max-width: 100%; word-break: break-word; overflow-wrap: anywhere; }
        /* make the icon a bit smaller so content has more room */
        .meta-icon { width:28px; height:28px; flex:0 0 28px; }
        .meta-body { min-width: 0; }
        .meta-body .meta-label { font-size: .65rem; }
        .meta-body .meta-value { color: #444; white-space: normal; word-break: break-word; overflow-wrap: anywhere; max-width: 100%; }
        </style>
        <style>
        /* personnel plain text styling */
        .personnel-chips { gap: .25rem; align-items: center; }
        .personnel-name { color: #333; font-size: .92rem; }
        .more-personnel { padding: 0; font-size: .9rem; }
        .personnel-full-list { padding-left: .5rem; }
        .personnel-list { margin: 0; padding-left: 1rem; }
        .personnel-item { color: #444; padding: .12rem 0; list-style: disc; word-break: break-word; overflow-wrap: anywhere; }
        /* align more link with list items */
        .more-personnel-wrap { display: block; margin: 0; padding-left: 1rem; }
        .more-personnel { display: inline-block; margin-left: 0; }
        @media (max-width:576px) {
            .personnel-chips { gap: .2rem; }
            /* ensure values wrap and don't push layout */
            .meta-body .meta-value { display: block; max-width: 100%; }
        }
    </style>
    <style>
        /* overdue booking highlight */
        .reservation-card .overdue-booking {
            border-left: 4px solid #dc3545; /* bootstrap danger */
            background-color: rgba(220,53,69,0.05);
        }
        .reservation-card .overdue-booking .user-name { color: #b02a37; }
        @media (max-width:576px) {
            .reservation-card .overdue-booking { padding-left: .75rem; }
        }
    </style>
@endpush

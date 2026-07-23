@extends('layouts.app')

@section('title', 'Dashboard Booking Ruangan')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">Halo, {{ Auth::user() ? Str::title(Auth::user()->name) : 'User' }}</h3>
            <div>
                @if(Route::has('bookings.create'))
                    <a href="{{ route('bookings.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus"></i> Book
                    </a>
                @else
                    <a href="#" class="btn btn-primary">Buat Reservasi</a>
                @endif
            </div>
        </div>

    <div class="row g-3 mb-4">
    @include('partials.dashboard-cards', ['allTimeBookings' => $allTimeBookings ?? collect()])

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Reservasi Minggu Ini</h6>
                    {{-- <small class="text-muted">Menampilkan 7 hari dari hari ini</small> --}}
                </div>
                <div class="card-body">
                    @php $hasAnyBooking = false; @endphp

                    @foreach($weekDays as $date => $day)
                        @php $bookings = $day['bookings'] ?? collect(); @endphp

                        @if(count($bookings) > 0)
                            @php $hasAnyBooking = true; @endphp

                            <div class="panel panel-default">
                                <div class="panel-body">
                                    <h4 class="page-header">{{ $day['label'] ?? \Carbon\Carbon::parse($date)->format('l, d M Y') }}</h4>

                                    <ul class="list-unstyled">
                                        @foreach($bookings as $booking)
                                            <li class="mb-2">
                                                <div class="card shadow-sm border-0 reservation-card">
                                                    <div class="card-body d-flex justify-content-between align-items-center py-2">
                                                        <div>
                                                            <h5 class="fw-bold mb-1">{{ $booking['room_name'] ?? '—' }}</h5>
                                                            <div class="text-muted small mb-0">{{ $booking['title'] ?? '-' }}</div>
                                                            @php
                                                                $bStart = \Carbon\Carbon::parse($booking['start_at']);
                                                                $bEnd   = \Carbon\Carbon::parse($booking['end_at']);
                                                                $isMultiDay = !$bStart->isSameDay($bEnd);
                                                            @endphp
                                                            <div class="text-muted small mb-0">
                                                                @if($isMultiDay)
                                                                    {{ $bStart->locale('id')->isoFormat('D MMM YYYY HH:mm') }} &rarr; {{ $bEnd->locale('id')->isoFormat('D MMM YYYY HH:mm') }}
                                                                @else
                                                                    {{ $bStart->format('H:i') }} - {{ $bEnd->format('H:i') }}
                                                                @endif
                                                            </div>
                                                            @php
                                                                // structured meta rows with icons (like kendaraan dashboard)
                                                                $meta = [];
                                                                if(!empty($booking['division'])) $meta[] = ['label' => 'Divisi', 'value' => $booking['division']];
                                                                if(!empty($booking['directorate'])) $meta[] = ['label' => 'Direktorat', 'value' => $booking['directorate']];
                                                                if(!empty($booking['partner']) && $booking['partner'] !== '-') $meta[] = ['label' => 'Mitra', 'value' => $booking['partner']];

                                                                $pInternal = $booking['participants_internal'] ?? null;
                                                                $pExternal = $booking['participants_external'] ?? null;
                                                                if(!empty($pInternal) || !empty($pExternal)) {
                                                                    $parts = [];
                                                                    if(!empty($pInternal)) $parts[] = 'Internal: '.$pInternal;
                                                                    if(!empty($pExternal)) $parts[] = 'Eksternal: '.$pExternal;
                                                                    $meta[] = ['label' => 'Peserta', 'value' => implode(' • ', $parts)];
                                                                }

                                                                if(!empty($booking['facilities'])) {
                                                                    $facStr = trim((string) $booking['facilities']);
                                                                    if(!in_array($facStr, ['-', '–', '—'], true)) {
                                                                        if(strpos($facStr, ',') !== false) {
                                                                            $facItems = array_filter(array_map('trim', explode(',', $facStr)));
                                                                            $facValue = implode(', ', $facItems);
                                                                        } else {
                                                                            $facValue = $facStr;
                                                                        }
                                                                        if(!empty($facValue)) $meta[] = ['label' => 'Kebutuhan Fasilitas', 'value' => $facValue];
                                                                    }
                                                                }
                                                            @endphp
                                                            @if(count($meta))
                                                                @php
                                                                    $iconMap = [
                                                                        'Divisi' => 'diagram-3',
                                                                        'Direktorat' => 'building',
                                                                        'Mitra' => 'person-lines-fill',
                                                                        'Peserta' => 'people-fill',
                                                                        'Kebutuhan Fasilitas' => 'card-checklist'
                                                                    ];
                                                                @endphp
                                                                <div class="text-muted small mt-2">
                                                                    @foreach($meta as $m)
                                                                        @php $icon = $iconMap[$m['label']] ?? 'info-circle'; @endphp
                                                                        <div class="d-flex align-items-start gap-2 mb-1 meta-row">
                                                                            <div class="meta-icon bg-light rounded-2 d-inline-flex align-items-center justify-content-center">
                                                                                <i class="bi bi-{{ $icon }} text-muted" style="font-size:1rem"></i>
                                                                            </div>
                                                                            <div class="meta-body">
                                                                                <div class="meta-label small text-uppercase fw-bold" style="letter-spacing:.02em">{{ $m['label'] }}</div>
                                                                                <div class="meta-value">{{ $m['value'] }}</div>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                            @if(!empty($booking['facilities']))
                                                                @php
                                                                    $facStr = trim((string) $booking['facilities']);
                                                                    // treat a single hyphen / dash as "not provided"
                                                                    if(in_array($facStr, ['-', '–', '—'], true)) {
                                                                        $facItems = [];
                                                                    } else {
                                                                        $facItems = array_filter(
                                                                            array_map('trim', explode(',', $facStr)),
                                                                            function($v){ return $v !== '' && !in_array($v, ['-', '–', '—'], true); }
                                                                        );
                                                                    }
                                                                @endphp
                                                            @endif
                                                        </div>

                                                        <div class="text-end pt-1 py-2">
                                                            @php
                                                                $now = \Carbon\Carbon::now();
                                                                $start = \Carbon\Carbon::parse($booking['start_at']);
                                                                $end = \Carbon\Carbon::parse($booking['end_at']);
                                                                $status = $booking['status'] ?? null;
                                                                $listDayIsToday = \Carbon\Carbon::parse($date)->isToday();
                                                            @endphp

                                                                @if(!empty($booking['user_name']))
                                                                    <div class="small mt-1 user-name">{{ Str::title($booking['user_name']) }}</div>
                                                                @endif

                                                                @if($status === 'approved')
                                                                    @if($listDayIsToday && $now->between($start, $end))
                                                                        <span class="badge bg-success">Sedang Digunakan</span>
                                                                    @elseif($start->gt($now) || !$listDayIsToday)
                                                                        <span class="badge bg-secondary">Dibooking</span>
                                                                    @elseif($end->lt($now))
                                                                        <span class="badge bg-success-">Selesai</span>
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
                                <p class="text-muted">Tidak ada reservasi dalam 7 hari dari hari ini.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection

@push('head')
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.7/index.global.min.css" rel="stylesheet">
    <style>
        /* remove underline and link color for day numbers inside the dashboard calendar */
        #dashboard-calendar .fc-daygrid-day-top a,
        #dashboard-calendar .fc-daygrid-day-number,
        #dashboard-calendar .fc-daygrid-day-number a {
            text-decoration: none !important;
            color: inherit !important;
            cursor: default !important;
        }

        /* weekday header: use muted/secondary color and remove underline */
        #dashboard-calendar .fc-col-header-cell,
        #dashboard-calendar .fc-col-header-cell * {
            color: #6c757d !important; /* bootstrap text-muted */
            text-decoration: none !important;
            cursor: default !important;
        }

        /* style FullCalendar header buttons (prev/next) to appear as small dark icons beside the title */
        #dashboard-calendar .fc-toolbar .fc-button {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            padding: 0.25rem;
            background: #1f2937; /* dark */
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

        /* tighten spacing to better fit the card and center toolbar items */
        #dashboard-calendar .fc-toolbar {
            padding: .5rem .5rem 0 !important;
        }
        /* make the center chunk (title area) a horizontal flex container so prev/title/next align
           prevent wrapping so buttons remain at left/right of the title and add more gap */
        #dashboard-calendar .fc-toolbar .fc-toolbar-chunk:nth-child(2) {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 1rem !important;
            flex-wrap: nowrap !important;
        }
        #dashboard-calendar .fc-toolbar-title {
            margin: 0 !important;
            font-size: 1.4rem !important;
            font-weight: 600 !important;
        }

        /* ensure calendar card fits visually with other cards */
        #dashboard-calendar .fc-daygrid-day {
            min-height: 56px;
        }

        /* booking marker: small green dot in the top-left of a date cell */
        #dashboard-calendar .booking-marker {
            position: absolute;
            top: 6px;
            left: 6px;
            width: 10px;
            height: 10px;
            background: #28a745; /* bootstrap success */
            border-radius: 50%;
            box-shadow: 0 1px 2px rgba(0,0,0,0.12);
            pointer-events: none;
            z-index: 5;
        }

        .calendar-nav-left, .calendar-nav-right {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 36px !important;
            height: 36px !important;
            padding: 0 !important;
            border-radius: 6px !important;
            flex: 0 0 auto !important; /* prevent shrinking */
            visibility: visible !important;
            opacity: 1 !important;
        }
        /* Responsive fixes for reservation cards on small screens */
        @media (max-width: 576px) {
            /* stack card content vertically and allow wrapping */
            .reservation-card .card-body.d-flex {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: .5rem;
            }

            /* move status/user area to the bottom-right without squeezing the left column */
            .reservation-card .card-body .text-end {
                align-self: stretch;
                display: flex !important;
                flex-direction: row !important;
                justify-content: space-between !important;
                align-items: center !important;
                width: 100%;
                margin-top: .25rem;
                text-align: left !important;
            }

            .reservation-card .card-body .text-end > * {
                margin-left: 0;
            }

            .reservation-card .card-body .text-end .user-name {
                text-align: left !important;
                flex: 1;
                min-width: 0;
                overflow-wrap: anywhere;
            }

            .reservation-card .card-body .text-end .badge {
                flex-shrink: 0;
                margin-left: .5rem;
            }

            /* slightly smaller metadata text to avoid wrap collisions */
            .reservation-card .text-muted.small {
                font-size: .82rem;
            }

            /* ensure headings have breathing room */
            .reservation-card .fw-bold {
                font-size: 1.05rem;
                margin-bottom: .2rem;
            }

            /* increase card spacing on mobile */
            .reservation-card { margin-bottom: .8rem; }
        }
        /* Card padding and user name styling */
        .reservation-card { padding: 0.25rem; }
        .reservation-card .card-body { padding: .75rem 1rem; }
        .reservation-card .user-name { font-weight: 600; color: #222; }
        @media (min-width: 768px) {
            .reservation-card .card-body { padding: .9rem 1.25rem; }
            .reservation-card .user-name { font-weight: 700; }
        }
        /* dark toolbar style for small nav buttons (used in calendar and card) */
        .calendar-nav-left, .calendar-nav-right {
            background: #1f2937 !important; /* dark */
            color: #fff !important;
            border: none !important;
            box-shadow: 0 1px 2px rgba(0,0,0,0.08) !important;
            cursor: pointer !important;
        }
        .calendar-nav-left i, .calendar-nav-right i {
            color: #fff !important;
            font-size: 1rem;
            line-height: 1;
        }
        .calendar-nav-left:hover, .calendar-nav-right:hover,
        .calendar-nav-left:focus, .calendar-nav-right:focus {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.12) !important;
        }
        /* toolbar placement overrides: we'll move these into FC toolbar via JS */
        .fc-toolbar .calendar-nav-left, .fc-toolbar .calendar-nav-right {
            margin: 0 .75rem !important; /* give extra breathing room from the title */
        }
        /* rooms list styling inside the first card */
        .rooms-list .fw-semibold {
            font-size: 0.95rem;
        }
        .rooms-list .badge {
            font-size: 0.8rem;
            padding: .35em .5em;
        }
        .rooms-list::-webkit-scrollbar {
            width: 8px;
        }
        .rooms-list::-webkit-scrollbar-thumb {
            background: rgba(0,0,0,0.08);
            border-radius: 8px;
        }
    /* meta row styling (shared with vehicle dashboard) */
    .meta-row { margin-top: 0.125rem; }
    .meta-icon { width:34px; height:34px; flex:0 0 34px; }
    .meta-body .meta-label { font-size: .65rem; }
    .meta-body .meta-value { color: #444; }
    </style>
@endpush

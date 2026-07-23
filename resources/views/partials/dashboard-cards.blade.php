<div class="col-md-4 {{ $colClass ?? '' }}">
    @php
        // configurable pieces for re-use with rooms or assets
        $idPrefix = $idPrefix ?? 'rooms';
        $titleLabel = $titleLabel ?? 'Ruang Tersedia';
        // items can be provided as $items, or fall back to legacy $rooms / $availableRooms / $assets
        $itemsData = $items ?? $rooms ?? ($availableRooms ?? null) ?? ($availableAssets ?? $assets ?? collect());
    @endphp

    <div class="d-flex flex-column gap-3">
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <div class="text-center">
                    <h4 class="mb-3">{{ $titleLabel }}</h4>
                </div>

                <div class="d-flex justify-content-center align-items-center gap-2 my-2">
                    <button type="button" id="{{ $idPrefix }}-date-prev" class="calendar-nav-left bg-dark text-white" aria-label="Previous date"><i class="bi bi-chevron-left"></i></button>
                    <h5 id="{{ $idPrefix }}-date-label" class="text-center pt-2 mx-3" style="min-width:160px;"></h5>
                    <button type="button" id="{{ $idPrefix }}-date-next" class="calendar-nav-right bg-dark text-white" aria-label="Next date"><i class="bi bi-chevron-right"></i></button>
                </div>

                {{-- items list: scrollable to keep card height reasonable --}}
                <div id="{{ $idPrefix }}-availability" class="mt-4 rooms-list" data-items='@json($itemsData)'
                     data-rooms='@json($rooms ?? $itemsData ?? null)' data-alltime-bookings='@json($allTimeBookings ?? collect())'>
                    @php
                        // Try to render a server-side list when possible so the card is usable without JS
                        try {
                            $list = $itemsData instanceof \Illuminate\Support\Collection ? $itemsData : (is_array($itemsData) ? collect($itemsData) : null);
                        } catch (\Throwable $e) {
                            $list = null;
                        }
                    @endphp
                    <div class="text-muted">Memuat…</div>
                </div>
            </div>
        </div>

        @if($showSummaries ?? true)
        {{-- simple overdue card (shown when controller passes overdueCount) --}}
        @if(!empty($overdueCount) && $overdueCount > 0)
        <div class="card h-100 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="bg-danger text-white rounded-3 p-3 d-inline-flex align-items-center justify-content-center" style="width:56px;height:56px;">
                    <i class="bi bi-exclamation-circle" style="font-size:1.25rem;"></i>
                </div>
                <div>
                    <div class="text-muted small">Keterlambatan</div>
                    <div class="h5 mb-0">{{ $overdueCount }}</div>
                </div>
                <div class="ms-auto">
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#overdueModal">Lihat</button>
                </div>
            </div>
        </div>
        @endif
        <div class="card h-100 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="bg-success text-white rounded-3 p-3 d-inline-flex align-items-center justify-content-center" style="width:56px;height:56px;">
                    <i class="bi bi-calendar-check" style="font-size:1.25rem;"></i>
                </div>
                <div>
                    <div class="text-muted small">Reservasi Hari Ini</div>
                    <div class="h5 mb-0">{{ $bookingsToday ?? '0' }}</div>
                </div>
            </div>
        </div>

        <div class="card h-100 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="bg-warning text-white rounded-3 p-3 d-inline-flex align-items-center justify-content-center" style="width:56px;height:56px;">
                    <i class="bi bi-clock" style="font-size:1.25rem;"></i>
                </div>
                <div>
                    <div class="text-muted small">Booking Berikutnya</div>
                    <div class="h5 mb-0">{{ $nextBookingTime }}</div>
                </div>
            </div>
        </div>
        @endif
        <!-- Calendar widget (driven by external calendar lib via JS) -->
        <div class="card shadow-sm">
            <div class="card-body">
                        <div style="position:relative">
                            <div id="dashboard-calendar"></div>
                        </div>
                        <div class="mt-2 small text-muted">
                            <ul class="mb-0 ps-3" style="line-height:1.15;">
                                <li><strong>Klik</strong> tanggal untuk melihat booking pada hari itu.</li>
                                <li><strong>Titik hijau</strong> di pojok kiri = ada minimal 1 booking terverifikasi.</li>
                                <li>Modal menampilkan booking yang <em>overlap</em> dengan hari (bukan hanya waktu).</li>
                            </ul>
                        </div>
                </div>
        </div>
    </div>
</div>
<!-- Overdue modal (moved inline from partial so it lives inside dashboard cards) -->
<div class="modal fade" id="overdueModal" tabindex="-1" aria-labelledby="overdueModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="overdueModalLabel">Keterlambatan Kendaraan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if(empty($overdueBookings) || count($overdueBookings) === 0)
                    <p class="text-muted">Tidak ada kendaraan terlambat.</p>
                @else
                    <ul class="list-unstyled">
                        @foreach($overdueBookings as $booking)
                            <li class="mb-3">
                                <div class="card shadow-sm border-0 reservation-card">
                                    @php
                                        $isOverdue = !empty($booking['is_overdue']) && $booking['is_overdue'];
                                        $cardBodyClass = 'card-body d-flex justify-content-between align-items-center py-2';
                                        if($isOverdue) $cardBodyClass .= ' overdue-booking';
                                    @endphp
                                    <div class="{{ $cardBodyClass }}">
                                        {{-- left: flexible info column (allow wrapping) - match weekly list sizing --}}
                                        <div class="reservation-info d-flex flex-column" style="flex:1 1 auto; min-width:0; max-width:calc(100% - 180px);">
                                            <h5 class="fw-bold mb-1">{{ $booking['asset_name'] ?? '—' }}</h5>
                                            <div class="text-muted small mb-0">{{ $booking['title'] ?? '-' }}</div>
                                            @php
                                                $start = \Carbon\Carbon::parse($booking['start_at']);
                                                $end = \Carbon\Carbon::parse($booking['end_at']);
                                                $today = \Carbon\Carbon::today();
                                            @endphp
                                            {{-- if both start and end are today show times only, otherwise show full range --}}
                                            @if($start->isSameDay($today) && $end->isSameDay($today))
                                                <div class="text-muted small mb-0">{{ $start->format('H:i') }} - {{ $end->format('H:i') }}</div>
                                            @else
                                                <div class="text-muted small mb-0">{{ $start->locale('id')->isoFormat('D MMM YYYY HH:mm') }} &rarr; {{ $end->locale('id')->isoFormat('D MMM YYYY HH:mm') }}</div>
                                            @endif

                                            @php
                                                // build meta rows
                                                $meta = [];
                                                if(!empty($booking['pic_name'])) $meta[] = ['label' => 'PIC', 'value' => Str::title($booking['pic_name'])];
                                                elseif(!empty($booking['pic'])) $meta[] = ['label' => 'PIC', 'value' => $booking['pic'] ?? null];
                                                if(!empty($booking['driver'])) $meta[] = ['label' => 'Driver', 'value' => $booking['driver']];
                                                if(!empty($booking['personnel'])) {
                                                    $personnelArr = is_array($booking['personnel']) ? $booking['personnel'] : preg_split('/[,;]\s*/', $booking['personnel']);
                                                    $meta[] = ['label' => 'Personel', 'value' => $personnelArr, 'is_personnel' => true];
                                                }
                                                if(!empty($booking['purpose'])) $meta[] = ['label' => 'Keperluan', 'value' => $booking['purpose']];
                                                if(!empty($booking['destination_text'])) $meta[] = ['label' => 'Tujuan', 'value' => $booking['destination_text']];
                                                elseif(!empty($booking['destination'])) $meta[] = ['label' => 'Tujuan', 'value' => $booking['destination']];
                                            @endphp

                                            @if(count($meta))
                                                @php
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
                                                                            @if(!empty($booking['driver_phone']) || !empty($booking['driver_mobile']))
                                                                                @php $drvPhone = $booking['driver_phone'] ?? $booking['driver_mobile']; @endphp
                                                                                <div class="small text-muted mt-1">
                                                                                    <i class="bi bi-telephone" style="font-size:.9rem"></i>
                                                                                    <a href="tel:{{ preg_replace('/\s+/', '', $drvPhone) }}" class="ms-1">{{ $drvPhone }}</a>
                                                                                </div>
                                                                            @endif
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

                                        {{-- right: fixed/status column (prevent shrinking) - match weekly list sizing --}}
                                        <div class="reservation-right-col pt-1 py-2 d-flex flex-column align-items-end text-end d-none d-sm-flex" style="flex:0 0 180px; min-width:140px; max-width:220px;">
                                            @php
                                                $now = \Carbon\Carbon::now();
                                                $start = \Carbon\Carbon::parse($booking['start_at']);
                                                $end = \Carbon\Carbon::parse($booking['end_at']);
                                                $status = $booking['status'] ?? null;
                                            @endphp

                                            {{-- @if(!empty($booking['user_name']))
                                                <div class="small mt-1 user-name">{{ $booking['user_name'] }}</div>
                                            @endif --}}

                                            <div class="small mt-1 user-name">{{ isset($booking['user_name']) ? Str::title($booking['user_name']) : ($booking['user'] ?? '—') }}</div>

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

                                        {{-- Mobile footer: show user + status in one row on extra-small screens --}}
                                        <div class="mobile-footer d-flex justify-content-between align-items-center w-100 mt-2 d-sm-none p-3">
                                            <div class="small user-name">{{ isset($booking['user_name']) ? Str::title($booking['user_name']) : ($booking['user'] ?? '—') }}</div>
                                            <div>
                                                @php
                                                    $now = \Carbon\Carbon::now();
                                                    $start = \Carbon\Carbon::parse($booking['start_at']);
                                                    $status = $booking['status'] ?? null;
                                                @endphp

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
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    /* Cap modal height and make modal-body scrollable so the modal stays aligned */
    #overdueModal .modal-content { max-height: 90vh; overflow: hidden; }
    #overdueModal .modal-body { overflow-y: auto; max-height: calc(90vh - 120px); }
    /* ensure centering */
    #overdueModal .modal-dialog { display: flex !important; align-items: center !important; justify-content: center !important; }
    /* Fix long/unbreakable text inside reservation items so it wraps instead of pushing layout */
    #overdueModal .reservation-info { min-width: 0; }
    #overdueModal .reservation-info, #overdueModal .reservation-info .text-muted, #overdueModal .meta-value, #overdueModal .reservation-info * {
        overflow-wrap: anywhere !important; /* allow long words to break */
        word-break: break-word !important;
        white-space: normal !important;
    }
    /* Truncate user name and keep badge visible in right column */
    #overdueModal .user-name { max-width: 140px; display: inline-block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    /* Make modal fit better on very small screens */
    @media (max-width: 480px) {
        #overdueModal .modal-dialog { max-width: 95vw; margin: 0 8px; }
        #overdueModal .modal-content { box-sizing: border-box; }
    }
    /* Ensure modal left column sizing matches weekly list */
    #overdueModal .reservation-info[style] { max-width: calc(100% - 160px) !important; }
    @media (max-width: 768px) {
        #overdueModal .reservation-info[style] { max-width: calc(100% - 140px) !important; }
    }
    /* Mobile: stack columns so content isn't squeezed */
    @media (max-width: 576px) {
        #overdueModal .reservation-card .card-body.d-flex { flex-direction: column !important; align-items: flex-start !important; gap: .5rem; }
        #overdueModal .reservation-info[style] { max-width: 100% !important; flex: 1 1 auto !important; }
        #overdueModal .text-end[style] { min-width: 0 !important; max-width: 100% !important; align-self: stretch; display: flex; justify-content: space-between; margin-top: .25rem; }
        #overdueModal .user-name { max-width: 70% !important; }
    }

    /* Dashboard card: ensure right column sticks to the edge and left info flexes */
    .reservation-right-col { display:flex; flex-direction:column; align-items:flex-end; text-align:right; gap: .35rem; }
    .reservation-info { overflow-wrap:anywhere; word-break:break-word; }

    @media (max-width: 768px) {
        /* allow right column to reduce but stack below on very small widths */
        .reservation-right-col { flex: 0 0 140px; }
    }
    @media (max-width: 576px) {
        .reservation-card .card-body.d-flex { flex-direction: column !important; align-items: flex-start !important; }
        .reservation-right-col { width: 100% !important; align-items: flex-start !important; text-align: left !important; margin-top: .5rem; }
    }

    /* Mobile footer: show condensed user + status row */
    .mobile-footer .user-name { font-weight:600; }
    @media (max-width: 576px) {
        .reservation-right-col.d-none.d-sm-flex { display: none !important; }
        .mobile-footer { display: flex !important; gap: .5rem; }
    }
</style>

<!-- Modal for showing bookings on a selected date (shared) -->
<div class="modal fade" id="calendarDateModal" tabindex="-1" aria-labelledby="calendarDateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="calendarDateModalLabel">Booking pada <span id="modal-date-label"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="modal-bookings-list">
                    <div class="text-muted">Loading…</div>
                </div>
            </div>
            {{-- <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div> --}}
        </div>
    </div>
</div>

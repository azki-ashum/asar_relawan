@extends('layouts.app')

@section('title', 'Admin - Booking Ruangan')

@section('content')
<!-- ====== Style lokal halaman (card layout mirip asset booking) ====== -->
<style>
    #booking-admin .modern-table-wrapper { max-height: 70vh; }
    /* Status badge palette */
    #booking-admin .badge-soft-success{ background:#d1e7dd; color:#0f5132; font-weight:600; }
    #booking-admin .badge-soft-warning{ background:#fff3cd; color:#664d03; font-weight:600; }
    #booking-admin .badge-soft-danger{ background:#f8d7da; color:#842029; font-weight:600; }
    #booking-admin .badge-soft-green{ background:#d2f4d2; color:#0c8f0c; font-weight:600; }
    #booking-admin .badge-soft-secondary{ background:#e2e3e5; color:#41464b; font-weight:600; }

    /* Booking card list (responsive grid) */
    .booking-list { display: grid; gap: 1rem; grid-template-columns: 1fr; }
    @media(min-width:768px) { .booking-list { grid-template-columns: 1fr 1fr; } }
    .booking-list .empty-state { grid-column: 1 / -1; }

    .booking-card { border-radius: .5rem; }
    .booking-card .icon { width:40px; height:40px; display:inline-flex; align-items:center; justify-content:center; }
    .booking-card .card-body { padding: .75rem; }
    .booking-card h5 { font-size: 1rem; margin-bottom: .25rem; }
    .booking-card .text-muted.small { font-size: .78rem; }
    .booking-card .actions .btn { padding: .25rem .5rem; }
    .booking-card .status-badge { display:block; text-align:right; }
    .booking-card .status-badge .badge{ font-weight:600; }

    /* Make actions wrap nicely on small screens */
    @media (max-width: 575.98px) {
        .booking-card .actions { gap: .5rem; }
        .booking-card .actions .btn { flex: 1 1 auto; }
    }

    /* Modal styling */
    #roomBookingDetailModal .modal-content{ border-radius: 1rem; }
    #roomBookingDetailModal .detail-header{ display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; margin-bottom: .75rem; }
    #roomBookingDetailModal .detail-title{ margin:0; font-size:1.1rem; font-weight:700; }
    #roomBookingDetailModal .detail-meta{ font-size:.9rem; color:#6b7280; }
    #roomBookingDetailModal .status-pill-sm{ display:inline-flex; align-items:center; gap:.35rem; padding:.25rem .55rem; border-radius:999px; font-weight:600; font-size:.76rem; background:#eef2ff; color:#1e40af; }
    #roomBookingDetailModal .status-pill-sm.badge-soft-success{ background:#d1e7dd; color:#0f5132; }
    #roomBookingDetailModal .status-pill-sm.badge-soft-warning{ background:#fff3cd; color:#664d03; }
    #roomBookingDetailModal .status-pill-sm.badge-soft-danger{ background:#f8d7da; color:#842029; }
    #roomBookingDetailModal .status-pill-sm.badge-soft-green{ background:#d2f4d2; color:#0c8f0c; }
    #roomBookingDetailModal .status-pill-sm.badge-soft-secondary{ background:#e2e3e5; color:#41464b; }
    #roomBookingDetailModal .section-title{ font-size:.78rem; letter-spacing:.02em; text-transform:uppercase; color:#6b7280; margin-bottom:.35rem; font-weight:700; }
    #roomBookingDetailModal .section{ padding:.5rem 0; border-top:1px dashed #eceff3; }
    #roomBookingDetailModal .section:first-of-type{ border-top:none; }
</style>

<div id="booking-admin" class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
        <div>
            <h3 class="mb-0 fw-semibold">Admin – Booking Ruangan</h3>
        </div>
    </div>

    {{-- Filter card (admin) --}}
    <div class="card border-0 mb-3 mx-0">
        <div class="card-body px-0 px-lg-3">
            <form method="get" class="row g-3 align-items-end">
                <div class="col-lg-6 col-md-8 col-12">
                    <label class="form-label small mb-0">Judul/User/Tujuan</label>
                    <div class="input-group mt-1">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input name="q" type="search" class="form-control" placeholder="Cari judul/user/tujuan" value="{{ request()->query('q','') }}">
                    </div>
                </div>

                <div class="col-md-3 col-12">
                    <label class="form-label small mb-0">Tanggal Dibuat</label>
                    <input name="created_date" type="text" class="form-control mt-1 flatpickr-date" placeholder="Cari tanggal dibuat" value="{{ request()->query('created_date','') }}">
                </div>

                <div class="col-lg-3 col-md-12 col-12 d-flex gap-2 justify-content-end mt-3 mt-lg-0">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>
    <!-- Card + List -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            @php
                $isPaginator = ($bookings instanceof \Illuminate\Contracts\Pagination\Paginator) || ($bookings instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) || (is_object($bookings) && method_exists($bookings,'links'));
                $hasPages = $isPaginator && is_object($bookings) && method_exists($bookings,'hasPages') ? $bookings->hasPages() : false;
                $statusMap = [
                    'approved'  => ['label'=>'Book','class'=>'badge-soft-success','icon'=>'bi-check-circle'],
                    'pending'   => ['label'=>'Pending','class'=>'badge-soft-warning','icon'=>'bi-hourglass'],
                    'in_use'    => ['label'=>'Sedang Digunakan','class'=>'badge-soft-success','icon'=>'bi-play-circle'],
                    'cancelled' => ['label'=>'Dibatalkan','class'=>'badge-soft-danger','icon'=>'bi-x-circle'],
                    'done'      => ['label'=>'Selesai','class'=>'badge-soft-green','icon'=>'bi-flag'],
                ];
            @endphp

            @if($hasPages)
                <div class="p-3 d-flex justify-content-end">
                    {{ $bookings->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            @elseif(!$isPaginator)
                <div class="p-3 small text-muted">Showing {{ count($bookings) }} items</div>
            @endif

            <div class="booking-list p-3">
                @forelse($bookings as $b)
                    @php
                        $s = $statusMap[$b->status] ?? ['label'=>ucfirst($b->status ?? '-'),'class'=>'badge-soft-secondary','icon'=>'bi-info-circle'];
                        $userName = $b->user->name ? Str::title($b->user->name) : ($b->user->email ?? '-');
                        $pi = (int)($b->participants_internal ?? 0);
                        $pe = (int)($b->participants_external ?? 0);
                    @endphp

                    <div class="card mb-2 booking-card">
                        <div class="card-body d-flex align-items-start gap-3">
                            <div class="icon bg-light rounded-3 d-flex align-items-center justify-content-center">
                                <i class="bi bi-door-open-fill fs-5 text-primary"></i>
                            </div>

                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="text-muted small">#{{ $b->id }} • {{ $b->room->name ?? '-' }}</div>
                                        <div class="fw-semibold">{{ $b->title ?? '-' }}</div>
                                        <div class="small text-muted">{{ \Carbon\Carbon::parse($b->start_at)->format('d M Y H:i') }} - {{ \Carbon\Carbon::parse($b->end_at)->format('d M Y H:i') }}</div>
                                        <div class="d-flex flex-column flex-md-row align-items-start gap-0 gap-md-3">
                                            <div class="small text-muted">PIC : {{ $userName }}</div>
                                            @if(!empty($b->division))
                                                <div class="small text-muted">Divisi : {{ $b->division }}</div>
                                            @endif
                                            @if(!empty($b->directorate))
                                                <div class="small text-muted">Direktorat : {{ $b->directorate }}</div>
                                            @endif
                                        </div>
                                        <div class="d-flex flex-column my-1">
                                            <div class="small text-muted">Tujuan : {{ $b->purpose ?? '-' }}</div>
                                            {{-- @if(!empty($b->partner))
                                                <div class="small text-muted">Mitra: {{ $b->partner }}</div>
                                            @endif --}}
                                            <div class="small text-muted">Peserta : {{ $pi }} Internal, {{ $pe }} Eksternal</div>
                                            <div class="small text-muted">Kebutuhan : {{ $b->facilities ?? '-' }}</div>
                                        </div>
                                    </div>

                                    <div class="status-badge ms-3">
                                        <span class="badge {{ $s['class'] }}"><i class="bi {{ $s['icon'] }} me-1"></i>{{ $s['label'] }}</span>
                                    </div>
                                </div>

                                <div class="mt-2">
                                    <div class="actions d-flex align-items-center gap-1 flex-wrap">
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#roomBookingDetailModal"
                                            data-id="{{ $b->id }}"
                                            data-room="{{ $b->room->name ?? '-' }}"
                                            data-title="{{ $b->title ?? '-' }}"
                                            data-user="{{ $userName }}"
                                            data-division="{{ $b->division ?? '-' }}"
                                            data-directorate="{{ $b->directorate ?? '-' }}"
                                            data-purpose="{{ $b->purpose ?? '-' }}"
                                            data-partner="{{ $b->partner ?? '-' }}"
                                            data-facilities="{{ $b->facilities ?? '-' }}"
                                            data-pi="{{ $pi }}"
                                            data-pe="{{ $pe }}"
                                            data-created="{{ \Carbon\Carbon::parse($b->created_at)->format('d-m-Y H:i') }}"
                                            data-start="{{ \Carbon\Carbon::parse($b->start_at)->format('d-m-Y H:i') }}"
                                            data-end="{{ \Carbon\Carbon::parse($b->end_at)->format('d-m-Y H:i') }}"
                                            data-status-label="{{ $s['label'] }}"
                                            data-status-class="{{ $s['class'] }}"
                                            data-status-icon="{{ $s['icon'] }}"
                                        >Details</button>

                                        <a href="{{ route('admin.bookings.edit', $b) }}" class="btn btn-sm btn-light border btn-icon" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.bookings.destroy', $b) }}" method="post" class="d-inline swal-confirm" data-confirm="Hapus booking ini?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light border btn-icon" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state w-100 d-flex align-items-center justify-content-center py-5 text-muted" style="min-height:220px;">
                        <div class="text-center">
                            <i class="bi bi-calendar-x display-6 d-block mb-2"></i>
                            <div>Belum ada data booking.</div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- @if(method_exists($bookings, 'links'))
            <div class="card-footer bg-white border-0 d-flex justify-content-end pt-4 pt-md-2">
                {{ $bookings->links('pagination::bootstrap-5') }}
            </div>
        @endif --}}
    </div>
</div>
<!-- ====== Script lokal halaman (tooltip + modal binding) ====== -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.bootstrap && bootstrap.Tooltip) {
            document.querySelectorAll('#booking-admin [title]').forEach(function (el) {
                new bootstrap.Tooltip(el);
            });
        }

        var bookingModal = document.getElementById('roomBookingDetailModal');
        if (!bookingModal) return;

        bookingModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;

            document.getElementById('rb-id').textContent = button.getAttribute('data-id') || '-';
            document.getElementById('rb-room').textContent = button.getAttribute('data-room') || '-';
            document.getElementById('rb-title').textContent = button.getAttribute('data-title') || '-';
            document.getElementById('rb-user').textContent = button.getAttribute('data-user') || '-';
            document.getElementById('rb-division').textContent = button.getAttribute('data-division') || '-';
            document.getElementById('rb-directorate').textContent = button.getAttribute('data-directorate') || '-';
            document.getElementById('rb-purpose').textContent = button.getAttribute('data-purpose') || '-';
            document.getElementById('rb-partner').textContent = button.getAttribute('data-partner') || '-';
            document.getElementById('rb-facilities').textContent = button.getAttribute('data-facilities') || '-';
            document.getElementById('rb-pi').textContent = button.getAttribute('data-pi') || '0';
            document.getElementById('rb-pe').textContent = button.getAttribute('data-pe') || '0';
            document.getElementById('rb-created').textContent = button.getAttribute('data-created') || '-';
            document.getElementById('rb-start').textContent = button.getAttribute('data-start') || '-';
            document.getElementById('rb-end').textContent = button.getAttribute('data-end') || '-';

            var statusLabel = button.getAttribute('data-status-label') || '';
            var statusIcon = button.getAttribute('data-status-icon') || '';
            var statusClass = button.getAttribute('data-status-class') || '';
            var statusEl = document.getElementById('rb-status');
            statusEl.className = 'status-pill-sm';
            if (statusLabel) {
                statusEl.classList.add(statusClass);
                statusEl.style.display = 'inline-flex';
                statusEl.innerHTML = (statusIcon ? '<i class="bi ' + statusIcon + '"></i> ' : '') + statusLabel;
            } else {
                statusEl.style.display = 'none';
                statusEl.textContent = '';
            }
        });
    });
    </script>

    <!-- Room Booking detail modal -->
    <div class="modal fade" id="roomBookingDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Booking Detail</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="detail-header">
                        <div>
                            <div class="detail-title" id="rb-title">-</div>
                            <div class="detail-meta">ID <span id="rb-id">-</span> • Ruang: <span id="rb-room">-</span></div>
                        </div>
                        <div>
                            <span id="rb-status" class="status-pill-sm" style="display:none;"></span>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="section">
                                <div class="section-title">Tujuan</div>
                                <div id="rb-purpose">-</div>
                            </div>
                            <div class="section">
                                <div class="section-title">User Booking</div>
                                <div id="rb-user">-</div>
                            </div>
                            <div class="section">
                                <div class="section-title">Divisi</div>
                                <div id="rb-division">-</div>
                            </div>
                            <div class="section">
                                <div class="section-title">Direktorat</div>
                                <div id="rb-directorate">-</div>
                            </div>
                            <div class="section">
                                <div class="section-title">Mitra</div>
                                <div id="rb-partner">-</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="section">
                                <div class="section-title">Kebutuhan</div>
                                <div id="rb-facilities">-</div>
                            </div>
                            <div class="section">
                                <div class="section-title">Peserta</div>
                                <div class="row g-2 small">
                                    <div class="col-5 text-muted">Internal</div>
                                    <div class="col-7" id="rb-pi">-</div>
                                    <div class="col-5 text-muted">Eksternal</div>
                                    <div class="col-7" id="rb-pe">-</div>
                                </div>
                            </div>
                            <div class="section">
                                <div class="section-title">Timestamps</div>
                                <div class="row g-2 small">
                                    <div class="col-5 text-muted">Dibuat</div>
                                    <div class="col-7" id="rb-created">-</div>
                                    <div class="col-5 text-muted">Mulai</div>
                                    <div class="col-7" id="rb-start">-</div>
                                    <div class="col-5 text-muted">Selesai</div>
                                    <div class="col-7" id="rb-end">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

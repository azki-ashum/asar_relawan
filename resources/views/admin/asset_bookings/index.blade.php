@extends('layouts.app')

@section('title', 'Admin - Asset Bookings')

@section('content')
<style>
    #asset-booking-admin .modern-table-wrapper { max-height: 70vh; }
    #asset-booking-admin .modern-table thead th{ position: sticky; top: 0; z-index: 2; background: #f8f9fa; box-shadow: inset 0 -1px 0 rgba(0,0,0,.08); text-align:center; }
    #asset-booking-admin .modern-table td { text-align: center; }
    #asset-booking-admin .modern-table :where(td, th){ padding: 1.1rem 1.25rem !important; vertical-align: middle; }
    #asset-booking-admin .modern-table tbody td { border-top: 1px solid #eef0f2; }
    #asset-booking-admin .truncate-1{ display:-webkit-box; -webkit-box-orient:vertical; overflow:hidden; -webkit-line-clamp:1; }
    .badge-soft-success{ background:#d1e7dd; color:#0f5132; font-weight:600; }
    .badge-soft-done{ background:#cfe2ff; color:#084298; font-weight:600; }
    .badge-soft-green{ background:#d2f4d2; color:#0c8f0c; font-weight:600; }
    .badge-soft-warning{ background:#fff3cd; color:#664d03; font-weight:600; }
    .badge-soft-danger{ background:#f8d7da; color:#842029; font-weight:600; }
    /* booking card list styles (compact 2-col grid) */
    .booking-list { display: grid; gap: 1rem; grid-template-columns: 1fr; }
    @media(min-width:768px) { .booking-list { grid-template-columns: 1fr 1fr; } }
    /* make empty-state span full width of the grid so it won't be split into columns */
    @media(min-width:768px) { .booking-list .empty-state { grid-column: 1 / -1; } }
    .booking-card { border-radius: .5rem; }
    .booking-card .icon { width:40px; height:40px; display:inline-flex; align-items:center; justify-content:center; }
    .booking-card .card-body { padding: .75rem; }
    .booking-card h5 { font-size: 1rem; margin-bottom: .25rem; }
    .booking-card .text-muted.small { font-size: .78rem; }
    .booking-list .personnel-list { margin:0; padding-left:1rem; }
    .booking-list .personnel-list li { padding: 0.06rem 0; }
    .booking-card .actions .btn { padding: .25rem .5rem; }
    .booking-card .status-badge { display:block; text-align:right; }
    .booking-card .status-badge .badge{ font-weight:600; }

    /* Make actions wrap nicely on small screens */
    @media (max-width: 575.98px) {
        .booking-card .actions { gap: .5rem; }
        .booking-card .actions .btn { flex: 1 1 auto; }
    }

    /* Modal modern layout */
    #bookingDetailModal .modal-content{ border-radius: 1rem; }
    #bookingDetailModal .detail-header{ display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; margin-bottom: .75rem; }
    #bookingDetailModal .detail-title{ margin:0; font-size:1.1rem; font-weight:700; }
    #bookingDetailModal .detail-meta{ font-size:.9rem; color:#6b7280; }
    #bookingDetailModal .status-pill-sm{ display:inline-flex; align-items:center; gap:.35rem; padding:.25rem .55rem; border-radius:999px; font-weight:600; font-size:.76rem; background:#eef2ff; color:#1e40af; }
    /* Match modal status pill colors to card badges */
    #bookingDetailModal .status-pill-sm.badge-soft-success{ background:#d1e7dd; color:#0f5132; }
    #bookingDetailModal .status-pill-sm.badge-soft-warning{ background:#fff3cd; color:#664d03; }
    #bookingDetailModal .status-pill-sm.badge-soft-danger{ background:#f8d7da; color:#842029; }
    #bookingDetailModal .status-pill-sm.badge-soft-green{ background:#d2f4d2; color:#0c8f0c; }
    #bookingDetailModal .status-pill-sm.badge-soft-done{ background:#cfe2ff; color:#084298; }
    #bookingDetailModal .section-title{ font-size:.78rem; letter-spacing:.02em; text-transform:uppercase; color:#6b7280; margin-bottom:.35rem; font-weight:700; }
    #bookingDetailModal .section{ padding:.5rem 0; border-top:1px dashed #eceff3; }
    #bookingDetailModal .section:first-of-type{ border-top:none; }
    #bookingDetailModal .chips{ display:flex; flex-wrap:wrap; gap:.4rem; }
    #bookingDetailModal .chip{ background:#f3f4f6; color:#374151; border:1px solid #e5e7eb; border-radius:999px; padding:.25rem .55rem; font-size:.8rem; }
    #bookingDetailModal .snapshot-box{ background:#f8fafc; border:1px solid #eef0f2; border-radius:.75rem; padding:.75rem; }
    #bookingDetailModal .snapshot-box img{ max-height:360px; width:100%; height:auto; object-fit:contain; border-radius:.5rem; }
    #bookingDetailModal .snapshot-meta{ display:flex; flex-wrap:wrap; gap:.5rem; align-items:center; justify-content:space-between; margin-top:.5rem; }
    #bookingDetailModal .snapshot-meta .info{ font-size:.85rem; color:#6b7280; }
    #bookingDetailModal .snapshot-meta .btn{ padding:.25rem .5rem; }
</style>

<div id="asset-booking-admin" class="container-fluid px-4">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
        <div>
            <h3 class="mb-0 fw-semibold">Admin – Asset Bookings</h3>
        </div>
    </div>

    <div class="card border-0 mb-3 mx-0">
        <div class="card-body px-0 px-lg-3">
            <form method="get" class="row g-3 align-items-end">
                <div class="col-lg-6 col-md-8 col-12">
                    <label class="form-label small mb-0">Judul/PIC/Asset/Tujuan</label>
                    <div class="input-group mt-1">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input name="q" type="search" class="form-control" placeholder="Cari judul/PIC/asset/tujuan" value="{{ request()->query('q','') }}">
                    </div>
                </div>

                <div class="col-md-3 col-12">
                    <label class="form-label small mb-0">Tanggal Dibuat</label>
                    <input name="created_date" type="text" class="form-control mt-1 flatpickr-date" placeholder="Cari tanggal dibuat" value="{{ request()->query('created_date','') }}">
                </div>

                <div class="col-lg-3 col-md-12 col-12 d-flex gap-2 justify-content-end mt-3 mt-lg-0">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('asset.admin.bookings.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            @php
                $isPaginator = ($bookings instanceof \Illuminate\Contracts\Pagination\Paginator) || ($bookings instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) || (is_object($bookings) && method_exists($bookings,'links'));
                $hasPages = $isPaginator && is_object($bookings) && method_exists($bookings,'hasPages') ? $bookings->hasPages() : false;

                // status map for badges
                $statusMap = [
                    'approved'  => ['label'=>'Book','class'=>'badge-soft-success','icon'=>'bi-check-circle'],
                    'pending'   => ['label'=>'Pending','class'=>'badge-soft-warning','icon'=>'bi-hourglass'],
                    'in_use'    => ['label'=>'Sedang Digunakan','class'=>'badge-soft-success','icon'=>'bi-play-circle'],
                    'cancelled' => ['label'=>'Dibatalkan','class'=>'badge-soft-danger','icon'=>'bi-x-circle'],
                    'done'      => ['label'=>'Selesai','class'=>'badge-soft-green','icon'=>'bi-flag'],
                    'revision'  => ['label'=>'Revisi','class'=>'badge-soft-warning','icon'=>'bi-arrow-repeat'],
                    'unknown'   => ['label'=>'Unknown','class'=>'badge-soft-warning','icon'=>'bi-question-circle'],
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
                        $personnel = is_array($b->personnel) ? $b->personnel : ($b->personnel ? preg_split('/[,;]\s*/', $b->personnel) : []);
                        // map status to badge using $statusMap
                        $st = isset($b->status) ? strtolower($b->status) : null;
                        if ($st && isset($statusMap[$st])) {
                            $s = $statusMap[$st];
                        } elseif (isset($b->done) && $b->done) {
                            $s = $statusMap['done'];
                        } else {
                            $s = $statusMap['unknown'];
                        }

                        $s = $statusMap[$b->status] ?? ['label'=>ucfirst($b->status ?? '-'),'class'=>'badge-soft-secondary'];
                        if (!empty($b->is_overdue) && $b->is_overdue) {
                            $s = ['label' => 'Terlambat', 'class' => 'badge-soft-danger', 'icon' => 'bi-exclamation-triangle'];
                        }

                        // prepare snapshot url and meta if available
                        $snap = is_array($b->asset_snapshot)
                            ? $b->asset_snapshot
                            : (is_string($b->asset_snapshot) ? json_decode($b->asset_snapshot, true) : null);
                        $snapshotUrl = null; $snapshotOriginal = null; $snapshotUploadedAt = null;
                        if ($snap && !empty($snap['file'])) {
                            // use streaming route to ensure consistent serving
                            $snapshotUrl = route('asset.bookings.snapshot', $b);
                            $snapshotOriginal = $snap['original'] ?? basename($snap['file']);
                            $snapshotUploadedAt = $snap['uploaded_at'] ?? null;
                        }
                    @endphp

                    <div class="card mb-2 booking-card">
                        <div class="card-body d-flex align-items-start gap-3">
                            <div class="icon bg-light rounded-3 d-flex align-items-center justify-content-center">
                                <i class="bi bi-car-front-fill fs-5 text-primary"></i>
                            </div>

                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="text-muted small">#{{ $b->id }} • {{ $b->asset->name ?? '-' }}</div>
                                        <div class="fw-semibold">{{ $b->title ?? '-' }}</div>
                                        <div class="small text-muted">{{ \Carbon\Carbon::parse($b->start_at)->format('d M Y H:i') }} - {{ \Carbon\Carbon::parse($b->end_at)->format('d M Y H:i') }}</div>
                                        <div class="d-flex flex-wrap gap-3 small text-muted">
                                            <div>PIC : {{ $b->pic_name ? Str::title($b->pic_name) : '-' }}</div>
                                            <div>Personel : {{ count($personnel) }} Orang</div>
                                        </div>
                                    </div>

                                    <div class="status-badge ms-3">
                                        <span class="badge {{ $s['class'] }}"><i class="bi {{ $s['icon'] }} me-1"></i>{{ $s['label'] }}</span>
                                    </div>
                                </div>

                                <div class="mt-2">
                                    <div class="actions d-flex align-items-center gap-1 flex-wrap">
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#bookingDetailModal"
                                            data-id="{{ $b->id }}"
                                            data-asset="{{ $b->asset->name ?? '-' }}"
                                            data-title="{{ $b->title ?? '-' }}"
                                            data-purpose="{{ $b->purpose ?? '-' }}"
                                            data-pic="{{ $b->pic_name ? Str::title($b->pic_name) : '-' }}"
                                            data-destination="{{ $b->destination_text ?? '-' }}"
                                            data-created="{{ \Carbon\Carbon::parse($b->created_at)->format('d-m-Y H:i') }}"
                                            data-start="{{ \Carbon\Carbon::parse($b->start_at)->format('d-m-Y H:i') }}"
                                            data-end="{{ \Carbon\Carbon::parse($b->end_at)->format('d-m-Y H:i') }}"
                                            data-checked-out="{{ $b->checked_out_at ? \Carbon\Carbon::parse($b->checked_out_at)->format('d-m-Y H:i') : '' }}"
                                            data-returned="{{ $b->returned_at ? \Carbon\Carbon::parse($b->returned_at)->format('d-m-Y H:i') : '' }}"
                                            data-overdue="{{ (int)($b->is_overdue ?? 0) }}"
                                            data-end-diff="{{ $b->end_diff_minutes ?? '' }}"
                                            data-personnel='{{ json_encode($personnel) }}'
                                            data-status-label="{{ $s['label'] }}"
                                            data-status-icon="{{ $s['icon'] }}"
                                            data-status-class="{{ $s['class'] }}"
                                            data-snapshot-url="{{ $snapshotUrl ?? '' }}"
                                            data-snapshot-original="{{ $snapshotOriginal ?? '' }}"
                                            data-snapshot-time="{{ $snapshotUploadedAt ?? '' }}"
                                            data-revision-notes="{{ e($b->revision_notes ?? '') }}"
                                            data-revision-count="{{ $b->revision_count ?? 0 }}"
                                                data-user="{{ $b->user->name ? Str::title($b->user->name) : ($b->user->email ?? '-') }}"
                                        >Details</button>

                                        <a href="{{ route('asset.admin.bookings.edit', $b) }}" class="btn btn-sm btn-light border btn-icon" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @if($b->status === 'done')
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#revisionModal"
                                            data-revision-url="{{ route('asset.admin.bookings.revision', $b) }}"
                                            data-booking-title="{{ e($b->title ?? '-') }}" title="Revisi">
                                            <i class="bi bi-arrow-repeat"></i> Revisi
                                        </button>
                                        @elseif($b->status === 'revision')
                                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#cancelRevisionModal"
                                            data-cancel-url="{{ route('asset.admin.bookings.cancel_revision', $b) }}"
                                            data-booking-title="{{ e($b->title ?? '-') }}" title="Batalkan Revisi">
                                            <i class="bi bi-x-circle"></i> Batalkan Revisi
                                        </button>
                                        @endif
                                        <form action="{{ route('asset.admin.bookings.destroy', $b) }}" method="post" class="d-inline swal-confirm" data-confirm="Hapus booking ini?">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-light border btn-icon" title="Hapus">
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
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.bootstrap && bootstrap.Tooltip) {
            document.querySelectorAll('#asset-booking-admin [title]').forEach(function (el) {
                new bootstrap.Tooltip(el);
            });
        }
    });
</script>
        <!-- Booking detail modal -->
        <div class="modal fade" id="bookingDetailModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Booking Detail</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="detail-header">
                            <div>
                                <div class="detail-title" id="md-title">-</div>
                                <div class="detail-meta">ID <span id="md-id">-</span> • Asset: <span id="md-asset">-</span></div>
                            </div>
                            <div>
                                <span id="md-status" class="status-pill-sm" style="display:none;"></span>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="section">
                                    <div class="section-title">Tujuan</div>
                                    <div id="md-purpose">-</div>
                                </div>
                                    <div class="section">
                                        <div class="section-title">User Booking</div>
                                        <div id="md-user">-</div>
                                    </div>
                                <div class="section">
                                    <div class="section-title">PIC</div>
                                    <div id="md-userpic">-</div>
                                </div>
                                <div class="section">
                                    <div class="section-title">Personnel</div>
                                    <div id="md-personnel" class="chips">-</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="section">
                                    <div class="section-title">Destinasi</div>
                                    <div id="md-destination">-</div>
                                </div>
                                <div class="section">
                                    <div class="section-title">Timestamps</div>
                                    <div class="row g-2 small" id="md-timestamps">
                                        <div class="col-5 text-muted">Dibuat</div>
                                        <div class="col-7" id="md-created">-</div>
                                        <div class="col-5 text-muted">Mulai</div>
                                        <div class="col-7" id="md-start">-</div>
                                        <div class="col-5 text-muted">Selesai (Rencana)</div>
                                        <div class="col-7" id="md-end">-</div>
                                        <div class="col-5 text-muted">Check-out</div>
                                        <div class="col-7" id="md-checked-out">-</div>
                                        <div class="col-5 text-muted">Kembali</div>
                                        <div class="col-7" id="md-returned">-</div>
                                        <div class="col-5 text-muted">Terlambat</div>
                                        <div class="col-7" id="md-overdue">-</div>
                                        <div class="col-5 text-muted">End Diff</div>
                                        <div class="col-7" id="md-end-diff">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="section">
                            <div class="section-title">Bukti Foto</div>
                            <div id="md-snapshot" class="snapshot-box"></div>
                        </div>

                        <div class="section" id="md-revision-section" style="display:none;">
                            <div class="section-title">Info Revisi</div>
                            <div class="alert alert-warning small mb-0">
                                <div><strong>Jumlah Revisi:</strong> <span id="md-revision-count">0</span>x</div>
                                <div id="md-revision-notes-row" style="display:none;"><strong>Catatan:</strong> <span id="md-revision-notes-text"></span></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
                var bookingModal = document.getElementById('bookingDetailModal');
                if (!bookingModal) return;

                function parseDMYHI(str) {
                    if (!str) return null;
                    try {
                        var parts = str.trim().split(' ');
                        if (parts.length < 2) return null;
                        var dmy = parts[0].split('-');
                        var hm = parts[1].split(':');
                        if (dmy.length !== 3 || hm.length < 2) return null;
                        var d = parseInt(dmy[0], 10);
                        var m = parseInt(dmy[1], 10) - 1;
                        var y = parseInt(dmy[2], 10);
                        var h = parseInt(hm[0], 10);
                        var i = parseInt(hm[1], 10);
                        if (isNaN(d) || isNaN(m) || isNaN(y) || isNaN(h) || isNaN(i)) return null;
                        return new Date(y, m, d, h, i, 0, 0);
                    } catch (e) { return null; }
                }

                function formatDiffMinutes(mins) {
                    if (mins === null || mins === undefined || isNaN(mins)) return '-';
                    var sign = mins < 0 ? '-' : '';
                    var abs = Math.abs(mins);
                    var days = Math.floor(abs / (60 * 24));
                    var hours = Math.floor((abs % (60 * 24)) / 60);
                    var minutes = abs % 60;
                    var parts = [];
                    if (days) parts.push(days + ' hari');
                    if (days || hours) parts.push(hours + ' jam');
                    parts.push(minutes + ' menit');
                    return sign + parts.join(' ');
                }

                bookingModal.addEventListener('show.bs.modal', function (event) {
                        var button = event.relatedTarget;
                        if (!button) return;

                        document.getElementById('md-id').textContent = button.getAttribute('data-id');
                        document.getElementById('md-asset').textContent = button.getAttribute('data-asset');
                        document.getElementById('md-title').textContent = button.getAttribute('data-title');
                        document.getElementById('md-purpose').textContent = button.getAttribute('data-purpose');
                            document.getElementById('md-user').textContent = button.getAttribute('data-user') || '-';
                        document.getElementById('md-userpic').textContent = button.getAttribute('data-pic') || '-';
                        // status pill (optional)
                        var statusLabel = button.getAttribute('data-status-label') || '';
                        var statusIcon = button.getAttribute('data-status-icon') || '';
                        var statusEl = document.getElementById('md-status');
                        var statusClass = button.getAttribute('data-status-class') || '';
                        // reset classes to base, then add badge class to match card
                        statusEl.className = 'status-pill-sm';
                        if (statusLabel) {
                            statusEl.classList.add(statusClass);
                            statusEl.style.display = 'inline-flex';
                            statusEl.innerHTML = (statusIcon ? '<i class="bi ' + statusIcon + '"></i> ' : '') + statusLabel;
                        } else {
                            statusEl.style.display = 'none';
                            statusEl.textContent = '';
                        }
                        document.getElementById('md-destination').textContent = button.getAttribute('data-destination');
                        // timestamps (separated rows)
                        document.getElementById('md-created').textContent = button.getAttribute('data-created') || '-';
                        document.getElementById('md-start').textContent = button.getAttribute('data-start') || '-';
                        document.getElementById('md-end').textContent = button.getAttribute('data-end') || '-';
                        document.getElementById('md-checked-out').textContent = button.getAttribute('data-checked-out') || '-';
                        document.getElementById('md-returned').textContent = button.getAttribute('data-returned') || '-';
                        // compute lateness from planned end vs actual returned (not from DB)
                        var endStr = button.getAttribute('data-end') || '';
                        var retStr = button.getAttribute('data-returned') || '';
                        var endDt = parseDMYHI(endStr);
                        var retDt = parseDMYHI(retStr);
                        if (endDt && retDt) {
                            var diffMin = Math.round((retDt.getTime() - endDt.getTime()) / 60000);
                            document.getElementById('md-overdue').textContent = diffMin > 0 ? 'Ya' : 'Tidak';
                            document.getElementById('md-end-diff').textContent = formatDiffMinutes(diffMin);
                        } else {
                            document.getElementById('md-overdue').textContent = '-';
                            document.getElementById('md-end-diff').textContent = '-';
                        }

                        // personnel is JSON encoded
                        var p = [];
                        try { p = JSON.parse(button.getAttribute('data-personnel') || '[]'); } catch(e) { p = []; }
                        var out = '';
                        if (p.length) {
                                out = '<ul class="mb-0">';
                                p.forEach(function(name){ out += '<li>' + (name||'') + '</li>'; });
                                out += '</ul>';
                        } else { out = '-'; }
                        document.getElementById('md-personnel').innerHTML = out;

            // snapshot preview (if any)
            var snapUrl = button.getAttribute('data-snapshot-url') || '';
            var snapOriginal = button.getAttribute('data-snapshot-original') || '';
            var snapTime = button.getAttribute('data-snapshot-time') || '';
            var snapEl = document.getElementById('md-snapshot');
            if (snapUrl) {
                var metaText = [];
                if (snapOriginal) metaText.push('Nama file: ' + snapOriginal);
                if (snapTime) metaText.push('Diunggah: ' + snapTime);
                var metaInfo = metaText.join(' • ');
                snapEl.innerHTML = ''
                    + '<a href="' + snapUrl + '" target="_blank" rel="noopener">'
                    + '  <img src="' + snapUrl + '" alt="Bukti foto">'
                    + '</a>'
                    + '<div class="snapshot-meta">'
                    + '  <div class="info">' + (metaInfo || '') + '</div>'
                    + '  <div class="actions">'
                    + '    <a class="btn btn-sm btn-outline-primary" href="' + snapUrl + '" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> Buka</a>'
                    + '    <a class="btn btn-sm btn-outline-secondary" href="' + snapUrl + '" download><i class="bi bi-download"></i> Unduh</a>'
                    + '    <button type="button" class="btn btn-sm btn-outline-secondary js-copy-snapshot" data-url="' + snapUrl + '"><i class="bi bi-clipboard"></i> Salin Link</button>'
                    + '  </div>'
                    + '</div>';

                var copyBtn = snapEl.querySelector('.js-copy-snapshot');
                if (copyBtn && navigator.clipboard) {
                    copyBtn.addEventListener('click', function(){
                        var url = copyBtn.getAttribute('data-url');
                        navigator.clipboard.writeText(url).then(function(){
                            var prev = copyBtn.innerHTML;
                            copyBtn.innerHTML = '<i class="bi bi-check2"></i> Tersalin';
                            setTimeout(function(){ copyBtn.innerHTML = prev; }, 1500);
                        });
                    });
                }
            } else {
                snapEl.innerHTML = '<span class="text-muted">-</span>';
            }

            // Revision info
            var revCount = parseInt(button.getAttribute('data-revision-count') || '0', 10);
            var revNotes = button.getAttribute('data-revision-notes') || '';
            var revSection = document.getElementById('md-revision-section');
            var revCountEl = document.getElementById('md-revision-count');
            var revNotesRow = document.getElementById('md-revision-notes-row');
            var revNotesText = document.getElementById('md-revision-notes-text');
            if (revCount > 0) {
                revSection.style.display = 'block';
                revCountEl.textContent = revCount;
                if (revNotes) {
                    revNotesRow.style.display = 'block';
                    revNotesText.textContent = revNotes;
                } else {
                    revNotesRow.style.display = 'none';
                }
            } else {
                revSection.style.display = 'none';
            }
                });
        });
        </script>

        <!-- Cancel Revision Modal -->
        <div class="modal fade" id="cancelRevisionModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form id="cancelRevisionForm" method="post">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="cancelRevisionModalTitle">Batalkan Revisi</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-secondary small mb-0">
                                <i class="bi bi-info-circle me-1"></i>
                                Booking <strong id="cancelRevisionBookingTitle"></strong> akan dikembalikan ke status <strong>Selesai</strong> dan catatan revisi akan dihapus.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Kembali</button>
                            <button type="submit" class="btn btn-secondary"><i class="bi bi-x-circle me-1"></i>Batalkan Revisi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            var cancelRevisionModal = document.getElementById('cancelRevisionModal');
            if (!cancelRevisionModal) return;
            cancelRevisionModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var action = button.getAttribute('data-cancel-url');
                var title = button.getAttribute('data-booking-title') || '';
                var form = document.getElementById('cancelRevisionForm');
                var titleEl = document.getElementById('cancelRevisionBookingTitle');
                if (form && action) form.action = action;
                if (titleEl) titleEl.textContent = title;
            });
        });
        </script>

        <!-- Revision Modal (admin sets booking to revision status) -->
        <div class="modal fade" id="revisionModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form id="revisionForm" method="post">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="revisionModalTitle">Revisi Booking</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-warning small mb-3">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Dengan merevisi booking ini, status akan berubah menjadi <strong>Revisi</strong> dan user harus mengunggah ulang bukti foto.
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Catatan Revisi (opsional)</label>
                                <textarea name="revision_notes" class="form-control" rows="3" placeholder="Jelaskan alasan revisi, misal: foto speedometer tidak terlihat jelas..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-warning"><i class="bi bi-arrow-repeat me-1"></i>Revisi Booking</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            var revisionModal = document.getElementById('revisionModal');
            if (!revisionModal) return;
            revisionModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var action = button.getAttribute('data-revision-url');
                var title = button.getAttribute('data-booking-title') || '';
                var form = document.getElementById('revisionForm');
                var modalTitle = document.getElementById('revisionModalTitle');
                if (form && action) form.action = action;
                if (modalTitle) modalTitle.textContent = 'Revisi Booking: ' + title;
                // Reset textarea
                var textarea = form.querySelector('textarea[name="revision_notes"]');
                if (textarea) textarea.value = '';
            });
        });
        </script>
@endsection

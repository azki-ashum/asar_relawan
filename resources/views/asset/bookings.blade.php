@extends('layouts.app')

@section('title', 'Peminjaman Kendaraan')

@section('content')
<!-- Local styles: reuse admin table look for bookings list -->
<style>
    /* Scope to this page */
    #booking-asset .modern-table-wrapper { max-height: 60vh; }
    #booking-asset .modern-table thead th{
        position: sticky; top: 0; z-index: 2;
        background: #f8f9fa;
        box-shadow: inset 0 -1px 0 rgba(0,0,0,.08);
        text-align: center;
    }

    /* center all table cells to match headers */
    #booking-asset .modern-table td { text-align: center; }

    #booking-asset .modern-table :where(td, th){ padding: .9rem 1rem !important; vertical-align: middle; }
    #booking-asset .truncate-1, #booking-asset .truncate-2{ display:-webkit-box; -webkit-box-orient:vertical; overflow:hidden; }
    /* Separator between rows instead of zebra */
    #booking-asset .modern-table tbody td { border-top: 1px solid #eef0f2; }
    #booking-asset .truncate-1{ -webkit-line-clamp:1; }
    #booking-asset .truncate-2{ -webkit-line-clamp:2; }
    #booking-asset .badge-soft-success   { background:#d1e7dd; color:#0f5132; font-weight:600; }
    #booking-asset .badge-soft-warning   { background:#fff3cd; color:#664d03; font-weight:600; }
    #booking-asset .badge-soft-danger    { background:#f8d7da; color:#842029; font-weight:600; }
    #booking-asset .badge-soft-green     { background:#d2f4d2; color:#0c8f0c; font-weight:600; }
    #booking-asset .badge-soft-secondary { background:#e2e3e5; color:#41464b; font-weight:600; }
    #booking-asset .btn-icon{ width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center; }
</style>

@php
    // fallback URLs: if the original room booking named routes exist we keep them,
    // otherwise point to /asset/* paths for the dummy asset flow
    // Prefer asset named routes when available, then fallback to room routes, then to /asset paths
    if (Route::has('asset.bookings.create')) {
        $createUrl = route('asset.bookings.create');
    } elseif (Route::has('bookings.create')) {
        $createUrl = route('bookings.create');
    } else {
        $createUrl = url('/asset/bookings/create');
    }

    if (Route::has('asset.bookings.index')) {
        $indexUrl = route('asset.bookings.index');
    } elseif (Route::has('bookings.index')) {
        $indexUrl = route('bookings.index');
    } else {
        $indexUrl = url('/asset/bookings');
    }
    // Ensure $userBookings exists to avoid runtime errors in the view when controller doesn't provide it
    if (!isset($userBookings)) {
        $userBookings = collect();
    }
@endphp

<div id="booking-asset" class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Booking Anda</h3>
        @if(isset($pendingRevisions) && $pendingRevisions->isNotEmpty())
            <button class="btn btn-primary disabled" disabled title="Selesaikan revisi terlebih dahulu">
                <i class="bi bi-plus"></i> Book
            </button>
        @else
            <a href="{{ $createUrl }}" class="btn btn-primary">
                <i class="bi bi-plus"></i> Book
            </a>
        @endif
    </div>

    @if(isset($pendingRevisions) && $pendingRevisions->isNotEmpty())
    <div class="alert alert-warning d-flex align-items-start gap-2 mb-3">
        <i class="bi bi-exclamation-triangle-fill fs-5 mt-1 flex-shrink-0"></i>
        <div>
            <strong>Booking dalam status revisi.</strong>
            Anda tidak dapat membuat booking baru sebelum menyelesaikan semua revisi berikut:
            <ul class="mb-1 mt-1">
                @foreach($pendingRevisions as $pendingRevision)
                <li>
                    <strong>{{ $pendingRevision->asset->name ?? 'kendaraan' }}</strong> (ID #{{ $pendingRevision->id }})
                    @if($pendingRevision->revision_notes)
                        <br><span class="text-muted small">Catatan admin: {{ $pendingRevision->revision_notes }}</span>
                    @endif
                </li>
                @endforeach
            </ul>
            Gulir ke bawah untuk menemukan booking tersebut dan klik <strong>"Upload Ulang"</strong>.
        </div>
    </div>
    @endif

    <div class="card mb-3 border-0">
        <div class="card-body px-0 px-lg-3">
            <form method="get" class="row g-2 align-items-end">
                <div class="col-md-6 col-12">
                    <label class="form-label small mb-1">Nama Kegiatan</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input name="title" type="search" class="form-control" placeholder="Cari nama kegiatan" value="{{ request()->query('title', '') }}">
                    </div>
                </div>

                <div class="col-md-3 col-12">
                    <label class="form-label small mb-1">Tanggal Mulai</label>
                    <input name="start_date" type="text" class="form-control flatpickr-date" placeholder="Cari tanggal mulai" value="{{ request()->query('start_date', '') }}">
                </div>

                <div class="col-md-3 col-12 d-flex gap-2 justify-content-end mt-3 mt-lg-0">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ $indexUrl }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    @if(isset($userBookings) && $userBookings->count() === 0)
        @php
            $hasFilters = request()->query('title', '') !== '' || request()->query('start_date', '') !== '';
        @endphp
        <div class="alert alert-secondary d-flex align-items-center justify-content-between">
            <div>
                @if($hasFilters)
                    Tidak ada peminjaman kendaraan yang sesuai pencarian.
                @else
                    Anda belum memiliki peminjaman kendaraan.
                @endif
            </div>
        </div>
    @else
        <div class="table-responsive modern-table-wrapper">
            @php
                $statusMap = [
                    'approved'  => ['label'=>'Book','class'=>'badge-soft-success','icon'=>'bi-check-circle'],
                    'pending'   => ['label'=>'Pending','class'=>'badge-soft-warning','icon'=>'bi-hourglass'],
                    'in_use'    => ['label'=>'Sedang Digunakan','class'=>'badge-soft-success','icon'=>'bi-play-circle'],
                    'cancelled' => ['label'=>'Dibatalkan','class'=>'badge-soft-danger','icon'=>'bi-x-circle'],
                    'done'      => ['label'=>'Selesai','class'=>'badge-soft-green','icon'=>'bi-flag'],
                    'revision'  => ['label'=>'Revisi','class'=>'badge-soft-warning','icon'=>'bi-arrow-repeat'],
                ];
            @endphp
                </script>
                <script>
                // Fill global complete modal with correct action and title when opened
                document.addEventListener('DOMContentLoaded', function() {
                    var completeModal = document.getElementById('completeModal');
                    if (!completeModal) return;
                    completeModal.addEventListener('show.bs.modal', function (event) {
                        var button = event.relatedTarget; // Button that triggered the modal
                        var action = button.getAttribute('data-complete-url');
                        var title = button.getAttribute('data-booking-title') || 'Tandai selesai';
                        var form = document.getElementById('completeForm');
                        var modalTitle = document.getElementById('completeModalTitle');
                        if (form && action) {
                            form.action = action;
                        }
                        if (modalTitle) modalTitle.textContent = 'Tandai selesai: ' + title;
                    });
                });
                </script>
            <table class="table table-hover align-middle mb-0 modern-table">
                <thead>
                    <tr>
                        <th class="text-muted fw-semibold">ID Transaksi</th>
                        <th class="text-muted fw-semibold">Kendaraan</th>
                        <th class="text-muted fw-semibold">Nama Kegiatan</th>
                        <th class="text-muted fw-semibold">Mulai</th>
                        <th class="text-muted fw-semibold">Selesai</th>
                        <th class="text-muted fw-semibold text-center" style="min-width:130px">Status</th>
                        <th class="text-muted fw-semibold text-center" style="min-width:110px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($userBookings as $b)
                        @php
                            $s = $statusMap[$b->status] ?? ['label'=>ucfirst($b->status ?? '-'),'class'=>'badge-soft-secondary'];
                            if (!empty($b->is_overdue) && $b->is_overdue) {
                                $s = ['label' => 'Terlambat', 'class' => 'badge-soft-danger', 'icon' => 'bi-exclamation-triangle'];
                            }
                        @endphp
                        <tr>
                            <td class="text-nowrap text-muted small">#{{ $b->id }}</td>
                            <td>{{ $b->asset->name ?? $b->asset_name ?? '-' }}</td>
                            <td>{{ $b->title ?? '-' }}</td>
                            <td class="text-nowrap">{{ \Carbon\Carbon::parse($b->start_at)->format('d-m-Y H:i') }}</td>
                            <td class="text-nowrap">{{ \Carbon\Carbon::parse($b->end_at)->format('d-m-Y H:i') }}</td>
                            <td class="text-center" style="width:120px;">
                                <span class="badge w-100 {{ $s['class'] }} py-2">
                                    <i class="bi {{ $s['icon'] }} me-1"></i>{{ $s['label'] }}
                                </span>
                            </td>
                            <td class="text-center" style="min-width:110px">
                                @if($b->status === 'revision')
                                    {{-- Revision: user must re-upload photo --}}
                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#resubmitModal"
                                        data-resubmit-url="{{ route('asset.bookings.resubmit', $b) }}"
                                        data-booking-title="{{ e($b->title ?? '-') }}"
                                        data-revision-notes="{{ e($b->revision_notes ?? '') }}">
                                        <i class="bi bi-arrow-repeat"></i> Upload Ulang
                                    </button>
                                @elseif(! in_array($b->status, ['cancelled', 'done']))
                                    @php
                                        // Prefer asset-specific named routes, then room routes, then URL fallbacks
                                        if (Route::has('asset.bookings.edit')) {
                                            $editUrl = route('asset.bookings.edit', $b);
                                        } elseif (Route::has('bookings.edit')) {
                                            $editUrl = route('bookings.edit', $b);
                                        } else {
                                            $editUrl = url("/asset/bookings/{$b->id}/edit");
                                        }

                                        if (Route::has('asset.bookings.complete')) {
                                            $completeUrl = route('asset.bookings.complete', $b);
                                        } elseif (Route::has('bookings.complete')) {
                                            $completeUrl = route('bookings.complete', $b);
                                        } else {
                                            $completeUrl = url("/asset/bookings/{$b->id}/complete");
                                        }

                                        if (Route::has('asset.bookings.cancel')) {
                                            $cancelUrl = route('asset.bookings.cancel', $b);
                                        } elseif (Route::has('bookings.cancel')) {
                                            $cancelUrl = route('bookings.cancel', $b);
                                        } else {
                                            $cancelUrl = url("/asset/bookings/{$b->id}/cancel");
                                        }
                                    @endphp

                                    <a href="{{ $editUrl }}" class="btn btn-sm btn-primary me-1">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>

                                    {{-- Open modal to upload snapshot before completing (single global modal will be used) --}}
                                    <button class="btn btn-sm btn-success me-1" data-bs-toggle="modal" data-bs-target="#completeModal" 
                                        data-complete-url="{{ $completeUrl }}" data-booking-title="{{ e($b->title ?? '-') }}">
                                        <i class="bi bi-flag"></i> Selesai
                                    </button>

                                    <form action="{{ $cancelUrl }}" method="post" class="d-inline swal-confirm" data-confirm="Batalkan booking ini?">
                                        @csrf
                                        <button class="btn btn-sm btn-danger">
                                            <i class="bi bi-x"></i> Batal
                                        </button>
                                    </form>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if(isset($userBookings) && method_exists($userBookings, 'links'))
            <div class="d-flex justify-content-end mt-3">
                {{ $userBookings->links('pagination::bootstrap-5') }}
            </div>
        @endif
    @endif
</div>

<!-- Global Complete Modal (single instance to avoid clipping/duplicate text issues) -->
<div class="modal fade" id="completeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="completeForm" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="completeModalTitle">Tandai selesai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Foto Bukti (wajib)</label>
                        <input type="file" name="asset_snapshot_file" accept="image/*" class="form-control" required>
                        <div class="form-text small text-muted">
                            <ul class="mb-0" style="padding-left:1rem;">
                                <li>Foto harus menampilkan speedometer (jarak/tempuh).</li>
                                <li>Foto harus menunjukkan posisi/tanda level bahan bakar (fuel).</li>
                                <li>Format: JPG/PNG. Ukuran maksimal 5MB.</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Tandai Selesai</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Resubmit Modal (user re-uploads photo after admin revision) -->
<div class="modal fade" id="resubmitModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="resubmitForm" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="resubmitModalTitle">Upload Ulang Bukti Foto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning small mb-3">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Admin meminta revisi pada bukti foto booking ini. Silakan unggah ulang foto yang sesuai.
                    </div>
                    <div id="resubmitRevisionNotes" class="alert alert-info small mb-3" style="display:none;">
                        <strong>Catatan dari Admin:</strong>
                        <div id="resubmitRevisionNotesText"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Foto Bukti Baru (wajib)</label>
                        <input type="file" name="asset_snapshot_file" accept="image/*" class="form-control" required>
                        <div class="form-text small text-muted">
                            <ul class="mb-0" style="padding-left:1rem;">
                                <li>Foto harus menampilkan speedometer (jarak/tempuh).</li>
                                <li>Foto harus menunjukkan posisi/tanda level bahan bakar (fuel).</li>
                                <li>Format: JPG/PNG. Ukuran maksimal 5MB.</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning"><i class="bi bi-arrow-repeat me-1"></i>Upload Ulang & Selesaikan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var resubmitModal = document.getElementById('resubmitModal');
    if (!resubmitModal) return;
    resubmitModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var action = button.getAttribute('data-resubmit-url');
        var title = button.getAttribute('data-booking-title') || '';
        var notes = button.getAttribute('data-revision-notes') || '';
        var form = document.getElementById('resubmitForm');
        var modalTitle = document.getElementById('resubmitModalTitle');
        var notesContainer = document.getElementById('resubmitRevisionNotes');
        var notesText = document.getElementById('resubmitRevisionNotesText');

        if (form && action) form.action = action;
        if (modalTitle) modalTitle.textContent = 'Upload Ulang: ' + title;

        // Show admin revision notes if available
        if (notes && notesContainer && notesText) {
            notesContainer.style.display = 'block';
            notesText.textContent = notes;
        } else if (notesContainer) {
            notesContainer.style.display = 'none';
        }

        // Reset file input
        var fileInput = form.querySelector('input[type="file"]');
        if (fileInput) fileInput.value = '';
    });
});
</script>

@push('scripts')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        events: '/asset/api/bookings/events',
        eventDisplay: 'block',
        eventContent: function(arg) {
            return { html: '<div><strong>'+arg.event.title+'</strong><div style="font-size:0.85em">'+(arg.event.extendedProps.purpose||'')+'</div></div>' };
        }
    });
    calendar.render();
});
</script>
@endpush

@endsection

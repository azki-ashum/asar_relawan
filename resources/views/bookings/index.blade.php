@extends('layouts.app')

@section('title', 'Booking Anda')

@section('content')
<!-- Local styles: reuse admin table look for bookings list -->
<style>
    /* Scope to this page */
    #booking-user .modern-table-wrapper { max-height: 60vh; }
    #booking-user .modern-table thead th{
        position: sticky; top: 0; z-index: 2;
        background: #f8f9fa;
        box-shadow: inset 0 -1px 0 rgba(0,0,0,.08);
        text-align: center;
    }

    /* center all table cells to match headers */
    #booking-user .modern-table td { text-align: center; }

    #booking-user .modern-table :where(td, th){ padding: .9rem 1rem !important; vertical-align: middle; }
    #booking-user .truncate-1, #booking-user .truncate-2{ display:-webkit-box; -webkit-box-orient:vertical; overflow:hidden; }
    /* Separator between rows instead of zebra */
    #booking-user .modern-table tbody td { border-top: 1px solid #eef0f2; }
    #booking-user .truncate-1{ -webkit-line-clamp:1; }
    #booking-user .truncate-2{ -webkit-line-clamp:2; }
    #booking-user .badge-soft-success   { background:#d1e7dd; color:#0f5132; font-weight:600; }
    #booking-user .badge-soft-warning   { background:#fff3cd; color:#664d03; font-weight:600; }
    #booking-user .badge-soft-danger    { background:#f8d7da; color:#842029; font-weight:600; }
    #booking-user .badge-soft-green     { background:#d2f4d2; color:#0c8f0c; font-weight:600; }
    #booking-user .badge-soft-secondary { background:#e2e3e5; color:#41464b; font-weight:600; }
    #booking-user .btn-icon{ width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center; }
</style>

<div id="booking-user" class="container">
    {{-- <h1>Booking Calendar</h1> --}}
    
    {{-- <div id='calendar'></div> --}}
    
    {{-- <hr> --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Booking Anda</h3>
        <a href="{{ route('bookings.create') }}" class="btn btn-primary">
            <i class="bi bi-plus"></i> Book
        </a>
    </div>

    <div class="card mb-3 border-0">
        <div class="card-body px-0 px-lg-3">
            <form method="get" class="row g-2 align-items-end">
                <div class="col-md-6 col-12">
                    <label class="form-label small mb-1">Judul</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input name="title" type="search" class="form-control" placeholder="Cari judul booking" value="{{ request()->query('title', '') }}">
                    </div>
                </div>

                <div class="col-md-3 col-12">
                    <label class="form-label small mb-1">Tanggal Mulai</label>
                    <input name="start_date" type="text" class="form-control flatpickr-date" placeholder="Cari tanggal mulai" value="{{ request()->query('start_date', '') }}">
                </div>

                <div class="col-md-3 col-12 d-flex gap-2 justify-content-end mt-3 mt-lg-0">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('bookings.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    @if($userBookings->count() === 0)
        <div class="alert alert-secondary">Anda belum memiliki booking.</div>
    @else
        <div class="table-responsive modern-table-wrapper">
            @php
                $statusMap = [
                    'approved'  => ['label'=>'Book','class'=>'badge-soft-success','icon'=>'bi-check-circle'],
                    'pending'   => ['label'=>'Pending','class'=>'badge-soft-warning','icon'=>'bi-hourglass'],
                    'in_use'    => ['label'=>'Sedang Digunakan','class'=>'badge-soft-success','icon'=>'bi-play-circle'],
                    'cancelled' => ['label'=>'Dibatalkan','class'=>'badge-soft-danger','icon'=>'bi-x-circle'],
                    'done'      => ['label'=>'Selesai','class'=>'badge-soft-green','icon'=>'bi-flag'],
                ];
            @endphp
            <table class="table table-hover align-middle mb-0 modern-table">
                <thead>
                    <tr>
                        <th class="text-muted fw-semibold">Ruang</th>
                        <th class="text-muted fw-semibold">Judul</th>
                        <th class="text-muted fw-semibold">Mulai</th>
                        <th class="text-muted fw-semibold">Selesai</th>
                        <th class="text-muted fw-semibold text-center" style="min-width:130px">Status</th>
                        <th class="text-muted fw-semibold text-center" style="min-width:110px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($userBookings as $b)
                        @php $s = $statusMap[$b->status] ?? ['label'=>ucfirst($b->status ?? '-'),'class'=>'badge-soft-secondary']; @endphp
                        <tr>
                            <td>{{ $b->room->name ?? '-' }}</td>
                            <td>{{ $b->title ?? '-' }}</td>
                            <td class="text-nowrap">{{ \Carbon\Carbon::parse($b->start_at)->format('d-m-Y H:i') }}</td>
                            <td class="text-nowrap">{{ \Carbon\Carbon::parse($b->end_at)->format('d-m-Y H:i') }}</td>
                            <td class="text-center" style="width:120px;">
                                <span class="badge w-100 {{ $s['class'] }} py-2">
                                    <i class="bi {{ $s['icon'] }} me-1"></i>{{ $s['label'] }}
                                </span>
                            </td>
                            <td class="text-center" style="min-width:110px">
                                @if(! in_array($b->status, ['cancelled', 'done']))
                                    <a href="{{ route('bookings.edit', $b) }}" class="btn btn-sm btn-primary me-1">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>

                                    {{-- Complete (Selesai) button: user may mark their booking as done when it's active/approved/pending --}}
                                    <form action="{{ route('bookings.complete', $b) }}" method="post" class="d-inline swal-confirm me-1" data-confirm="Tandai booking ini sebagai selesai?">
                                        @csrf
                                        <button class="btn btn-sm btn-success">
                                            <i class="bi bi-flag"></i> Selesai
                                        </button>
                                    </form>

                                    <form action="{{ route('bookings.cancel', $b) }}" method="post" class="d-inline swal-confirm" data-confirm="Batalkan booking ini?">
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

        @if(method_exists($userBookings, 'links'))
            <div class="d-flex justify-content-end mt-3">
                {{ $userBookings->links('pagination::bootstrap-5') }}
            </div>
        @endif
    @endif
</div>

@push('scripts')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        events: '/api/bookings/events',
        eventDisplay: 'block',
        eventContent: function(arg) {
            return { html: '<div><strong>'+arg.event.title+'</strong><div style="font-size:0.85em">'+arg.event.extendedProps.purpose+'</div></div>' };
        }
    });
    calendar.render();
});
</script>
@endpush

@endsection

@extends('layouts.app')

@section('title', 'Edit Booking')

@section('content')
<div class="container">
    <div class="row">
    @include('partials.dashboard-cards', ['colClass' => 'order-2 order-md-1', 'showSummaries' => false])

    <div class="col-md-8 order-1 order-md-2">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-0">Edit Booking</h5>
                    </div>
                    <div>
                        <a href="{{ route('admin.rooms.index') }}" class="btn btn-light">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>

                <div class="card-body">

                    <form id="bookingEditForm" action="{{ route('bookings.update', $booking) }}" method="post">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Ruangan</label>
                    <select name="room_id" class="form-select @error('room_id') is-invalid @enderror">
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}" {{ old('room_id', $booking->room_id) == $room->id ? 'selected' : '' }}>{{ $room->name }}</option>
                        @endforeach
                    </select>
                    @error('room_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Judul Kegiatan</label>
                    <input name="title" type="text" class="form-control" value="{{ old('title', $booking->title) }}">
                    @error('title') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Tanggal & Waktu Mulai</label>
                        <input id="start_picker" type="text" class="form-control" placeholder="YYYY-MM-DD HH:MM" value="{{ old('start_at') ? \Carbon\Carbon::parse(old('start_at'))->format('Y-m-d H:i') : $booking->start_at->format('Y-m-d H:i') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tanggal & Waktu Selesai</label>
                        <input id="end_picker" type="text" class="form-control" placeholder="YYYY-MM-DD HH:MM" value="{{ old('end_at') ? \Carbon\Carbon::parse(old('end_at'))->format('Y-m-d H:i') : $booking->end_at->format('Y-m-d H:i') }}">
                    </div>
                </div>

                {{-- hidden composed datetimes for server (controller still expects start_at/end_at) --}}
                <input type="hidden" name="start_at" id="start_at_hidden" value="{{ old('start_at', $booking->start_at->format('Y-m-d H:i:00')) }}">
                <input type="hidden" name="end_at" id="end_at_hidden" value="{{ old('end_at', $booking->end_at->format('Y-m-d H:i:00')) }}">

                <div class="mb-3">
                    <label class="form-label">Tujuan Kegiatan</label>
                    @php
                        $pval = old('purpose', $booking->purpose);
                        $std = ['Meeting Internal','Meeting Mitra Fundraising','Meeting Mitra Program','Meeting Mitra Implementasi','Meeting DPS'];
                        $showOther = ($pval && !in_array($pval, $std));
                    @endphp
                    <div class="d-flex gap-2 purpose-pair {{ $showOther ? 'wide-other' : '' }}">
                        <select id="purpose" name="purpose_select" class="form-select">
                            <option value="">-- Pilih Tujuan --</option>
                            <option value="Meeting Internal" {{ (old('purpose', $booking->purpose) == 'Meeting Internal') ? 'selected' : '' }}>Meeting Internal</option>
                            <option value="Meeting Mitra Fundraising" {{ (old('purpose', $booking->purpose) == 'Meeting Mitra Fundraising') ? 'selected' : '' }}>Meeting Mitra Fundraising</option>
                            <option value="Meeting Mitra Program" {{ (old('purpose', $booking->purpose) == 'Meeting Mitra Program') ? 'selected' : '' }}>Meeting Mitra Program</option>
                            <option value="Meeting Mitra Implementasi" {{ (old('purpose', $booking->purpose) == 'Meeting Mitra Implementasi') ? 'selected' : '' }}>Meeting Mitra Implementasi</option>
                            <option value="Meeting DPS" {{ (old('purpose', $booking->purpose) == 'Meeting DPS') ? 'selected' : '' }}>Meeting DPS</option>
                                <option value="__other__" {{ $showOther ? 'selected' : '' }}>Lainnya</option>
                            </select>
                            <input id="purpose_other" type="text" class="form-control purpose-other {{ $showOther ? '' : 'd-none' }}" placeholder="Tuliskan tujuan lain..." value="{{ $showOther ? $pval : '' }}">
                    </div>
                    {{-- hidden purpose field sent to server --}}
                    <input type="hidden" id="purpose_hidden" name="purpose" value="{{ old('purpose', $booking->purpose) }}">
                    @error('purpose') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Direktorat</label>
                        <select id="directorate" name="directorate" class="form-select">
                            <option value="">-- Pilih Direktorat --</option>
                            <option value="GNPE" {{ (old('directorate', $booking->directorate) === 'GNPE') ? 'selected' : '' }}>GNPE</option>
                            <option value="SPDE" {{ (old('directorate', $booking->directorate) === 'SPDE') ? 'selected' : '' }}>SPDE</option>
                            <option value="IHPN" {{ (old('directorate', $booking->directorate) === 'IHPN') ? 'selected' : '' }}>IHPN</option>
                            <option value="BIC" {{ (old('directorate', $booking->directorate) === 'BIC') ? 'selected' : '' }}>BIC</option>
                            <option value="SOSM" {{ (old('directorate', $booking->directorate) === 'SOSM') ? 'selected' : '' }}>SOSM</option>
                            <option value="ASHUM" {{ (old('directorate', $booking->directorate) === 'ASHUM') ? 'selected' : '' }}>ASHUM</option>
                        </select>
                        @error('directorate') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Divisi</label>
                        <select id="division" name="division" class="form-select">
                            <option value="">-- Pilih Divisi --</option>
                        </select>
                        @error('division') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Mitra (jika tidak ada diisi '-')</label>
                        <input name="partner" class="form-control" value="{{ old('partner', $booking->partner) }}">
                        @error('partner') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Jumlah Peserta (Internal / Eksternal)</label>
                        <div class="d-flex gap-2">
                            <input name="participants_internal" type="number" min="0" class="form-control" value="{{ old('participants_internal', $booking->participants_internal) }}" placeholder="Internal">
                            <input name="participants_external" type="number" min="0" class="form-control" value="{{ old('participants_external', $booking->participants_external) }}" placeholder="Eksternal">
                        </div>
                        @error('participants_internal') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        @error('participants_external') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Kebutuhan Fasilitas (jika tidak ada diisi '-')</label>
                    <textarea name="facilities" rows="2" class="form-control">{{ old('facilities', $booking->facilities) }}</textarea>
                    @error('facilities') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="d-flex gap-2 mb-2">
                    <button class="btn btn-success w-100">Save Changes</button>
                </div>
            </form>

            <!-- flatpickr CSS/JS (CDN) -->
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
            <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
            <style>
                /* By default the select should take available width; the 'other' input is hidden.
                   When 'wide-other' is present we keep the select large and give the other input a fixed small width. */
                .purpose-pair { display: flex; align-items: center; gap: .5rem; }
                .purpose-pair > select { flex: 1 1 auto; min-width: 0; }
                .purpose-pair > .purpose-other { flex: 0 0 auto; width: auto; }
                /* when showing other, make it small and let select remain wide */
                .purpose-pair.wide-other > .purpose-other { flex: 0 0 70%; max-width: 70%; }
                .purpose-pair.wide-other > select { flex: 1 1 auto; }
            </style>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    if (typeof flatpickr === 'function') {
                        const fpOpts = {
                            enableTime: true,
                            time_24hr: true,
                            dateFormat: 'Y-m-d H:i', // nilai yang dikirim
                            altFormat: 'd-m-Y H:i',  // yang tampil ke user
                            disableMobile: true
                        };
                        flatpickr('#start_picker', fpOpts);
                        flatpickr('#end_picker', fpOpts);
                    }

                    const form = document.getElementById('bookingEditForm');
                    if (!form) return;
                    function composeFromPicker(pickerId){
                        const v = document.getElementById(pickerId)?.value;
                        if (!v) return '';
                        return v + ':00';
                    }
                    form.addEventListener('submit', function(){
                        const s = composeFromPicker('start_picker');
                        const e = composeFromPicker('end_picker');
                        const sh = document.getElementById('start_at_hidden'); if (sh) sh.value = s;
                        const eh = document.getElementById('end_at_hidden'); if (eh) eh.value = e;
                        // sync purpose select -> hidden
                        const ps = document.getElementById('purpose');
                        const po = document.getElementById('purpose_other');
                        const ph = document.getElementById('purpose_hidden');
                        if (ps) {
                            if (ps.value === '__other__') {
                                ph.value = po ? po.value : '';
                            } else {
                                ph.value = ps.value;
                            }
                        }
                    });

                    // purpose toggle
                    const purposeSelect = document.getElementById('purpose');
                    const purposeOther = document.getElementById('purpose_other');
                    function togglePurpose() {
                        if (!purposeSelect) return;
                        const container = purposeSelect.closest('.purpose-pair');
                        if (purposeSelect.value === '__other__') {
                            purposeOther.classList.remove('d-none');
                            if (container) container.classList.add('wide-other');
                        } else {
                            purposeOther.classList.add('d-none');
                            if (container) container.classList.remove('wide-other');
                        }
                    }
                    if (purposeSelect) purposeSelect.addEventListener('change', togglePurpose);
                    // initial toggle state
                    togglePurpose();

                    // directorate -> divisions mapping for edit
                    const divisionMap = {
                        'GNPE': ['GPN','PDE'],
                        'SPDE': ['DCPE','SNP'],
                        'IHPN': ['PND','PSS','PSI'],
                        'BIC': ['BCMR','SMO'],
                        'SOSM': ['HC','GALC','ACC & FIN','IT'],
                        'ASHUM': ['ASHUM']
                    };

                    function populateDivisions(selectedDirectorate, selectedDivision) {
                        const divEl = document.getElementById('division');
                        if (!divEl) return;
                        divEl.innerHTML = '<option value="">-- Pilih Divisi --</option>';
                        if (!selectedDirectorate || !divisionMap[selectedDirectorate]) return;
                        divisionMap[selectedDirectorate].forEach(d => {
                            const opt = document.createElement('option');
                            opt.value = d;
                            opt.textContent = d;
                            if (selectedDivision && selectedDivision.toString().trim() === d.toString().trim()) opt.selected = true;
                            divEl.appendChild(opt);
                        });
                    }

                    // initial populate using existing booking/directorate
                    const initDirectorate = document.getElementById('directorate')?.value || @json(old('directorate', $booking->directorate));
                    const initDivision = @json(old('division', $booking->division));
                    populateDivisions(initDirectorate, initDivision);

                    const directorateEl = document.getElementById('directorate');
                    if (directorateEl) directorateEl.addEventListener('change', function(){ populateDivisions(this.value, ''); });
                });
            </script>
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
    </style>
@endpush

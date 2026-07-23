@extends('layouts.app')

@section('title', 'Edit Booking Kendaraan')

@section('content')
<div class="container mt-4">
    <div class="row g-3 align-items-start">
        @include('partials.dashboard-cards', [
            'colClass' => 'order-2 order-md-1',
            'idPrefix' => 'assets',
            'titleLabel' => 'Kendaraan Tersedia',
            'items' => $assets ?? collect(),
            'showSummaries' => false,
            'bookingsToday' => $bookingsToday ?? 0,
            'nextBookingTime' => $nextBookingTime ?? '-'
        ])

        <div class="col-md-8 order-1 order-md-2">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Edit Booking Kendaraan</h5>
                    <a href="{{ route('asset.bookings.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Kembali</a>
                </div>
                <div class="card-body">
                    <form id="asset-booking-edit-form" action="{{ route('asset.bookings.update', $booking) }}" method="post">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Kendaraan</label>
                            <select name="asset_id" class="form-select @error('asset_id') is-invalid @enderror">
                                <option value="">-- Pilih Kendaraan --</option>
                                @foreach($assets as $a)
                                    <option value="{{ $a->id }}" {{ old('asset_id', $booking->asset_id) == $a->id ? 'selected' : '' }}>{{ $a->name }}</option>
                                @endforeach
                            </select>
                            @error('asset_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tujuan Kegiatan</label>
                            <div class="d-flex gap-2 purpose-pair">
                                <select id="purpose" name="purpose_select" class="form-select">
                                    <option value="">-- Pilih Tujuan --</option>
                                    <option value="Implementasi">Implementasi</option>
                                    <option value="Pelayanan">Pelayanan</option>
                                    <option value="Meeting">Meeting</option>
                                    <option value="Pendampingan Mitra">Pendampingan Mitra</option>
                                    <option value="__other__">Lainnya</option>
                                </select>
                                <input id="purpose_other" type="text" class="form-control purpose-other d-none" placeholder="Tuliskan tujuan lain...">
                            </div>
                            <input type="hidden" id="purpose_hidden" name="purpose" value="{{ old('purpose', $booking->purpose) }}">
                            @error('purpose') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama Kegiatan</label>
                            <input name="title" type="text" class="form-control" value="{{ old('title', $booking->title) }}">
                            @error('title') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Direktorat</label>
                                <select id="directorate" name="directorate" class="form-select">
                                    <option value="">-- Pilih Direktorat --</option>
                                    <option value="GNPE" {{ old('directorate', $booking->directorate) === 'GNPE' ? 'selected' : '' }}>GNPE</option>
                                    <option value="SPDE" {{ old('directorate', $booking->directorate) === 'SPDE' ? 'selected' : '' }}>SPDE</option>
                                    <option value="IHPN" {{ old('directorate', $booking->directorate) === 'IHPN' ? 'selected' : '' }}>IHPN</option>
                                    <option value="BIC" {{ old('directorate', $booking->directorate) === 'BIC' ? 'selected' : '' }}>BIC</option>
                                    <option value="SOSM" {{ old('directorate', $booking->directorate) === 'SOSM' ? 'selected' : '' }}>SOSM</option>
                                    <option value="ASHUM" {{ old('directorate', $booking->directorate) === 'ASHUM' ? 'selected' : '' }}>ASHUM</option>
                                </select>
                                @error('directorate') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Divisi</label>
                                <select id="division" data-initial="{{ old('division', $booking->division) }}" name="division" class="form-select">
                                    <option value="">-- Pilih Divisi --</option>
                                </select>
                                @error('division') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Tanggal & Waktu Mulai</label>
                                <input id="start_picker" type="text" class="form-control flatpickr-date" placeholder="Pilih tanggal & waktu" value="{{ old('start_at') ? \Carbon\Carbon::parse(old('start_at'))->format('Y-m-d H:i') : $booking->start_at->format('Y-m-d H:i') }}" data-default="{{ old('start_at', $booking->start_at->format('Y-m-d H:i')) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal & Waktu Selesai</label>
                                <input id="end_picker" type="text" class="form-control flatpickr-date" placeholder="Pilih tanggal & waktu" value="{{ old('end_at') ? \Carbon\Carbon::parse(old('end_at'))->format('Y-m-d H:i') : $booking->end_at->format('Y-m-d H:i') }}" data-default="{{ old('end_at', $booking->end_at->format('Y-m-d H:i')) }}">
                            </div>
                        </div>

                        <input type="hidden" name="start_at" id="start_at_hidden" value="{{ old('start_at', $booking->start_at->format('Y-m-d H:i')) }}">
                        <input type="hidden" name="end_at" id="end_at_hidden" value="{{ old('end_at', $booking->end_at->format('Y-m-d H:i')) }}">

                        <div class="mb-3">
                            <label class="form-label">PIC (Penanggung Jawab)</label>
                            <select name="pic_name" id="pic_id" class="form-select">
                                <option value="">-- Pilih PIC --</option>
                                {{-- options will be populated by JS from public/karyawan_pusat_all.json; fallback server-rendered options for no-JS --}}
                                @foreach($users as $u)
                                    <option value="{{ $u->name }}" {{ old('pic_name', $booking->pic_name) == $u->name ? 'selected' : '' }}>{{ Str::title($u->name) }} ({{ $u->email }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Destinasi Tujuan</label>
                            <input name="destination_text" class="form-control" value="{{ old('destination_text', $booking->destination_text) }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Driver</label>
                            <input name="driver" class="form-control @error('driver') is-invalid @enderror" placeholder="Masukkan nama driver (minimal 3 karakter)" value="{{ old('driver', $booking->driver ?? '') }}" minlength="3">
                            @error('driver') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            <small class="text-muted">Wajib diisi, minimal 3 karakter, tidak boleh hanya "-" atau spasi.</small>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0">Personel</label>
                                <button type="button" id="add-personnel" class="btn btn-sm btn-outline-primary px-2">+ Personel</button>
                            </div>
                            <div id="personnel-list">
                                @php
                                    $oldPersonnel = old('personnel', $booking->personnel ?? []);
                                    if (!is_array($oldPersonnel)) {
                                        $oldPersonnel = $oldPersonnel ? [$oldPersonnel] : [''];
                                    }
                                @endphp
                                @foreach($oldPersonnel as $p)
                                    <div class="input-group mb-2 personnel-item">
                                        <input type="text" name="personnel[]" class="form-control" value="{{ $p }}" placeholder="Nama atau keterangan personel">
                                        <button type="button" class="btn btn-outline-danger btn-sm rounded-circle remove-personnel ms-2 align-self-center" title="Hapus">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                            @error('personnel') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            @if($errors->has('personnel.*'))
                                <div class="text-danger small mt-1">Terdapat kesalahan pada salah satu entri personel.</div>
                            @endif
                            <div class="form-text small text-muted">Tambah atau hapus personel sesuai kebutuhan.</div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
    if (typeof flatpickr !== 'function') return;
    var startEl = document.getElementById('start_picker');
    var startPicker = flatpickr('#start_picker', {
        enableTime: true,
        time_24hr: true,
        dateFormat: 'Y-m-d H:i',
        altFormat: 'd-m-Y H:i',
        altInput: false,
        disableMobile: true,
        allowInput: false,
        defaultDate: (startEl && startEl.dataset && startEl.dataset.default) ? startEl.dataset.default : (startEl ? startEl.value : null),
        onChange: function(sd, ds){ var h = document.getElementById('start_at_hidden'); if (h) h.value = ds ? ds + ':00' : ''; }
    });
    var endEl = document.getElementById('end_picker');
    var endPicker = flatpickr('#end_picker', {
        enableTime: true,
        time_24hr: true,
        dateFormat: 'Y-m-d H:i',
        altFormat: 'd-m-Y H:i',
        altInput: false,
        disableMobile: true,
        allowInput: false,
        defaultDate: (endEl && endEl.dataset && endEl.dataset.default) ? endEl.dataset.default : (endEl ? endEl.value : null),
        onChange: function(sd, ds){ var h = document.getElementById('end_at_hidden'); if (h) h.value = ds ? ds + ':00' : ''; }
    });
    try {
        if (startPicker && startPicker.selectedDates.length) document.getElementById('start_at_hidden').value = startPicker.formatDate(startPicker.selectedDates[0], 'Y-m-d H:i') + ':00';
        if (endPicker && endPicker.selectedDates.length) document.getElementById('end_at_hidden').value = endPicker.formatDate(endPicker.selectedDates[0], 'Y-m-d H:i') + ':00';
    } catch(e){}

    // compose hidden start/end before submit — target form explicitly
    var form = document.getElementById('asset-booking-edit-form');
    if (form) {
        form.addEventListener('submit', function(){
            var s = document.getElementById('start_picker')?.value || '';
            var en = document.getElementById('end_picker')?.value || '';
            var startHidden = document.getElementById('start_at_hidden');
            var endHidden = document.getElementById('end_at_hidden');
            if (startHidden) startHidden.value = s ? s + ':00' : '';
            if (endHidden) endHidden.value = en ? en + ':00' : '';
        });
    }

    // personnel dynamic fields
    var personnelList = document.getElementById('personnel-list');
    var addBtn = document.getElementById('add-personnel');
    function makeItem(value) {
        var wrapper = document.createElement('div');
        wrapper.className = 'input-group mb-2 personnel-item';
        wrapper.innerHTML = '<input type="text" name="personnel[]" class="form-control" value="'+(value||'')+'" placeholder="Nama atau keterangan personel">' +
            '<button type="button" class="btn btn-outline-danger btn-sm rounded-circle remove-personnel ms-2 align-self-center" title="Hapus">' +
                '<i class="bi bi-x"></i>' +
            '</button>';
        return wrapper;
    }

    addBtn.addEventListener('click', function(){
        personnelList.appendChild(makeItem(''));
    });

    personnelList.addEventListener('click', function(e){
        var btn = (e.target && e.target.closest) ? e.target.closest('.remove-personnel') : null;
        if (btn) {
            var item = btn.closest('.personnel-item');
            if (item) item.remove();
        }
    });
});
</script>
@endpush

@push('head')
    <style>
        .purpose-pair { display:flex; align-items:center; gap:.5rem; }
        .purpose-pair > select { flex:1 1 auto; min-width:0; }
        .purpose-pair > .purpose-other { flex:0 0 auto; width:auto; }
        .purpose-pair.wide-other > .purpose-other { flex:0 0 70%; max-width:70%; }
    </style>
    <!-- Select2 CSS for PIC dropdown -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Select2 look & feel mirip Bootstrap */
        .select2-container--default .select2-selection--single{
            height:calc(2.25rem + 2px);
            padding:.375rem .75rem;
            border:1px solid #ced4da; border-radius:.25rem; background-color:#fff; box-shadow:none;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered{
            line-height:1.5; color:#495057; padding-left:0;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow{
            height:100%; right:.5rem; top:0; width:2rem; display:flex; align-items:center; justify-content:center; color:#495057;
        }
        .select2-container { width:100%!important; }
        .select2-container--default .select2-results__option--highlighted,
        .select2-container--default .select2-results__option[aria-selected=true]{
            background-color:#007bff; color:#fff;
        }
        .select2-container--default .select2-selection__clear{ display:none!important; }
        /* lighter placeholder color for Select2 when no value is selected */
        .select2-container.placeholder-light .select2-selection__rendered { color: #6c757d !important; }
    </style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
    try {
        const picSelect = $('#pic_id');
        if (!picSelect || !picSelect.length) return;

        fetch('{{ asset('karyawan_pusat_all.json') }}')
            .then(res => res.json())
            .then(data => {
                picSelect.find('option:not(:first)').remove();
                var names = Array.isArray(data) ? data.slice() : [];
                names.sort(function(a,b){ return String(a).localeCompare(String(b), undefined, { sensitivity: 'base' }); });
                names.forEach(function(name){ picSelect.append(new Option(name, name)); });

                picSelect.select2({ placeholder: '-- Pilih PIC --', allowClear: true, width: '100%' });
                var picContainer = picSelect.next('.select2-container');
                function updatePicPlaceholderClass(){ if (!picSelect.val() || picSelect.val().length === 0) picContainer.addClass('placeholder-light'); else picContainer.removeClass('placeholder-light'); }
                const oldVal = @json(old('pic_name', $booking->pic_name ?? ''));
                if (oldVal) { picSelect.val(oldVal).trigger('change'); }
                picSelect.on('change', updatePicPlaceholderClass);
                updatePicPlaceholderClass();
            }).catch(()=>{});
    } catch(e){}
});
</script>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    // Purpose select <-> hidden sync and "Lainnya" handling
    var purposeSelect = document.getElementById('purpose');
    var purposeOther = document.getElementById('purpose_other');
    var purposeHidden = document.getElementById('purpose_hidden');
    if (purposeSelect && purposeHidden) {
        // initialize select based on hidden value
        var current = purposeHidden.value || '';
        var found = false;
        for (var i=0;i<purposeSelect.options.length;i++){
            if (purposeSelect.options[i].value === current){ found = true; break; }
        }
        if (found) {
            purposeSelect.value = current;
            purposeOther.classList.add('d-none');
        } else {
            purposeSelect.value = '__other__';
            purposeOther.classList.remove('d-none');
            purposeOther.value = current;
            purposeSelect.closest('.purpose-pair')?.classList.add('wide-other');
        }

        function syncPurpose(){
            if (purposeSelect.value === '__other__') {
                purposeHidden.value = purposeOther.value || '';
            } else {
                purposeHidden.value = purposeSelect.value || '';
            }
        }

        purposeSelect.addEventListener('change', function(){
            if (this.value === '__other__') {
                purposeOther.classList.remove('d-none');
                this.closest('.purpose-pair')?.classList.add('wide-other');
            } else {
                purposeOther.classList.add('d-none');
                this.closest('.purpose-pair')?.classList.remove('wide-other');
            }
            syncPurpose();
        });
        purposeOther.addEventListener('input', syncPurpose);
    }

    // Directorate -> Division population
    var divisionMap = {
        'GNPE': ['GPN','PDE'],
        'SPDE': ['DCPE','SNP'],
        'IHPN': ['PND','PSS','PSI'],
        'BIC': ['BCMR','SMO'],
        'SOSM': ['HC','GALC','ACC & FIN','IT'],
        'ASHUM': ['ASHUM']
    };
    function populateDivisions(selectedDirectorate, selectedDivision){
        var divEl = document.getElementById('division');
        if (!divEl) return;
        var initial = divEl.getAttribute('data-initial') || '';
        divEl.innerHTML = '<option value="">-- Pilih Divisi --</option>';
        if (!selectedDirectorate || !divisionMap[selectedDirectorate]) return;
        divisionMap[selectedDirectorate].forEach(function(d){
            var opt = document.createElement('option');
            opt.value = d;
            opt.textContent = d;
            if (selectedDivision && selectedDivision === d) opt.selected = true;
            if (!selectedDivision && initial && initial === d) opt.selected = true;
            divEl.appendChild(opt);
        });
    }
    var directorateEl = document.getElementById('directorate');
    var initDirectorate = directorateEl ? directorateEl.value : '';
    var initDivision = document.getElementById('division')?.getAttribute('data-initial') || '';
    populateDivisions(initDirectorate, initDivision);
    if (directorateEl) directorateEl.addEventListener('change', function(){ populateDivisions(this.value, ''); });
});
</script>
@endpush

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
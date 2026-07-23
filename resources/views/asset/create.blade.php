@extends('layouts.app')

@section('title', 'Booking Kendaraan')

@section('content')
<div class="container mt-4">
    <div class="row g-3 align-items-start">
        @include('partials.dashboard-cards', [
            'colClass' => 'order-2 order-md-1',
            'idPrefix' => 'assets',
            'titleLabel' => 'Kendaraan Tersedia',
            'items' => ($availableAssets ?? $assets ?? collect()),
            'showSummaries' => false,
            'bookingsToday' => $bookingsToday ?? 0,
            'nextBookingTime' => $nextBookingTime ?? '-'
        ])

        <div class="col-md-8 order-1 order-md-2">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Booking Kendaraan</h5>
                    <a href="javascript:history.back()" class="btn btn-light"><i class="bi bi-arrow-left"></i> Kembali</a>
                </div>
                <div class="card-body">
                    <form id="asset-booking-form" action="{{ route('asset.bookings.store') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label">Kendaraan</label>
                            <select name="asset_id" class="form-select @error('asset_id') is-invalid @enderror">
                                <option value="">-- Pilih Kendaraan --</option>
                                @forelse($assets as $a)
                                    <option value="{{ $a->id }}" {{ old('asset_id') == $a->id ? 'selected' : '' }}>{{ $a->name }}</option>
                                @empty
                                    <option value="" disabled>-- Tidak ada asset terdaftar --</option>
                                @endforelse
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
                            <input type="hidden" id="purpose_hidden" name="purpose" value="{{ old('purpose', '') }}">
                            @error('purpose') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama Kegiatan</label>
                            <input name="title" type="text" class="form-control" placeholder="Nama Kegiatan" value="{{ old('title') }}">
                            @error('title') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Direktorat</label>
                                <select id="directorate" name="directorate" class="form-select">
                                    <option value="">-- Pilih Direktorat --</option>
                                    <option value="GNPE" {{ old('directorate') === 'GNPE' ? 'selected' : '' }}>GNPE</option>
                                    <option value="SPDE" {{ old('directorate') === 'SPDE' ? 'selected' : '' }}>SPDE</option>
                                    <option value="IHPN" {{ old('directorate') === 'IHPN' ? 'selected' : '' }}>IHPN</option>
                                    <option value="BIC" {{ old('directorate') === 'BIC' ? 'selected' : '' }}>BIC</option>
                                    <option value="SOSM" {{ old('directorate') === 'SOSM' ? 'selected' : '' }}>SOSM</option>
                                    <option value="ASHUM" {{ old('directorate') === 'ASHUM' ? 'selected' : '' }}>ASHUM</option>
                                </select>
                                @error('directorate') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Divisi</label>
                                <select id="division" data-initial="{{ old('division', '') }}" name="division" class="form-select">
                                    <option value="">-- Pilih Divisi --</option>
                                </select>
                                @error('division') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Tanggal & Waktu Mulai</label>
                                <input id="start_picker" type="text" class="form-control flatpickr-date" placeholder="Pilih tanggal & waktu" value="{{ old('start_at') ? \Carbon\Carbon::parse(old('start_at'))->format('Y-m-d H:i') : '' }}" data-default="{{ old('start_at', '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal & Waktu Selesai</label>
                                <input id="end_picker" type="text" class="form-control flatpickr-date" placeholder="Pilih tanggal & waktu" value="{{ old('end_at') ? \Carbon\Carbon::parse(old('end_at'))->format('Y-m-d H:i') : '' }}" data-default="{{ old('end_at', '') }}">
                            </div>
                        </div>

                        <input type="hidden" name="start_at" id="start_at_hidden" value="{{ old('start_at', '') }}">
                        <input type="hidden" name="end_at" id="end_at_hidden" value="{{ old('end_at', '') }}">

                        {{-- === PILIH PIC: dari JSON + Select2 === --}}
                        <div class="mb-3">
                            <label class="form-label">PIC (Penanggung Jawab)</label>
                            <select name="pic_name" id="pic_id" class="form-select">
                                <option value="">-- Pilih PIC --</option>
                                {{-- opsi diisi via JS dari public/karyawan_pusat_all.json --}}
                            </select>
                            @error('pic_name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            <small class="text-muted">Ketik nama untuk mencari PIC.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Destinasi Tujuan</label>
                            <input name="destination_text" class="form-control" placeholder="Destinasi Tujuan" value="{{ old('destination_text') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Driver</label>
                            <input name="driver" class="form-control @error('driver') is-invalid @enderror" placeholder="Masukkan nama driver (minimal 3 karakter)" value="{{ old('driver') }}" minlength="3">
                            @error('driver') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            <small class="text-muted">Wajib diisi, minimal 3 karakter, tidak boleh hanya "-" atau spasi.</small>
                        </div>

                        {{-- snapshot upload removed from booking creation; snapshot will be captured on completion if needed --}}

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0">Personel (Opsional)</label>
                                <button type="button" id="add-personnel" class="btn btn-sm btn-outline-primary px-2">+ Personel</button>
                            </div>
                            <div id="personnel-list">
                                @php
                                    $oldPersonnel = old('personnel');
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
                            <button type="submit" class="btn btn-success">Simpan Peminjaman</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

{{-- jQuery & Select2 (agar mirip pola create-campaign/COA) --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function(){
    if (typeof flatpickr !== 'function') return;

    // --- Flatpickr start/end
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
        onChange: function(selectedDates, dateStr){
            var h = document.getElementById('start_at_hidden'); if (h) { h.value = dateStr ? dateStr + ':00' : ''; h.dispatchEvent(new Event('change', { bubbles: true })); }
        }
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
        onChange: function(selectedDates, dateStr){
            var h = document.getElementById('end_at_hidden'); if (h) { h.value = dateStr ? dateStr + ':00' : ''; h.dispatchEvent(new Event('change', { bubbles: true })); }
        }
    });

    try {
        if (startPicker && startPicker.selectedDates && startPicker.selectedDates.length) {
            var ds = startPicker.formatDate(startPicker.selectedDates[0], 'Y-m-d H:i');
            document.getElementById('start_at_hidden').value = ds ? ds + ':00' : '';
        }
        if (endPicker && endPicker.selectedDates && endPicker.selectedDates.length) {
            var de = endPicker.formatDate(endPicker.selectedDates[0], 'Y-m-d H:i');
            document.getElementById('end_at_hidden').value = de ? de + ':00' : '';
        }
    } catch(e) { /* ignore */ }

    var form = document.getElementById('asset-booking-form');
    if (form) {
        form.addEventListener('submit', function(e){
            var s = document.getElementById('start_picker')?.value || '';
            var en = document.getElementById('end_picker')?.value || '';
            var startHidden = document.getElementById('start_at_hidden');
            var endHidden = document.getElementById('end_at_hidden');
            if (startHidden) startHidden.value = s ? s + ':00' : '';
            if (endHidden) endHidden.value = en ? en + ':00' : '';
        });
    }

    // --- Personel dynamic fields
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
    addBtn.addEventListener('click', function(){ personnelList.appendChild(makeItem('')); });
    personnelList.addEventListener('click', function(e){
        var btn = (e.target && e.target.closest) ? e.target.closest('.remove-personnel') : null;
        if (btn) { var item = btn.closest('.personnel-item'); if (item) item.remove(); }
    });

    // --- Purpose select logic
    var purposeSelect = document.getElementById('purpose');
    var purposeOther = document.getElementById('purpose_other');
    var purposeHidden = document.getElementById('purpose_hidden');
    function togglePurpose() {
        if (!purposeSelect) return;
        var container = purposeSelect.closest('.purpose-pair');
        if (purposeSelect.value === '__other__') {
            purposeOther.classList.remove('d-none');
            if (container) container.classList.add('wide-other');
        } else {
            purposeOther.classList.add('d-none');
            if (container) container.classList.remove('wide-other');
        }
    }
    function syncPurpose() {
        if (!purposeSelect) return;
        if (purposeSelect.value === '__other__') {
            purposeHidden.value = purposeOther.value || '';
        } else {
            purposeHidden.value = purposeSelect.value;
        }
    }
    if (purposeSelect) { purposeSelect.addEventListener('change', function(){ syncPurpose(); togglePurpose(); }); }
    if (purposeOther) { purposeOther.addEventListener('input', syncPurpose); }
    syncPurpose(); togglePurpose();

    // --- Directorate -> Division mapping
    var divisionMap = {
        'GNPE': ['GPN','PDE'],
        'SPDE': ['DCPE','SNP'],
        'IHPN': ['PND','PSS','PSI'],
        'BIC': ['BCMR','SMO'],
        'SOSM': ['HC','GALC','ACC & FIN','IT'],
        'ASHUM': ['ASHUM']
    };
    function populateDivisions(selectedDirectorate, selectedDivision) {
        var divEl = document.getElementById('division');
        if (!divEl) return;
        divEl.innerHTML = '<option value="">-- Pilih Divisi --</option>';
        if (!selectedDirectorate || !divisionMap[selectedDirectorate]) return;
        divisionMap[selectedDirectorate].forEach(function(d){
            var opt = document.createElement('option'); opt.value = d; opt.textContent = d; if (selectedDivision && selectedDivision === d) opt.selected = true; divEl.appendChild(opt);
        });
    }
    var initDirectorate = document.getElementById('directorate')?.value || '';
    var initDivision = document.getElementById('division')?.getAttribute('data-initial') || '{{ old('division', '') }}';
    populateDivisions(initDirectorate, initDivision);
    var directorateEl = document.getElementById('directorate');
    if (directorateEl) directorateEl.addEventListener('change', function(){ populateDivisions(this.value, ''); });

        // === PIC Select2: load dari public/karyawan_pusat_all.json ===
        const picSelect = $('#pic_id');

        fetch('{{ asset('karyawan_pusat_all.json') }}')
            .then(res => res.json())
            .then(data => {
                // Bersihkan option selain placeholder
                picSelect.find('option:not(:first)').remove();

                // Tambahkan opsi dari array nama (urutkan abjad, case-insensitive)
                var names = Array.isArray(data) ? data.slice() : [];
                names.sort(function(a,b){ return String(a).localeCompare(String(b), undefined, { sensitivity: 'base' }); });
                names.forEach(function(name){
                    // value = name (karena tidak ada id/email di JSON)
                    const opt = new Option(name, name, false, false);
                    picSelect.append(opt);
                });

                // Init Select2
                picSelect.select2({
                    placeholder: '-- Pilih PIC --',
                    allowClear: true,
                    width: '100%'
                });

                // Helper: toggle lighter placeholder styling on the select2 container
                var picContainer = picSelect.next('.select2-container');
                function updatePicPlaceholderClass(){
                    if (!picSelect.val() || picSelect.val().length === 0) {
                        picContainer.addClass('placeholder-light');
                    } else {
                        picContainer.removeClass('placeholder-light');
                    }
                }

                // Restore old value jika ada
                const oldVal = @json(old('pic_name'));
                if (oldVal) { picSelect.val(oldVal).trigger('change'); }

                // Trigger validasi tombol dan update placeholder state saat berubah
                picSelect.on('change', function(){ updatePicPlaceholderClass(); checkFormCompleteness(); });

                // initial state
                updatePicPlaceholderClass();
            })
            .catch(err => console.error('Gagal load JSON PIC:', err));

    // --- form completeness check: DISABLE submit sampai field wajib terisi ---
    var submitBtn = document.querySelector('#asset-booking-form button[type="submit"]');
    if (!submitBtn && form) submitBtn = form.querySelector('button[type="submit"]');
    function checkFormCompleteness() {
        if (typeof syncPurpose === 'function') syncPurpose();

        var asset = document.querySelector('select[name="asset_id"]')?.value || '';
        var title = document.querySelector('input[name="title"]')?.value || '';
        var purpose = document.getElementById('purpose_hidden')?.value || '';
        var directorate = document.getElementById('directorate')?.value || '';
        var division = document.getElementById('division')?.value || '';
        var start = document.getElementById('start_at_hidden')?.value || document.getElementById('start_picker')?.value || '';
        var end = document.getElementById('end_at_hidden')?.value || document.getElementById('end_picker')?.value || '';
        var pic = document.querySelector('select[name="pic_name"]')?.value || '';
        var driver = document.querySelector('input[name="driver"]')?.value || '';

        var ok = asset.trim() && title.trim() && purpose.trim() && directorate.trim() && division.trim() && start.trim() && end.trim() && pic.trim() && driver.trim() && driver.trim().length >= 3;
        if (submitBtn) {
            submitBtn.disabled = !ok;
            submitBtn.classList.toggle('opacity-75', !ok);
        }
    }

    ['change','input'].forEach(function(ev){
      document.querySelectorAll('select[name="asset_id"], input[name="title"], #purpose, #purpose_other, #purpose_hidden, #directorate, #division, select[name="pic_name"], input[name="driver"], #start_at_hidden, #end_at_hidden, #start_picker, #end_picker').forEach(function(el){
        el.addEventListener(ev, checkFormCompleteness);
      });
    });

    document.getElementById('start_at_hidden')?.addEventListener('change', checkFormCompleteness);
    document.getElementById('end_at_hidden')?.addEventListener('change', checkFormCompleteness);

    checkFormCompleteness();
});
</script>
@endpush

@endsection

@push('head')
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.7/index.global.min.css" rel="stylesheet">
    <!-- Select2 CSS (samakan look dengan halaman create campaign) -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
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
        #dashboard-calendar .fc-toolbar .fc-button i { font-size: 1rem; line-height: 1; }
        #dashboard-calendar .fc-toolbar { padding: .5rem .5rem 0 !important; }
        #dashboard-calendar .fc-toolbar .fc-toolbar-chunk:nth-child(2) {
            display: flex !important; align-items: center !important; justify-content: center !important;
            gap: 1rem !important; flex-wrap: nowrap !important;
        }
        #dashboard-calendar .fc-toolbar-title { margin: 0 !important; font-size: 1.4rem !important; font-weight: 600 !important; }
        #dashboard-calendar .fc-daygrid-day { min-height: 56px; }
        #dashboard-calendar .booking-marker {
            position: absolute; top: 6px; left: 6px; width: 10px; height: 10px; background: #28a745;
            border-radius: 50%; box-shadow: 0 1px 2px rgba(0,0,0,0.12); pointer-events: none; z-index: 5;
        }
        .calendar-nav-left, .calendar-nav-right {
            display: inline-flex !important; align-items: center !important; justify-content: center !important;
            width: 36px !important; height: 36px !important; padding: 0 !important; border-radius: 6px !important;
            flex: 0 0 auto !important; visibility: visible !important; opacity: 1 !important;
            background: #1f2937 !important; color: #fff !important; border: none !important;
            box-shadow: 0 1px 2px rgba(0,0,0,0.08) !important; cursor: pointer !important;
        }
        .calendar-nav-left i, .calendar-nav-right i { color: #fff !important; font-size: 1rem; line-height: 1; }
        .calendar-nav-left:hover, .calendar-nav-right:hover,
        .calendar-nav-left:focus, .calendar-nav-right:focus {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.12) !important;
        }
        .fc-toolbar .calendar-nav-left, .fc-toolbar .calendar-nav-right { margin: 0 .75rem !important; }

        .rooms-list .fw-semibold { font-size: 0.95rem; }
        .rooms-list .badge { font-size: 0.8rem; padding: .35em .5em; }
        .rooms-list::-webkit-scrollbar { width: 8px; }
        .rooms-list::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.08); border-radius: 8px; }

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

@extends('layouts.app')

@section('title', 'Admin Edit Booking Asset')

@section('content')
<div class="container mt-4">
    <div class="row g-3">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Admin Edit Booking Asset</h5>
                    <a href="{{ route('asset.admin.bookings.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Kembali</a>
                </div>
                <div class="card-body">
                    <form id="asset-admin-edit-form" action="{{ route('asset.admin.bookings.update', $booking) }}" method="post">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Asset</label>
                            <select name="asset_id" class="form-select">
                                <option value="">-- Pilih Asset --</option>
                                @foreach($assets as $a)
                                    <option value="{{ $a->id }}" {{ old('asset_id', $booking->asset_id) == $a->id ? 'selected' : '' }}>{{ $a->name }}</option>
                                @endforeach
                            </select>
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
                        </div>

                        
                        <div class="mb-3">
                            <label class="form-label">Nama Kegiatan</label>
                            <input name="title" class="form-control" value="{{ old('title', $booking->title) }}">
                        </div>

                        @php
                            $curDirectorate = old('directorate', $booking->directorate);
                            $curDivision    = old('division', $booking->division);
                            $stdDirectorates = ['GNPE','SPDE','IHPN','BIC','SOSM','ASHUM'];
                        @endphp
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Direktorat</label>
                                <select id="directorate" name="directorate" class="form-select">
                                    <option value="">-- Pilih Direktorat --</option>
                                    {{-- Fallback: if stored value is not in the current list, show it so it isn't silently lost --}}
                                    @if($curDirectorate && !in_array($curDirectorate, $stdDirectorates))
                                        <option value="{{ $curDirectorate }}" selected>{{ $curDirectorate }} (lama)</option>
                                    @endif
                                    <option value="GNPE"  {{ $curDirectorate === 'GNPE'  ? 'selected' : '' }}>GNPE</option>
                                    <option value="SPDE"  {{ $curDirectorate === 'SPDE'  ? 'selected' : '' }}>SPDE</option>
                                    <option value="IHPN"  {{ $curDirectorate === 'IHPN'  ? 'selected' : '' }}>IHPN</option>
                                    <option value="BIC"   {{ $curDirectorate === 'BIC'   ? 'selected' : '' }}>BIC</option>
                                    <option value="SOSM"  {{ $curDirectorate === 'SOSM'  ? 'selected' : '' }}>SOSM</option>
                                    <option value="ASHUM" {{ $curDirectorate === 'ASHUM' ? 'selected' : '' }}>ASHUM</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Divisi</label>
                                {{-- Pre-render current division from PHP; JS will replace when a valid direktorat is selected --}}
                                <select id="division" data-initial="{{ $curDivision }}" name="division" class="form-select">
                                    <option value="">-- Pilih Divisi --</option>
                                    @if($curDivision)
                                        <option value="{{ $curDivision }}" selected>{{ $curDivision }}</option>
                                    @endif
                                </select>
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
                            <label class="form-label">PIC</label>
                            <select name="pic_name" id="pic_id" class="form-select">
                                <option value="">-- Pilih --</option>
                                {{-- JS will populate options; keep server-side options as fallback --}}
                                @foreach($users as $u)
                                    <option value="{{ $u->name }}" {{ old('pic_name', $booking->pic_name) == $u->name ? 'selected' : '' }}>{{ Str::title($u->name) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Destinasi Tujuan</label>
                            <input name="destination_text" class="form-control" value="{{ old('destination_text', $booking->destination_text) }}">
                            @error('destination_text') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Driver (Opsional)</label>
                            <input name="driver" class="form-control" value="{{ old('driver', $booking->driver ?? '') }}" placeholder="Nama driver">
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

                        <div class="mb-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                @foreach(['pending','approved','in_use','cancelled','done'] as $s)
                                    <option value="{{ $s }}" {{ old('status', $booking->status) == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="d-flex w-100">
                            <button type="submit" class="btn btn-primary w-100">Simpan Perubahan</button>
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
    if (typeof flatpickr === 'function') {
    var startEl = document.getElementById('start_picker');
    var endEl = document.getElementById('end_picker');
    var startPicker = flatpickr('#start_picker', { enableTime: true, time_24hr: true, dateFormat: 'Y-m-d H:i', altFormat: 'd-m-Y H:i', altInput: false, disableMobile: true, allowInput: false, defaultDate: (startEl && startEl.dataset && startEl.dataset.default) ? startEl.dataset.default : (startEl ? startEl.value : null), onChange: function(sd, ds){ var h = document.getElementById('start_at_hidden'); if (h) h.value = ds ? ds + ':00' : ''; } });
    var endPicker = flatpickr('#end_picker', { enableTime: true, time_24hr: true, dateFormat: 'Y-m-d H:i', altFormat: 'd-m-Y H:i', altInput: false, disableMobile: true, allowInput: false, defaultDate: (endEl && endEl.dataset && endEl.dataset.default) ? endEl.dataset.default : (endEl ? endEl.value : null), onChange: function(sd, ds){ var h = document.getElementById('end_at_hidden'); if (h) h.value = ds ? ds + ':00' : ''; } });
    try { if (startPicker && startPicker.selectedDates.length) document.getElementById('start_at_hidden').value = startPicker.formatDate(startPicker.selectedDates[0], 'Y-m-d H:i') + ':00'; if (endPicker && endPicker.selectedDates.length) document.getElementById('end_at_hidden').value = endPicker.formatDate(endPicker.selectedDates[0], 'Y-m-d H:i') + ':00'; } catch(e){}
    }
    
    // compose hidden start/end before submit
    var form = document.getElementById('asset-admin-edit-form');
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

    // personnel dynamic
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
    if (addBtn) addBtn.addEventListener('click', function(){ personnelList.appendChild(makeItem('')); });
    if (personnelList) personnelList.addEventListener('click', function(e){ var btn = (e.target && e.target.closest) ? e.target.closest('.remove-personnel') : null; if (btn) { var item = btn.closest('.personnel-item'); if (item) item.remove(); } });
});
</script>
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
                picSelect.select2({ placeholder: '-- Pilih --', allowClear: true, width: '100%' });
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
<script>
document.addEventListener('DOMContentLoaded', function(){
    // Purpose <-> hidden sync
    var purposeSelect = document.getElementById('purpose');
    var purposeOther = document.getElementById('purpose_other');
    var purposeHidden = document.getElementById('purpose_hidden');
    if (purposeSelect && purposeHidden) {
        var current = purposeHidden.value || '';
        var found = false;
        for (var i=0;i<purposeSelect.options.length;i++){ if (purposeSelect.options[i].value === current){ found = true; break; } }
        if (found) { purposeSelect.value = current; purposeOther.classList.add('d-none'); }
        else { purposeSelect.value = '__other__'; purposeOther.classList.remove('d-none'); purposeOther.value = current; purposeSelect.closest('.purpose-pair')?.classList.add('wide-other'); }
        function syncPurpose(){ if (purposeSelect.value === '__other__') purposeHidden.value = purposeOther.value || ''; else purposeHidden.value = purposeSelect.value || ''; }
        purposeSelect.addEventListener('change', function(){ if (this.value === '__other__') { purposeOther.classList.remove('d-none'); this.closest('.purpose-pair')?.classList.add('wide-other'); } else { purposeOther.classList.add('d-none'); this.closest('.purpose-pair')?.classList.remove('wide-other'); } syncPurpose(); });
        purposeOther.addEventListener('input', syncPurpose);
    }

    var divisionMap = { 'GNPE':['GPN','PDE'],'SPDE':['DCPE','SNP'],'IHPN':['PND','PSS','PSI'],'BIC':['BCMR','SMO'],'SOSM':['HC','GALC','ACC & FIN','IT'],'ASHUM':['ASHUM'] };
    function populateDivisions(selectedDirectorate, selectedDivision){ var divEl = document.getElementById('division'); if (!divEl) return; var initial = divEl.getAttribute('data-initial') || ''; divEl.innerHTML = '<option value="">-- Pilih Divisi --</option>'; if (!selectedDirectorate || !divisionMap[selectedDirectorate]) return; divisionMap[selectedDirectorate].forEach(function(d){ var opt = document.createElement('option'); opt.value = d; opt.textContent = d; if (selectedDivision && selectedDivision === d) opt.selected = true; if (!selectedDivision && initial && initial === d) opt.selected = true; divEl.appendChild(opt); }); }
    var directorateEl = document.getElementById('directorate');
    var initDirectorate = directorateEl ? directorateEl.value : '';
    var initDivision    = document.getElementById('division')?.getAttribute('data-initial') || '';
    // Only call populateDivisions on init when the stored direktorat is in the known map;
    // otherwise keep the PHP-pre-rendered division option as-is.
    if (initDirectorate && divisionMap[initDirectorate]) {
        populateDivisions(initDirectorate, initDivision);
    }
    if (directorateEl) directorateEl.addEventListener('change', function(){ populateDivisions(this.value, ''); });
});
</script>
@endpush

@endsection

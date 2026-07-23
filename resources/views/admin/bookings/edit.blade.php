@extends('layouts.app')

@section('title', 'Admin - Edit Booking')

@section('content')
<div class="container">
    <div class="card shadow-sm mx-auto my-4" style="max-width:720px">
        <div class="card-header bg-white py-4 pr-4 pl-0 d-flex align-items-center justify-content-between">
            <div>
                <h5 class="mb-0">Edit Booking (Admin)</h5>
            </div>
            <div>
                <a href="{{ route('admin.bookings.index') }}" class="btn btn-light">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.bookings.update', $booking) }}" method="post">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Ruangan</label>
                    <select name="room_id" class="form-select">
                        @foreach($rooms as $r)
                            <option value="{{ $r->id }}" {{ $r->id == old('room_id', $booking->room_id) ? 'selected' : '' }}>{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Judul Kegiatan</label>
                    <input name="title" class="form-control" value="{{ old('title', $booking->title) }}">
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
                            <option value="Meeting Internal" {{ ($pval === 'Meeting Internal') ? 'selected' : '' }}>Meeting Internal</option>
                            <option value="Meeting Mitra Fundraising" {{ ($pval === 'Meeting Mitra Fundraising') ? 'selected' : '' }}>Meeting Mitra Fundraising</option>
                            <option value="Meeting Mitra Program" {{ ($pval === 'Meeting Mitra Program') ? 'selected' : '' }}>Meeting Mitra Program</option>
                            <option value="Meeting Mitra Implementasi" {{ ($pval === 'Meeting Mitra Implementasi') ? 'selected' : '' }}>Meeting Mitra Implementasi</option>
                            <option value="Meeting DPS" {{ ($pval === 'Meeting DPS') ? 'selected' : '' }}>Meeting DPS</option>
                            <option value="__other__" {{ $showOther ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        <input id="purpose_other" type="text" class="form-control purpose-other {{ $showOther ? '' : 'd-none' }}" placeholder="Tuliskan tujuan lain..." value="{{ $showOther ? $pval : '' }}">
                    </div>
                    {{-- hidden purpose field sent to server --}}
                    <input type="hidden" id="purpose_hidden" name="purpose" value="{{ old('purpose', $booking->purpose) }}">
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
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Divisi</label>
                        <select id="division" name="division" class="form-select">
                            <option value="">-- Pilih Divisi --</option>
                        </select>
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Mitra (jika tidak ada diisi '-')</label>
                        <input name="partner" class="form-control" value="{{ old('partner', $booking->partner) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Jumlah Peserta (Internal / Eksternal)</label>
                        <div class="d-flex gap-2">
                            <input name="participants_internal" type="number" min="0" class="form-control" value="{{ old('participants_internal', $booking->participants_internal) }}" placeholder="Internal">
                            <input name="participants_external" type="number" min="0" class="form-control" value="{{ old('participants_external', $booking->participants_external) }}" placeholder="Eksternal">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Kebutuhan Fasilitas (jika tidak ada diisi '-')</label>
                    <textarea name="facilities" rows="2" class="form-control">{{ old('facilities', $booking->facilities) }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="pending" {{ old('status', $booking->status) === 'pending' ? 'selected' : '' }}>pending</option>
                        <option value="approved" {{ old('status', $booking->status) === 'approved' ? 'selected' : '' }}>approved</option>
                        <option value="cancelled" {{ old('status', $booking->status) === 'cancelled' ? 'selected' : '' }}>cancelled</option>
                        <option value="done" {{ old('status', $booking->status) === 'done' ? 'selected' : '' }}>done</option>
                    </select>
                </div>

                <div class="d-grid">
                    <button class="btn btn-success">Save</button>
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
                            altInput: true,
                            altFormat: 'd-m-Y H:i',  // yang tampil ke user
                            disableMobile: true
                        };
                        flatpickr('#start_picker', fpOpts);
                        flatpickr('#end_picker', fpOpts);
                    }

                    const form = document.querySelector('form[action*="admin/bookings"]');
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

                    // directorate -> divisions mapping for edit (same as user edit)
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


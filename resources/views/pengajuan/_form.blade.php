@php
$pengajuan = $pengajuan ?? null;
$rows = old('kebutuhan');
if (!$rows) {
if ($pengajuan && $pengajuan->kebutuhan->count()) {
$rows = $pengajuan->kebutuhan->map(fn ($k) => [
'jenis_relawan' => $k->jenis_relawan,
'jenis_kelamin' => $k->jenis_kelamin,
'detail_tugas' => $k->detail_tugas,
'nominal_apresiasi' => $k->nominal_apresiasi,
])->values()->toArray();
} else {
$rows = [['jenis_kelamin' => 'LP']];
}
}
$rows = array_values($rows);
$fmt = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('Y-m-d\TH:i') : '';
@endphp

<h5 class="mb-3"><i class="bi bi-clipboard-data me-1"></i>Informasi Kegiatan</h5>
<div class="row g-3">
    <div class="col-12 col-md-6">
        <label class="form-label">Direktorat</label>
        <select id="direktorat" name="direktorat" class="form-select">
            <option value="">-- Pilih Direktorat --</option>
            @php
            $selectedDirectorate = old('direktorat', $pengajuan->direktorat ?? '');
            @endphp
            @foreach(['GNPE', 'SPDE', 'IHPN', 'BIC', 'SOSM', 'ASHUM'] as $dir)
            <option value="{{ $dir }}" {{ $selectedDirectorate===$dir ? 'selected' : '' }}>{{ $dir }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label">Divisi</label>
        <select id="divisi" name="divisi" class="form-select"
            data-initial="{{ old('divisi', $pengajuan->divisi ?? '') }}">
            <option value="">-- Pilih Divisi --</option>
        </select>
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label">Nama PIC / Pengaju</label>
        <input type="text" name="nama_pic" class="form-control" placeholder="Nama PIC / Pengaju"
            value="{{ old('nama_pic', $pengajuan->nama_pic ?? auth()->user()->name) }}">
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label">Nama Kegiatan <span class="text-danger">*</span></label>
        <input type="text" name="judul" class="form-control" placeholder="Masukkan nama kegiatan"
            value="{{ old('judul', $pengajuan->judul ?? '') }}" required>
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label">Waktu Mulai Pelaksanaan</label>
        <input type="datetime-local" name="waktu_mulai" class="form-control"
            value="{{ old('waktu_mulai', $fmt($pengajuan->waktu_mulai ?? null)) }}">
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label">Waktu Selesai</label>
        <input type="datetime-local" name="waktu_selesai" class="form-control"
            value="{{ old('waktu_selesai', $fmt($pengajuan->waktu_selesai ?? null)) }}">
    </div>
    <div class="col-12">
        <label class="form-label">Lokasi Kegiatan</label>
        <input type="text" name="lokasi" class="form-control" placeholder="Masukkan lokasi kegiatan"
            value="{{ old('lokasi', $pengajuan->lokasi ?? '') }}">
    </div>
    <div class="col-12">
        <label class="form-label">Keterangan</label>
        <textarea name="keterangan" class="form-control" rows="2"
            placeholder="Keterangan tambahan (opsional)">{{ old('keterangan', $pengajuan->keterangan ?? '') }}</textarea>
    </div>
</div>

<hr class="my-4">

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
    <h5 class="mb-0"><i class="bi bi-people me-1"></i>Detail Kebutuhan Relawan</h5>
    <div class="head-actions">
        <button type="button" id="add-kebutuhan" class="btn btn-sm btn-outline-success"><i
                class="bi bi-plus-lg me-1"></i>Tambah Kebutuhan</button>
    </div>
</div>
<p class="text-muted small">Tambahkan satu baris untuk tiap jenis relawan yang dibutuhkan. Admin akan mencarikan &
    menugaskan relawan per baris.</p>

<div id="kebutuhan-container">
    @foreach($rows as $i => $row)
    @include('pengajuan._kebutuhan_row', ['i' => $i, 'row' => $row])
    @endforeach
</div>

<template id="kebutuhan-template">
    @include('pengajuan._kebutuhan_row', ['i' => '__INDEX__', 'row' => []])
</template>

@push('head')
<style>
    /* Pasangan select + input "Lainnya" disejajarkan, mengikuti pola Tujuan Kegiatan. */
    .jenis-pair {
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    .jenis-pair>select {
        flex: 1 1 auto;
        min-width: 0;
    }

    .jenis-pair>.input-jenis-custom {
        flex: 0 0 auto;
        width: auto;
        min-width: 0;
    }

    .jenis-pair.wide-other>select {
        flex: 0 0 40%;
        max-width: 40%;
    }

    .jenis-pair.wide-other>.input-jenis-custom {
        flex: 1 1 auto;
    }

    @media (max-width: 575.98px) {
        .jenis-pair.wide-other {
            flex-wrap: wrap;
        }

        .jenis-pair.wide-other>select,
        .jenis-pair.wide-other>.input-jenis-custom {
            flex: 1 1 100%;
            max-width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
    var divisionMap = {
        'GNPE': ['GPN','PDE'],
        'SPDE': ['DCPE','SNP'],
        'IHPN': ['PND','PSS','PSI'],
        'BIC': ['BCMR','SMO'],
        'SOSM': ['HC','GALC','ACC & FIN','IT'],
        'ASHUM': ['ASHUM']
    };

    function populateDivisions(selectedDirectorate, selectedDivision) {
        var divEl = document.getElementById('divisi');
        if (!divEl) return;
        divEl.innerHTML = '<option value="">-- Pilih Divisi --</option>';
        if (!selectedDirectorate || !divisionMap[selectedDirectorate]) return;
        var foundSelected = false;
        divisionMap[selectedDirectorate].forEach(function(d) {
            var opt = document.createElement('option');
            opt.value = d;
            opt.textContent = d;
            if (selectedDivision && selectedDivision === d) {
                opt.selected = true;
                foundSelected = true;
            }
            divEl.appendChild(opt);
        });
        if (selectedDivision && !foundSelected) {
            var opt = document.createElement('option');
            opt.value = selectedDivision;
            opt.textContent = selectedDivision;
            opt.selected = true;
            divEl.appendChild(opt);
        }
    }

    var directorateEl = document.getElementById('direktorat');
    var divisiEl = document.getElementById('divisi');
    if (directorateEl && divisiEl) {
        var initDirectorate = directorateEl.value || '';
        var initDivision = divisiEl.getAttribute('data-initial') || '';
        populateDivisions(initDirectorate, initDivision);

        directorateEl.addEventListener('change', function() {
            populateDivisions(this.value, '');
        });
    }

    var container = document.getElementById('kebutuhan-container');
    var tpl = document.getElementById('kebutuhan-template');
    var addBtn = document.getElementById('add-kebutuhan');
    if (!container || !tpl || !addBtn) return;
    var idx = {{ count($rows) }};

    function initJenisRelawanRow(item) {
        var select = item.querySelector('.select-jenis-relawan');
        var customInput = item.querySelector('.input-jenis-custom');
        var hiddenVal = item.querySelector('.jenis-relawan-val');
        var pair = item.querySelector('.jenis-pair');
        if (!select || !customInput || !hiddenVal) return;

        function sync() {
            if (select.value === 'lainnya') {
                customInput.classList.remove('d-none');
                customInput.required = true;
                select.required = false;
                if (pair) pair.classList.add('wide-other');
                hiddenVal.value = customInput.value.trim();
            } else {
                customInput.classList.add('d-none');
                customInput.required = false;
                select.required = true;
                if (pair) pair.classList.remove('wide-other');
                hiddenVal.value = select.value;
            }
        }

        select.addEventListener('change', function () {
            sync();
            if (select.value === 'lainnya') {
                customInput.focus();
            }
        });

        customInput.addEventListener('input', function () {
            if (select.value === 'lainnya') {
                hiddenVal.value = this.value.trim();
            }
        });

        sync();
    }

    function renumber() {
        var items = container.querySelectorAll('.kebutuhan-item');
        items.forEach(function (el, i) {
            var no = el.querySelector('.kebutuhan-no'); if (no) no.textContent = (i + 1);
            var btn = el.querySelector('.btn-remove-kebutuhan'); if (btn) btn.style.display = items.length > 1 ? '' : 'none';
        });
    }
    addBtn.addEventListener('click', function () {
        var html = tpl.innerHTML.replace(/__INDEX__/g, idx++);
        var wrap = document.createElement('div');
        wrap.innerHTML = html.trim();
        var node = wrap.firstElementChild;
        container.appendChild(node);
        initJenisRelawanRow(node);
        renumber();
        node.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
    container.addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-remove-kebutuhan'); if (!btn) return;
        if (container.querySelectorAll('.kebutuhan-item').length <= 1) return;
        btn.closest('.kebutuhan-item').remove();
        renumber();
    });

    container.querySelectorAll('.kebutuhan-item').forEach(initJenisRelawanRow);
    renumber();
})();
</script>
@endpush
@php
    $pengajuan = $pengajuan ?? null;
    $rows = old('kebutuhan');
    if (!$rows) {
        if ($pengajuan && $pengajuan->kebutuhan->count()) {
            $rows = $pengajuan->kebutuhan->map(fn ($k) => [
                'jenis_relawan'     => $k->jenis_relawan,
                'jenis_kelamin'     => $k->jenis_kelamin,
                'detail_tugas'      => $k->detail_tugas,
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
        <input type="text" name="direktorat" class="form-control" value="{{ old('direktorat', $pengajuan->direktorat ?? '') }}">
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label">Divisi</label>
        <input type="text" name="divisi" class="form-control" value="{{ old('divisi', $pengajuan->divisi ?? '') }}">
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label">Nama PIC / Pengaju</label>
        <input type="text" name="nama_pic" class="form-control" value="{{ old('nama_pic', $pengajuan->nama_pic ?? auth()->user()->name) }}">
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label">Nama Kegiatan <span class="text-danger">*</span></label>
        <input type="text" name="judul" class="form-control" value="{{ old('judul', $pengajuan->judul ?? '') }}" required>
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label">Waktu Mulai Pelaksanaan</label>
        <input type="datetime-local" name="waktu_mulai" class="form-control" value="{{ old('waktu_mulai', $fmt($pengajuan->waktu_mulai ?? null)) }}">
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label">Waktu Selesai</label>
        <input type="datetime-local" name="waktu_selesai" class="form-control" value="{{ old('waktu_selesai', $fmt($pengajuan->waktu_selesai ?? null)) }}">
    </div>
    <div class="col-12">
        <label class="form-label">Lokasi Kegiatan</label>
        <input type="text" name="lokasi" class="form-control" value="{{ old('lokasi', $pengajuan->lokasi ?? '') }}">
    </div>
    <div class="col-12">
        <label class="form-label">Keterangan</label>
        <textarea name="keterangan" class="form-control" rows="2" placeholder="Keterangan tambahan (opsional)">{{ old('keterangan', $pengajuan->keterangan ?? '') }}</textarea>
    </div>
</div>

<hr class="my-4">

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
    <h5 class="mb-0"><i class="bi bi-people me-1"></i>Detail Kebutuhan Relawan</h5>
    <div class="head-actions">
        <button type="button" id="add-kebutuhan" class="btn btn-sm btn-outline-success"><i class="bi bi-plus-lg me-1"></i>Tambah Kebutuhan</button>
    </div>
</div>
<p class="text-muted small">Tambahkan satu baris untuk tiap jenis relawan yang dibutuhkan. Admin akan mencarikan & menugaskan relawan per baris.</p>

<div id="kebutuhan-container">
    @foreach($rows as $i => $row)
        @include('pengajuan._kebutuhan_row', ['i' => $i, 'row' => $row])
    @endforeach
</div>

<template id="kebutuhan-template">
    @include('pengajuan._kebutuhan_row', ['i' => '__INDEX__', 'row' => []])
</template>

@push('scripts')
<script>
(function () {
    var container = document.getElementById('kebutuhan-container');
    var tpl = document.getElementById('kebutuhan-template');
    var addBtn = document.getElementById('add-kebutuhan');
    if (!container || !tpl || !addBtn) return;
    var idx = {{ count($rows) }};

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
        renumber();
        node.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
    container.addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-remove-kebutuhan'); if (!btn) return;
        if (container.querySelectorAll('.kebutuhan-item').length <= 1) return;
        btn.closest('.kebutuhan-item').remove();
        renumber();
    });
    renumber();
})();
</script>
@endpush

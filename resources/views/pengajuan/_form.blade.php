@php $pengajuan = $pengajuan ?? null; @endphp
<div class="row g-3">
    <div class="col-12">
        <label class="form-label">Judul Pengajuan <span class="text-danger">*</span></label>
        <input type="text" name="judul" class="form-control" value="{{ old('judul', $pengajuan->judul ?? '') }}" placeholder="mis. Butuh relawan medis untuk bakti sosial" required>
    </div>
    <div class="col-12">
        <label class="form-label">Deskripsi Kebutuhan <span class="text-danger">*</span></label>
        <textarea name="kebutuhan" class="form-control" rows="4" placeholder="Jelaskan kebutuhan SDM relawan: tugas, kualifikasi, durasi, dsb." required>{{ old('kebutuhan', $pengajuan->kebutuhan ?? '') }}</textarea>
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label">Bidang Relawan</label>
        <select name="bidang_relawan_id" class="form-select">
            <option value="">— Pilih bidang —</option>
            @foreach($bidangs as $b)
                <option value="{{ $b->id }}" @selected(old('bidang_relawan_id', $pengajuan->bidang_relawan_id ?? '') == $b->id)>{{ $b->nama }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label">Jumlah Relawan Dibutuhkan <span class="text-danger">*</span></label>
        <input type="number" name="jumlah_relawan" class="form-control" min="1" value="{{ old('jumlah_relawan', $pengajuan->jumlah_relawan ?? 1) }}" required>
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label">Tanggal Kegiatan</label>
        <input type="text" name="tanggal_kegiatan" class="form-control flatpickr-date" value="{{ old('tanggal_kegiatan', optional($pengajuan->tanggal_kegiatan ?? null)->format('Y-m-d')) }}" placeholder="Pilih tanggal">
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label">Lokasi</label>
        <input type="text" name="lokasi" class="form-control" value="{{ old('lokasi', $pengajuan->lokasi ?? '') }}" placeholder="mis. Balai Desa Sukamaju">
    </div>
</div>

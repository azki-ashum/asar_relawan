@php $relawan = $relawan ?? null; @endphp
<div class="row g-3">
    <div class="col-12 col-md-6">
        <label class="form-label">Nama <span class="text-danger">*</span></label>
        <input type="text" name="nama" class="form-control" value="{{ old('nama', $relawan->nama ?? '') }}" required>
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label">Bidang Relawan</label>
        <select name="bidang_relawan_id" class="form-select">
            <option value="">— Pilih bidang —</option>
            @foreach($bidangs as $b)
                <option value="{{ $b->id }}" @selected(old('bidang_relawan_id', $relawan->bidang_relawan_id ?? '') == $b->id)>{{ $b->nama }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label">Kontak (HP / WA)</label>
        <input type="text" name="kontak" class="form-control" value="{{ old('kontak', $relawan->kontak ?? '') }}" placeholder="08xxxxxxxxxx">
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $relawan->email ?? '') }}">
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label">Domisili</label>
        <input type="text" name="domisili" class="form-control" value="{{ old('domisili', $relawan->domisili ?? '') }}">
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            @foreach(\App\Models\Relawan::STATUSES as $key => $meta)
                <option value="{{ $key }}" @selected(old('status', $relawan->status ?? 'tersedia') === $key)>{{ $meta['label'] }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12">
        <label class="form-label">Keahlian / Skill</label>
        <textarea name="keahlian" class="form-control" rows="2" placeholder="mis. P3K, logistik, dokumentasi...">{{ old('keahlian', $relawan->keahlian ?? '') }}</textarea>
    </div>
    <div class="col-12">
        <label class="form-label">Catatan</label>
        <textarea name="catatan" class="form-control" rows="2">{{ old('catatan', $relawan->catatan ?? '') }}</textarea>
    </div>
</div>

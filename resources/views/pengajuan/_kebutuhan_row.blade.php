@php $i = $i ?? '__INDEX__'; $row = $row ?? []; @endphp
<div class="kebutuhan-item card border mb-2">
    <div class="card-body py-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            <span class="fw-semibold text-success text-truncate"><i class="bi bi-person-lines-fill me-1"></i>Kebutuhan Relawan <span class="kebutuhan-no"></span></span>
            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-kebutuhan flex-shrink-0" title="Hapus kebutuhan ini"><i class="bi bi-trash"></i></button>
        </div>
        <div class="row g-2">
            <div class="col-12 col-md-6">
                <label class="form-label small mb-1">Jenis Relawan <span class="text-danger">*</span></label>
                <select name="kebutuhan[{{ $i }}][jenis_relawan]" class="form-select form-select-sm" required>
                    <option value="">— pilih jenis —</option>
                    @foreach(\App\Models\KebutuhanRelawan::JENIS as $key => $label)
                        <option value="{{ $key }}" @selected(($row['jenis_relawan'] ?? '') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label small mb-1">Jenis Kelamin <span class="text-danger">*</span></label>
                <select name="kebutuhan[{{ $i }}][jenis_kelamin]" class="form-select form-select-sm" required>
                    @foreach(\App\Models\KebutuhanRelawan::JENIS_KELAMIN as $key => $label)
                        <option value="{{ $key }}" @selected(($row['jenis_kelamin'] ?? 'LP') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label small mb-1">Detail Tugas Relawan</label>
                <textarea name="kebutuhan[{{ $i }}][detail_tugas]" class="form-control form-control-sm" rows="2" placeholder="Uraikan tugas relawan untuk kebutuhan ini">{{ $row['detail_tugas'] ?? '' }}</textarea>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label small mb-1">Nominal Apresiasi (Rp)</label>
                <input type="number" min="0" step="1000" name="kebutuhan[{{ $i }}][nominal_apresiasi]" class="form-control form-control-sm" value="{{ $row['nominal_apresiasi'] ?? '' }}" placeholder="mis. 100000">
            </div>
        </div>
    </div>
</div>

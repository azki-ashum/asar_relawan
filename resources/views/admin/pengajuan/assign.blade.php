@extends('layouts.relawan')

@section('title', 'Penugasan Relawan')

@push('head')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<style>
    .ts-wrapper .ts-control {
        border-radius: 12px !important;
        border: 1px solid var(--line, #e8ecf2) !important;
        padding: 0.45rem 0.85rem !important;
        font-size: 0.88rem !important;
        background-color: #fff !important;
        box-shadow: none !important;
        min-height: 40px;
    }

    .ts-wrapper.single .ts-control:after {
        border-color: #64748b transparent transparent transparent !important;
        border-width: 5px 5px 0 5px !important;
        right: 14px !important;
    }

    .ts-wrapper.disabled {
        opacity: 0.65;
        cursor: not-allowed;
    }

    .ts-wrapper.disabled .ts-control {
        background-color: #f8fafc !important;
        cursor: not-allowed;
    }

    .ts-dropdown {
        border-radius: 12px !important;
        border: 1px solid var(--line, #e8ecf2) !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
        padding: 6px !important;
        margin-top: 4px !important;
        z-index: 1050 !important;
    }

    .ts-dropdown .dropdown-input-wrap {
        padding: 4px 4px 8px 4px !important;
    }

    .ts-dropdown .dropdown-input {
        border-radius: 8px !important;
        border: 1px solid #cbd5e1 !important;
        padding: 0.45rem 0.75rem !important;
        font-size: 0.85rem !important;
        outline: none !important;
        box-shadow: none !important;
    }

    .ts-dropdown .dropdown-input:focus {
        border-color: #16a34a !important;
        box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.15) !important;
    }

    .ts-dropdown .option {
        padding: 0.5rem 0.75rem !important;
        border-radius: 6px !important;
        font-size: 0.875rem !important;
    }

    .ts-dropdown .option.active,
    .ts-dropdown .option:hover {
        background-color: #f1f5f9 !important;
        color: #0f172a !important;
    }
</style>
@endpush

@section('content')
<div class="page-header mb-3">
    <div class="d-flex align-items-center gap-2 mb-2">
        <a href="{{ route('admin.pengajuan.show', $pengajuan) }}" class="btn-back"><i class="bi bi-arrow-left"></i></a>
        @include('pengajuan._status', ['status' => $pengajuan->status])
    </div>
    <h3 class="mb-0">Penugasan Relawan</h3>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <div class="row g-2 small">
            <div class="col-md-4"><span class="text-muted">Kegiatan:</span> <strong>{{ $pengajuan->judul }}</strong>
            </div>
            <div class="col-md-4"><span class="text-muted">Pengaju:</span> {{ $pengajuan->nama_pic ??
                $pengajuan->user->name }}</div>
            <div class="col-md-4"><span class="text-muted">Waktu:</span> {{ optional($pengajuan->waktu_mulai)->format('d
                M Y H:i') ?? '—' }}</div>
            <div class="col-md-8"><span class="text-muted">Lokasi:</span> {{ $pengajuan->lokasi ?? '—' }}</div>
            <div class="col-md-4"><span class="text-muted">Progres:</span> <strong>{{ $pengajuan->assignedCount() }}/{{
                    $pengajuan->kebutuhan->count() }}</strong> kebutuhan terisi</div>
        </div>
    </div>
</div>

@foreach($pengajuan->kebutuhan as $idx => $k)
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white border-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <span class="fw-semibold">#{{ $idx + 1 }} · {{ $k->jenisLabel() }}</span>
            <span class="badge badge-soft-secondary ms-1">{{ $k->jenisKelaminLabel() }}</span>
            {{-- @if($k->nominal_apresiasi)<span class="badge badge-soft-info ms-1">Rp {{
                number_format($k->nominal_apresiasi, 0, ',', '.') }}</span>@endif --}}
        </div>
        @if($k->isAssigned())<span class="badge badge-soft-success"><i
                class="bi bi-check-circle me-1"></i>Terisi</span>@else<span class="badge badge-soft-warning">Belum
            diisi</span>@endif
    </div>
    <div class="card-body">
        @if($k->detail_tugas)<div class="small text-muted mb-2"><i class="bi bi-list-task me-1"></i>{{ $k->detail_tugas
            }}</div>@endif

        @if($k->isAssigned())
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 bg-light rounded p-2">
            <div class="small">
                <i class="bi bi-person-badge text-success me-1"></i><strong>{{ $k->assignedName() }}</strong>
                @if($k->relawan_kontak) · {{ $k->relawan_kontak }}@endif
                @if($k->relawan_domisili) · {{ $k->relawan_domisili }}@endif
                @if(!$k->relawan_id)<span class="badge badge-soft-secondary ms-1">manual</span>@endif
            </div>
            <form action="{{ route('admin.pengajuan.kebutuhan.unassign', [$pengajuan, $k]) }}" method="post"
                class="swal-confirm" data-confirm="Batalkan penugasan relawan ini?">
                @csrf
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg me-1"></i>Batalkan</button>
            </form>
        </div>
        @else
        <form action="{{ route('admin.pengajuan.kebutuhan.assign', [$pengajuan, $k]) }}" method="post">
            @csrf
            <div class="mb-2">
                <label class="form-label small mb-1">Pilih relawan tersedia ({{ $k->jenisLabel() }})</label>
                <div class="d-flex align-items-center gap-2">
                    <div class="flex-grow-1">
                        <select name="relawan_id" class="form-select form-select-sm relawan-select"
                            placeholder="-- Pilih --">
                            <option value="">-- Pilih --</option>
                            @foreach($candidates[$k->id] as $cand)
                            <option value="{{ $cand->id }}">{{ $cand->nama }}@if($cand->domisili) — {{ $cand->domisili
                                }}@endif @if($cand->jenis_kelamin) ({{ $cand->jenis_kelamin }})@endif</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit"
                        class="btn btn-success flex-shrink-0 d-inline-flex align-items-center justify-content-center"
                        disabled style="height: 40px; padding: 0 1.25rem; border-radius: 12px; font-weight: 600;"><i
                            class="bi bi-check-lg me-1"></i>Tugaskan</button>
                </div>
                @if($candidates[$k->id]->isEmpty())
                <div class="form-text text-danger mt-1">Tidak ada relawan tersedia untuk jenis ini. <a
                        href="{{ route('admin.relawan.create') }}">Tambah relawan baru</a>.</div>
                @endif
            </div>
            {{-- Fitur isi manual dinonaktifkan sementara (bisa diaktifkan kembali dengan menghapus @if(false)) --}}
            @if(false)
            <div>
                <a class="small text-decoration-none" data-bs-toggle="collapse" href="#manual{{ $k->id }}"
                    role="button"><i class="bi bi-pencil-square me-1"></i>atau isi manual (Personal Volunteer
                    Management)</a>
                <div class="collapse mt-2" id="manual{{ $k->id }}">
                    <div class="row g-2">
                        <div class="col-md-4"><input type="text" name="relawan_nama"
                                class="form-control form-control-sm" placeholder="Nama relawan"></div>
                        <div class="col-md-4"><input type="text" name="relawan_kontak"
                                class="form-control form-control-sm" placeholder="No HP"></div>
                        <div class="col-md-4"><input type="text" name="relawan_domisili"
                                class="form-control form-control-sm" placeholder="Domisili"></div>
                    </div>
                </div>
            </div>
            @endif
        </form>
        @endif
    </div>
</div>
@endforeach

@if($pengajuan->status === 'disetujui')
<div class="card border-0 shadow-sm">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="small text-muted">
            @if($pengajuan->allAssigned())
            <i class="bi bi-check-circle text-success me-1"></i>Semua kebutuhan sudah terisi. Tandai siap ditugaskan
            untuk memberi tahu pengaju.
            @else
            <i class="bi bi-info-circle me-1"></i>Isi semua kebutuhan relawan terlebih dahulu untuk dapat menandai siap
            ditugaskan.
            @endif
        </div>
        <form action="{{ route('admin.pengajuan.tugaskan', $pengajuan) }}" method="post" class="swal-confirm"
            data-confirm="Tandai relawan siap ditugaskan? Pengaju akan diberi tahu untuk deployment."
            data-confirm-title="Konfirmasi Penugasan">
            @csrf
            <button class="btn btn-primary" @disabled(!$pengajuan->allAssigned())><i
                    class="bi bi-send-check me-1"></i>Tandai Siap Ditugaskan</button>
        </form>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.relawan-select').forEach(function (el) {
        var form = el.closest('form');
        if (!form) return;

        var submitBtn = form.querySelector('button[type="submit"]');
        var manualCollapse = form.querySelector('.collapse');
        var manualNameInput = form.querySelector('input[name="relawan_nama"]');
        var manualPhoneInput = form.querySelector('input[name="relawan_kontak"]');
        var manualDomisiliInput = form.querySelector('input[name="relawan_domisili"]');

        function checkSubmitState(tsInstance) {
            if (!submitBtn) return;
            var isManualOpen = manualCollapse && manualCollapse.classList.contains('show');
            if (isManualOpen) {
                var hasName = manualNameInput && manualNameInput.value.trim().length > 0;
                submitBtn.disabled = !hasName;
            } else {
                var selectedVal = tsInstance ? tsInstance.getValue() : el.value;
                var hasSelected = selectedVal && selectedVal.toString().trim() !== '';
                submitBtn.disabled = !hasSelected;
            }
        }

        var ts = new TomSelect(el, {
            plugins: ['dropdown_input'],
            create: false,
            placeholder: '-- Pilih --',
            allowEmptyOption: true,
            maxOptions: null, // default Tom Select cuma 50 opsi; kandidat bisa ratusan
            onInitialize: function() {
                var input = this.dropdown.querySelector('.dropdown-input');
                if (input) {
                    input.placeholder = 'Ketik untuk mencari relawan atau pilih...';
                }
            },
            onChange: function() {
                checkSubmitState(this);
            }
        });

        if (manualCollapse) {
            manualCollapse.addEventListener('show.bs.collapse', function () {
                ts.clear();
                ts.disable();
                checkSubmitState(ts);
            });
            manualCollapse.addEventListener('shown.bs.collapse', function () {
                checkSubmitState(ts);
                if (manualNameInput) manualNameInput.focus();
            });
            manualCollapse.addEventListener('hide.bs.collapse', function () {
                ts.enable();
                if (manualNameInput) manualNameInput.value = '';
                if (manualPhoneInput) manualPhoneInput.value = '';
                if (manualDomisiliInput) manualDomisiliInput.value = '';
                checkSubmitState(ts);
            });
        }

        if (manualNameInput) {
            manualNameInput.addEventListener('input', function () {
                checkSubmitState(ts);
            });
        }

        checkSubmitState(ts);
    });
});
</script>
@endpush
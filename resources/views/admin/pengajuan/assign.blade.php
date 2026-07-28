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

@php
$pending = $pengajuan->kebutuhan->filter(fn ($k) => !$k->isAssigned());
@endphp

<form action="{{ route('admin.pengajuan.kebutuhan.assign_bulk', $pengajuan) }}" method="post" id="bulk-assign-form">
    @csrf
    @foreach($pengajuan->kebutuhan as $idx => $k)
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white border-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <span class="fw-semibold">#{{ $idx + 1 }} · {{ $k->jenisLabel() }}</span>
                <span class="badge badge-soft-secondary ms-1">{{ $k->jenisKelaminLabel() }}</span>
            </div>
            @if($k->isAssigned())<span class="badge badge-soft-success"><i
                    class="bi bi-check-circle me-1"></i>Terisi</span>@else<span class="badge badge-soft-warning">Belum
                diisi</span>@endif
        </div>
        <div class="card-body">
            @if($k->detail_tugas)<div class="small text-muted mb-2"><i class="bi bi-list-task me-1"></i>{{
                $k->detail_tugas }}</div>@endif

            @if($k->isAssigned())
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 bg-light rounded p-2">
                <div class="small">
                    <i class="bi bi-person-badge text-success me-1"></i><strong>{{ $k->assignedName() }}</strong>
                    @if($k->relawan_kontak) · {{ $k->relawan_kontak }}@endif
                    @if($k->relawan_domisili) · {{ $k->relawan_domisili }}@endif
                    @if(!$k->relawan_id)<span class="badge badge-soft-secondary ms-1">manual</span>@endif
                </div>
                {{-- Form unassign diletakkan di luar form bulk (lihat bawah) agar tidak nested --}}
                <button type="submit" form="unassign-{{ $k->id }}" class="btn btn-sm btn-outline-danger"><i
                        class="bi bi-x-lg me-1"></i>Batalkan</button>
            </div>
            @else
            <label class="form-label small mb-1">Pilih relawan tersedia ({{ $k->jenisLabel() }})</label>
            <select name="assign[{{ $k->id }}]" class="form-select form-select-sm relawan-select"
                placeholder="-- Pilih --">
                <option value="">-- Pilih --</option>
                @foreach($candidates[$k->id] as $cand)
                <option value="{{ $cand->id }}">{{ $cand->nama }}@if($cand->domisili) — {{ $cand->domisili
                    }}@endif @if($cand->jenis_kelamin) ({{ $cand->jenis_kelamin }})@endif</option>
                @endforeach
            </select>
            @if($candidates[$k->id]->isEmpty())
            <div class="form-text text-danger mt-1">Tidak ada relawan tersedia untuk jenis ini. <a
                    href="{{ route('admin.relawan.create') }}">Tambah relawan baru</a>.</div>
            @endif
            @endif
        </div>
    </div>
    @endforeach

    @if($pending->isNotEmpty())
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="small text-muted" id="bulk-hint">
                <i class="bi bi-people me-1"></i>Pilih relawan untuk {{ $pending->count() }} kebutuhan di atas, lalu
                tugaskan sekaligus dalam satu klik.
            </div>
            <button type="submit" id="bulk-assign-btn" class="btn btn-success" disabled>
                <i class="bi bi-check-lg me-1"></i>Tugaskan{{ $pending->count() > 1 ? ' Semua' : '' }}
                <span class="badge bg-white text-success ms-1 d-none" id="bulk-count">0</span>
            </button>
        </div>
    </div>
    @endif
</form>

{{-- Form pembatalan penugasan, di luar form bulk agar HTML tetap valid --}}
@foreach($pengajuan->kebutuhan as $k)
@if($k->isAssigned())
<form id="unassign-{{ $k->id }}" action="{{ route('admin.pengajuan.kebutuhan.unassign', [$pengajuan, $k]) }}"
    method="post" class="swal-confirm d-none" data-confirm="Batalkan penugasan relawan ini?">
    @csrf
</form>
@endif
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
    var selects = Array.prototype.slice.call(document.querySelectorAll('.relawan-select'));
    if (!selects.length) return;

    var submitBtn = document.getElementById('bulk-assign-btn');
    var countBadge = document.getElementById('bulk-count');
    var hint = document.getElementById('bulk-hint');
    var instances = [];

    // Relawan yang sudah dipilih di baris lain di-disable agar tidak dobel tugas.
    function syncDuplicates() {
        var taken = {};
        instances.forEach(function (ts) {
            var v = ts.getValue();
            if (v) taken[v] = ts;
        });

        instances.forEach(function (ts) {
            Object.keys(ts.options).forEach(function (val) {
                if (!val) return;
                var isTakenElsewhere = taken[val] && taken[val] !== ts;
                var opt = ts.options[val];
                if (!!opt.disabled !== !!isTakenElsewhere) {
                    ts.updateOption(val, Object.assign({}, opt, { disabled: isTakenElsewhere }));
                }
            });
        });
    }

    function refreshState() {
        var filled = instances.filter(function (ts) {
            var v = ts.getValue();
            return v && v.toString().trim() !== '';
        }).length;

        if (submitBtn) submitBtn.disabled = filled === 0;
        if (countBadge) {
            countBadge.textContent = filled;
            countBadge.classList.toggle('d-none', filled === 0);
        }
        if (hint) {
            hint.innerHTML = filled === 0
                ? '<i class="bi bi-people me-1"></i>Pilih relawan untuk ' + instances.length + ' kebutuhan di atas, lalu tugaskan sekaligus dalam satu klik.'
                : '<i class="bi bi-check2-circle text-success me-1"></i>' + filled + ' dari ' + instances.length + ' kebutuhan siap ditugaskan.';
        }
    }

    selects.forEach(function (el) {
        var ts = new TomSelect(el, {
            plugins: ['dropdown_input'],
            create: false,
            placeholder: '-- Pilih --',
            allowEmptyOption: true,
            maxOptions: null, // default Tom Select cuma 50 opsi; kandidat bisa ratusan
            onInitialize: function () {
                var input = this.dropdown.querySelector('.dropdown-input');
                if (input) {
                    input.placeholder = 'Ketik untuk mencari relawan atau pilih...';
                }
            },
            onChange: function () {
                syncDuplicates();
                refreshState();
            }
        });
        instances.push(ts);
    });

    syncDuplicates();
    refreshState();
});
</script>
@endpush
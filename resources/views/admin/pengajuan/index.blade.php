@extends('layouts.relawan')

@section('title', 'Admin - Pengajuan')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h3 class="mb-0">Pengajuan Relawan</h3>
        <div class="text-muted small">Verifikasi pengajuan, lalu cari &amp; tugaskan relawan sesuai kebutuhan.</div>
    </div>
</div>

<div class="row g-3">
    {{-- Kolom kiri: kalender di atas, lalu ringkasan tiap tahap alur. --}}
    <div class="col-12 col-lg-4 col-xxl-3">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div id="dashboard-calendar" data-dates='@json(array_keys($calendarDates))'
                    data-items='@json($withSchedule)'></div>
                <div class="mt-2 small text-muted">
                    <ul class="mb-0 ps-3" style="line-height:1.4;">
                        <li><strong>Klik</strong> tanggal untuk melihat pengajuan pada hari itu.</li>
                        <li><strong>Titik hijau</strong> di pojok kiri = ada pengajuan pada tanggal tersebut.</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Ringkasan seluruh tahap alur; klik kartu untuk memfilter tabel. --}}
        @php
            $queue = [
                ['label' => 'Menunggu Verifikasi', 'value' => $counts['diajukan'],   'color' => 'text-secondary', 'icon' => 'bi-inbox',                  'status' => 'diajukan'],
                ['label' => 'Perlu Revisi',        'value' => $counts['revisi'],     'color' => 'text-danger',    'icon' => 'bi-arrow-counterclockwise', 'status' => 'revisi'],
                ['label' => 'Perlu Penugasan',     'value' => $counts['disetujui'],  'color' => 'text-info',      'icon' => 'bi-search',                 'status' => 'disetujui'],
                ['label' => 'Sedang Berjalan',     'value' => $counts['ditugaskan'], 'color' => 'text-warning',   'icon' => 'bi-person-check',           'status' => 'ditugaskan'],
                ['label' => 'Selesai',             'value' => $counts['selesai'],    'color' => 'text-success',   'icon' => 'bi-flag',                   'status' => 'selesai'],
                ['label' => 'Ditolak',             'value' => $counts['ditolak'],    'color' => 'text-danger',    'icon' => 'bi-x-circle',               'status' => 'ditolak'],
                ['label' => 'Terlambat Lapor',     'value' => $counts['terlambat'],  'color' => 'text-danger',    'icon' => 'bi-clock-history',          'status' => \App\Models\Pengajuan::FILTER_TERLAMBAT],
                ['label' => 'Terlewat Verifikasi', 'value' => $counts['terlewat'],   'color' => 'text-danger',    'icon' => 'bi-calendar-x',             'status' => \App\Models\Pengajuan::FILTER_TERLEWAT],
            ];
        @endphp
        <div class="row g-2">
            @foreach($queue as $q)
            @php $aktif = request('status') === $q['status']; @endphp
            <div class="col-6">
                <a href="{{ route('admin.pengajuan.index', array_filter(['status' => $q['status'], 'dari' => request('dari'), 'sampai' => request('sampai')])) }}"
                    class="text-decoration-none">
                    <div class="card border-0 shadow-sm stat-card h-100 {{ $aktif ? 'stat-card-active' : '' }}">
                        <div class="card-body d-flex align-items-center justify-content-between gap-2 py-3">
                            <div class="min-w-0">
                                <div class="text-muted small stat-card-label">{{ $q['label'] }}</div>
                                <div class="h3 mb-0 {{ $q['color'] }}">{{ $q['value'] }}</div>
                            </div>
                            <i class="bi {{ $q['icon'] }} fs-4 {{ $q['color'] }} flex-shrink-0"></i>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Kolom kanan: filter + tabel pengajuan. --}}
    <div class="col-12 col-lg-8 col-xxl-9">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <form method="get" class="row g-2 mb-3" id="filter-pengajuan">
                    <div class="col-12">
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                            placeholder="Cari kegiatan / divisi / pengaju...">
                    </div>
                    <div class="col-12 col-md-5">
                        {{-- Rentang tanggal kegiatan: satu input flatpickr, dua hidden field. --}}
                        <div class="date-range-control">
                            <i class="bi bi-calendar-range"></i>
                            <input type="text" id="rentang-tanggal" class="form-control form-control-sm"
                                placeholder="Semua tanggal kegiatan" autocomplete="off" readonly
                                data-dari="{{ request('dari') }}" data-sampai="{{ request('sampai') }}">
                            <button type="button"
                                class="date-range-clear {{ request('dari') || request('sampai') ? '' : 'd-none' }}"
                                aria-label="Hapus rentang tanggal"><i class="bi bi-x-lg"></i></button>
                        </div>
                        <input type="hidden" name="dari" value="{{ request('dari') }}">
                        <input type="hidden" name="sampai" value="{{ request('sampai') }}">
                    </div>
                    <div class="col-7 col-md-4">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Semua Status</option>
                            @foreach(\App\Models\Pengajuan::STATUSES as $key => $meta)
                            <option value="{{ $key }}" @selected(request('status')===$key)>{{ $meta['label'] }}</option>
                            @endforeach
                            @foreach(\App\Models\Pengajuan::FILTER_TURUNAN as $key => $label)
                            <option value="{{ $key }}" @selected(request('status')===$key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-5 col-md-3 d-grid">
                        <button class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i>Filter</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover table-stack tabel-pengajuan align-middle mb-0">
                <thead>
                    <tr>
                        <th>Kegiatan</th>
                        <th>Pengaju</th>
                        <th>Divisi</th>
                        <th>Kebutuhan</th>
                        <th>Waktu Kegiatan</th>
                        <th>Diajukan</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengajuan as $p)
                    @php
                    $mulai = $p->waktu_mulai;
                    $selesai = $p->waktu_selesai;
                    $lintasHari = $mulai && $selesai && !$mulai->isSameDay($selesai);
                    @endphp
                    <tr>
                        <td class="cell-title wrap">{{ $p->judul }}</td>
                        <td data-label="Pengaju">{{ $p->user->name ?? '—' }}</td>
                        <td data-label="Divisi">{{ $p->divisi ?? '—' }}</td>
                        <td data-label="Kebutuhan">{{ $p->jumlah_relawan }} Relawan</td>
                        {{-- Tanggal & jam disatukan: pada kegiatan lintas hari tiap tanggal
                        membawa jamnya sendiri supaya tidak terbaca sebagai rentang sehari. --}}
                        <td data-label="Waktu Kegiatan" class="text-nowrap">
                            <div>
                                @if(!$mulai)
                                —
                                @elseif($lintasHari)
                                {{ $mulai->translatedFormat('d M Y') }}, {{ $mulai->format('H:i') }}
                                <div class="small text-muted">s/d {{ $selesai->translatedFormat('d M Y') }}, {{
                                    $selesai->format('H:i') }}</div>
                                @else
                                {{ $mulai->translatedFormat('d M Y') }}
                                <div class="small text-muted">{{ $mulai->format('H:i') }}@if($selesai) – {{
                                    $selesai->format('H:i') }}@endif</div>
                                @endif
                            </div>
                        </td>
                        <td data-label="Diajukan" class="text-nowrap">
                            <div>
                                {{ $p->created_at->translatedFormat('d M Y') }}
                                <div class="small text-muted">{{ $p->created_at->format('H:i') }}</div>
                            </div>
                        </td>
                        <td data-label="Status" class="text-center">
                            @include('pengajuan._status_akhir', ['pengajuan' => $p])
                        </td>
                        <td class="cell-actions text-end">
                            <div class="d-flex gap-1 justify-content-end flex-wrap">
                                @if($p->status === 'diajukan')
                                <a href="{{ route('admin.pengajuan.show', $p) }}" class="btn btn-sm btn-primary"><i
                                        class="bi bi-clipboard-check me-1"></i>Verifikasi</a>
                                @elseif(in_array($p->status, ['disetujui', 'ditugaskan']))
                                <a href="{{ route('admin.pengajuan.assign_form', $p) }}"
                                    class="btn btn-sm btn-success"><i class="bi bi-person-plus me-1"></i>Penugasan</a>
                                <a href="{{ route('admin.pengajuan.show', $p) }}" class="btn btn-sm btn-light border"><i
                                        class="bi bi-eye me-1"></i>Detail</a>
                                @else
                                <a href="{{ route('admin.pengajuan.show', $p) }}" class="btn btn-sm btn-light border"><i
                                        class="bi bi-eye me-1"></i>Detail</a>
                                @endif
                                <form action="{{ route('admin.pengajuan.destroy', $p) }}" method="post"
                                    class="swal-confirm"
                                    data-confirm="Hapus pengajuan ini secara permanen dari database?">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i
                                            class="bi bi-trash me-1"></i>Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="no-card">
                        <td colspan="8">
                            <div class="empty-state"><i class="bi bi-inboxes"></i>Belum ada pengajuan yang cocok dengan
                                filter.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
                </div>
            </div>
            @if($pengajuan->hasPages())
            <div class="card-footer bg-white border-0 d-flex justify-content-end">{{ $pengajuan->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('head')
<style>
    .date-range-control {
        position: relative;
    }

    .date-range-control>.bi-calendar-range {
        position: absolute;
        left: .65rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--faint);
        pointer-events: none;
        font-size: .85rem;
    }

    .date-range-control .form-control {
        padding-left: 2rem;
        padding-right: 2rem;
        background: #fff;
        cursor: pointer;
    }

    .date-range-clear {
        position: absolute;
        right: .35rem;
        top: 50%;
        transform: translateY(-50%);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        padding: 0;
        border: 0;
        border-radius: 50%;
        background: var(--line-soft);
        color: var(--ink-soft);
        font-size: .6rem;
        line-height: 1;
    }

    .date-range-clear:hover {
        background: var(--line);
        color: var(--ink);
    }

    .stat-card-active {
        outline: 2px solid var(--brand);
        outline-offset: -1px;
    }

    /* Kolom kiri sempit: label kartu boleh turun dua baris daripada terpotong. */
    .stat-card-label {
        line-height: 1.25;
        font-size: .78rem;
    }

    /* ---- Kalender ringkas (dipakai bersama Beranda Pengaju) ---- */
    #dashboard-calendar .fc-daygrid-day-top a,
    #dashboard-calendar .fc-daygrid-day-number,
    #dashboard-calendar .fc-daygrid-day-number a {
        text-decoration: none !important;
        color: inherit !important;
        cursor: default !important;
    }

    #dashboard-calendar .fc-col-header-cell,
    #dashboard-calendar .fc-col-header-cell * {
        color: var(--faint) !important;
        text-decoration: none !important;
        cursor: default !important;
    }

    #dashboard-calendar .fc-toolbar {
        padding: 0 0 .5rem !important;
    }

    #dashboard-calendar .fc-toolbar .fc-toolbar-chunk:nth-child(2) {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: .75rem !important;
        flex-wrap: nowrap !important;
    }

    #dashboard-calendar .fc-toolbar-title {
        margin: 0 !important;
        font-size: .92rem !important;
        font-weight: 700 !important;
        text-transform: capitalize;
    }

    #dashboard-calendar .fc-col-header-cell {
        padding: .25rem 0 !important;
    }

    #dashboard-calendar .fc-col-header-cell-cushion {
        font-size: .68rem !important;
    }

    #dashboard-calendar .fc-daygrid-day {
        min-height: 26px;
    }

    #dashboard-calendar .fc-daygrid-day-top {
        justify-content: center;
        padding-top: 2px;
    }

    #dashboard-calendar .fc-daygrid-day-number {
        font-size: .78rem !important;
        padding: 2px !important;
    }

    #dashboard-calendar .fc-daygrid-day-frame {
        min-height: 26px !important;
        padding: 0 !important;
    }

    #dashboard-calendar .fc-daygrid-day-events {
        min-height: 0 !important;
        margin: 0 !important;
    }

    #dashboard-calendar .fc-scrollgrid,
    #dashboard-calendar .fc-scrollgrid td,
    #dashboard-calendar .fc-scrollgrid th {
        border-color: var(--line) !important;
    }

    #dashboard-calendar .fc-day-today {
        background: var(--brand-50) !important;
    }

    .calendar-nav-left,
    .calendar-nav-right {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        padding: 0;
        border-radius: 50%;
        background: #fff;
        border: 1px solid var(--line);
        color: var(--ink-soft);
        cursor: pointer;
        flex: 0 0 auto;
    }

    .calendar-nav-left:hover,
    .calendar-nav-right:hover {
        background: var(--line-soft);
        color: var(--ink);
    }

    .pengajuan-marker {
        position: absolute;
        top: 4px;
        left: 4px;
        width: 8px;
        height: 8px;
        background: var(--brand);
        border-radius: 50%;
        pointer-events: none;
        z-index: 5;
    }

    .min-w-0 {
        min-width: 0;
    }

    /* Label kartu ringkasan: kunci tinggi 2 baris supaya angka di bawahnya
       sejajar walau panjang teks label beda-beda (mis. "Selesai" vs "Menunggu Verifikasi"). */
    .stat-card-label {
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow: hidden;
        word-break: normal;
        overflow-wrap: normal;
        line-height: 1.25;
        min-height: 2.5em;
    }

    /* Tabel lebar: biarkan menggeser ke samping daripada membungkus tiap sel.
       Di bawah 768px layout sudah menumpuk jadi kartu, jadi jangan diutak-atik. */
    @media (min-width: 768px) {

        .tabel-pengajuan th,
        .tabel-pengajuan td {
            white-space: nowrap;
            vertical-align: middle;
        }

        .tabel-pengajuan td.cell-title {
            white-space: normal;
            min-width: 180px;
            max-width: 260px;
        }

        /* Tombol aksi tetap satu baris. */
        .tabel-pengajuan td.cell-actions>div {
            flex-wrap: nowrap !important;
        }
    }

    @media (max-width: 767.98px) {

        /* Kolom tanggal jangan dipaksa satu baris di kartu mobile, biar tidak
           kepotong pada kegiatan lintas hari ("s/d ..."). */
        .tabel-pengajuan.table-stack tbody td.text-nowrap {
            white-space: normal !important;
        }

        /* Tombol aksi: tetap berbagi rata dalam satu baris (2 tombol muat),
           tapi kalau ada 3 (Penugasan + Detail + Hapus) turun ke baris baru
           daripada diperas jadi sepertiga-sepertiga yang sempit. */
        .tabel-pengajuan.table-stack tbody td.cell-actions .d-flex>*,
        .tabel-pengajuan.table-stack tbody td.cell-actions .d-flex>form>* {
            flex: 1 1 0;
            min-width: 6.5rem;
        }
    }

    @media (min-width: 768px) {

        .table-responsive {
            scrollbar-width: thin;
            scrollbar-color: var(--line) transparent;
        }

        .table-responsive::-webkit-scrollbar {
            height: 8px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: var(--line);
            border-radius: 99px;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: var(--faint);
        }
    }

    /* Rentang terpilih pada flatpickr mengikuti warna brand. */
    .flatpickr-day.selected,
    .flatpickr-day.startRange,
    .flatpickr-day.endRange {
        background: var(--brand) !important;
        border-color: var(--brand) !important;
    }

    .flatpickr-day.inRange {
        background: var(--brand-50) !important;
        border-color: var(--brand-50) !important;
        box-shadow: -5px 0 0 var(--brand-50), 5px 0 0 var(--brand-50) !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.7/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.7/locales/id.global.min.js"></script>
<script src="{{ asset('js/relawan-dashboard-calendar.js') }}"></script>
{{-- Layout hanya memuat inti flatpickr; locale id ditambahkan di sini. --}}
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
<script>
    (function () {
        const input = document.getElementById('rentang-tanggal');
        if (!input || typeof flatpickr !== 'function') return;

        const form = document.getElementById('filter-pengajuan');
        const dari = form.querySelector('input[name="dari"]');
        const sampai = form.querySelector('input[name="sampai"]');
        const clearBtn = form.querySelector('.date-range-clear');

        const preset = [input.dataset.dari, input.dataset.sampai].filter(Boolean);

        const picker = flatpickr(input, {
            mode: 'range',
            dateFormat: 'Y-m-d',
            altInput: false,
            locale: 'id',
            disableMobile: true,
            defaultDate: preset.length ? preset : null,
            onChange: function (dates) {
                // Isi hidden field saja; penerapan filter menunggu tombol Filter
                // ditekan, sama seperti input pencarian dan dropdown status.
                dari.value = dates[0] ? picker.formatDate(dates[0], 'Y-m-d') : '';
                sampai.value = dates[1] ? picker.formatDate(dates[1], 'Y-m-d') : '';
                clearBtn.classList.toggle('d-none', !dates.length);
            },
        });

        clearBtn.addEventListener('click', function () {
            picker.clear();
            dari.value = '';
            sampai.value = '';
            clearBtn.classList.add('d-none');
        });
    })();
</script>
@endpush

@push('modals')
<div class="modal fade" id="pengajuanDateModal" tabindex="-1" aria-labelledby="pengajuanDateModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pengajuanDateModalLabel">Pengajuan pada <span
                        id="pengajuan-modal-date-label"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="pengajuan-modal-list">
                    <div class="text-muted">Memuat…</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endpush
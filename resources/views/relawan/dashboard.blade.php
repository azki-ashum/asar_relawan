@extends('layouts.relawan')

@section('title', 'Beranda')

@section('content')
{{-- Hero sapaan --}}
<div class="card hero-card border-0 mb-4 overflow-hidden"
    style="background:linear-gradient(120deg,#0f7a46 0%,#16a34a 55%,#22c55e 100%);">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3 text-white"
        style="padding:1.6rem 1.75rem;">
        <div>
            <div class="opacity-75 small mb-1"><i class="bi bi-calendar3 me-1"></i>{{ now()->translatedFormat('l, d F
                Y') }}</div>
            <h3 class="mb-1 text-white">Halo, {{ \Illuminate\Support\Str::title(auth()->user()->name ?? 'Pengaju') }} 👋
            </h3>
            <div class="opacity-75">Ringkasan pengajuan kebutuhan relawan Anda.</div>
        </div>
        <a href="{{ route('pengajuan.create') }}" class="btn btn-light fw-semibold"><i
                class="bi bi-plus-lg me-1"></i>Buat Pengajuan</a>
    </div>
</div>

{{-- Notifikasi aksi yang diperlukan --}}
@foreach($needAction as $p)
@if($p->status === 'revisi')
<div class="alert alert-warning d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div class="d-flex align-items-start gap-2">
        <i class="bi bi-arrow-counterclockwise mt-1"></i>
        <div>Pengajuan <strong>{{ $p->judul }}</strong> diminta <strong>revisi</strong> oleh Tim Ksatria.
            @if($p->catatan_revisi)<span class="d-block small mt-1 opacity-75">Catatan: {{ $p->catatan_revisi
                }}</span>@endif</div>
    </div>
    <a href="{{ route('pengajuan.edit', $p) }}" class="btn btn-sm btn-warning ms-auto">Perbaiki</a>
</div>
@else
<div class="alert alert-info d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div class="d-flex align-items-start gap-2">
        <i class="bi bi-person-check mt-1"></i>
        <div>Relawan untuk <strong>{{ $p->judul }}</strong> sudah ditugaskan.
            {{ $p->catatan_revisi ? 'Admin meminta revisi laporan.' : 'Unggah bukti & laporan untuk menyelesaikannya.'
            }}</div>
    </div>
    <a href="{{ route('pengajuan.show', $p) }}" class="btn btn-sm btn-success ms-auto">{{ $p->catatan_revisi ? 'Perbaiki
        Laporan' : 'Kirim Laporan' }}</a>
</div>
@endif
@endforeach

<div class="row g-3">
    {{-- Kartu statistik: kolom kiri, ditumpuk 1 kolom ke bawah --}}
    <div class="col-12 col-lg-3">
        <div class="row g-3">
            @php
            $tiles = [
            ['label' => 'Total Pengajuan', 'value' => $counts['total'], 'icon' => 'bi-collection', 'color' =>
            'text-primary'],
            ['label' => 'Diproses', 'value' => $counts['menunggu'], 'icon' => 'bi-hourglass-split', 'color' =>
            'text-info'],
            ['label' => 'Ditugaskan', 'value' => $counts['ditugaskan'], 'icon' => 'bi-person-check', 'color' =>
            'text-warning'],
            ['label' => 'Selesai', 'value' => $counts['selesai'], 'icon' => 'bi-flag', 'color' => 'text-success'],
            ];
            @endphp
            @foreach($tiles as $t)
            <div class="col-6 col-lg-12">
                <div class="card shadow-sm stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="text-muted small fw-semibold">{{ $t['label'] }}</span>
                            <i class="bi {{ $t['icon'] }} {{ $t['color'] }}"></i>
                        </div>
                        <div class="display-6 {{ $t['color'] }}">{{ $t['value'] }}</div>
                    </div>
                </div>
            </div>
            @endforeach

            {{-- Kalender: ditumpuk paling bawah pada kolom kartu kiri --}}
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div id="dashboard-calendar" data-dates='@json(array_keys($calendarDates))'
                            data-items='@json($withSchedule)'></div>
                        <div class="mt-2 small text-muted">
                            <ul class="mb-0 ps-3" style="line-height:1.4;">
                                <li><strong>Klik</strong> tanggal untuk melihat pengajuan pada hari itu.</li>
                                <li><strong>Titik hijau</strong> di pojok kiri = ada pengajuan pada tanggal
                                    tersebut.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Pengajuan minggu ini: kolom kanan --}}
    <div class="col-12 col-lg-9">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Pengajuan Minggu Ini</h5>
                <small class="text-muted d-none d-sm-inline">7 hari dari hari ini</small>
            </div>
            <div class="card-body">
                @php $hasAnyItem = collect($weekDays)->sum(fn($d) => count($d['items'])) > 0; @endphp
                @if(!$hasAnyItem)
                <div class="empty-state"><i class="bi bi-calendar-x"></i>Tidak ada pengajuan dalam 7 hari ke
                    depan.</div>
                @else
                @foreach($weekDays as $date => $day)
                @continue(count($day['items']) === 0)
                <div class="mb-4">
                    <h6 class="text-uppercase small fw-bold text-muted mb-2" style="letter-spacing:.04em;">
                        {{ $day['label'] }}</h6>
                    <div class="d-flex flex-column gap-2">
                        @foreach($day['items'] as $p)
                        <a href="{{ route('pengajuan.show', $p['id']) }}"
                            class="d-block border rounded-3 p-3 text-decoration-none text-reset week-pengajuan-item">
                            @php
                            $itemStart = \Carbon\Carbon::parse($p['waktu_mulai']);
                            $itemEnd = $p['waktu_selesai'] ? \Carbon\Carbon::parse($p['waktu_selesai']) : null;
                            $itemIsMultiDay = $itemEnd && !$itemStart->isSameDay($itemEnd);
                            @endphp
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                <div class="fw-bold min-w-0">{{ $p['judul'] }}</div>
                                <span class="badge {{ $p['status_class'] }} flex-shrink-0"><i
                                        class="bi {{ $p['status_icon'] }} me-1"></i>{{ $p['status_label'] }}</span>
                            </div>
                            <div class="text-muted small mt-2">
                                <i class="bi bi-clock me-1"></i>
                                @if($itemIsMultiDay)
                                {{ $itemStart->translatedFormat('d M Y H:i') }} &rarr; {{ $itemEnd->translatedFormat('d M Y H:i') }}
                                @else
                                {{ $itemStart->format('H:i') }}@if($itemEnd) – {{ $itemEnd->format('H:i') }}@endif
                                @endif
                            </div>
                            <div class="mt-2 d-flex flex-wrap gap-2">
                                @if($p['divisi'])
                                <span class="badge bg-light text-dark border fw-normal"><i
                                        class="bi bi-diagram-3 me-1"></i>{{ $p['divisi'] }}</span>
                                @endif
                                @if($p['lokasi'])
                                <span class="badge bg-light text-dark border fw-normal"><i
                                        class="bi bi-geo-alt me-1"></i>{{ $p['lokasi'] }}</span>
                                @endif
                                <span class="badge bg-light text-dark border fw-normal"><i
                                        class="bi bi-people me-1"></i>{{ $p['kebutuhan'] }} Relawan</span>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('head')
<style>
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

    .week-pengajuan-item:hover {
        background: var(--line-soft);
        border-color: var(--line) !important;
    }

    .min-w-0 {
        min-width: 0;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.7/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.7/locales/id.global.min.js"></script>
<script src="{{ asset('js/relawan-dashboard-calendar.js') }}"></script>
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
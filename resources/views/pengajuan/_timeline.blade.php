@php
    $step = $pengajuan->progressStep();
    $milestones = array_values(\App\Models\Pengajuan::MILESTONES);
    $icons = ['bi-send', 'bi-check2-circle', 'bi-person-check', 'bi-flag'];
    $fill = 75 * max(0, (min($step, 4) - 1)) / 3; // garis progres antar titik (pusat 12.5%..87.5%)
@endphp
<div class="sop-timeline">
    <div class="d-flex justify-content-between position-relative pt-1" style="min-height:62px">
        <div class="step-line position-absolute" style="top:20px;left:12.5%;width:75%;height:4px;background:var(--line);border-radius:999px;z-index:0"></div>
        @if($pengajuan->status !== 'ditolak')
            <div class="step-line position-absolute" style="top:20px;left:12.5%;width:{{ $fill }}%;height:4px;background:linear-gradient(90deg,var(--brand-600),var(--brand-700));border-radius:999px;z-index:0;transition:width .4s ease"></div>
        @endif
        @foreach($milestones as $i => $label)
            @php $n = $i + 1; $done = $step > $n; $current = $step === $n && $pengajuan->status !== 'ditolak'; @endphp
            <div class="text-center position-relative" style="z-index:1;flex:1">
                <div class="step-circle rounded-circle mx-auto d-flex align-items-center justify-content-center"
                     style="width:42px;height:42px;font-weight:700;font-size:.95rem;
                            background:{{ $done || $current ? 'var(--brand-600)' : '#fff' }};
                            color:{{ $done || $current ? '#fff' : 'var(--faint)' }};
                            border:2px solid {{ $done || $current ? 'var(--brand-600)' : 'var(--line)' }};
                            box-shadow:{{ $current ? '0 0 0 4px rgba(22,163,74,.15)' : 'none' }};
                            transition:all .3s ease;">
                    @if($done)<i class="bi bi-check-lg d-inline-flex align-items-center justify-content-center"></i>@else<i class="bi {{ $icons[$i] }} d-inline-flex align-items-center justify-content-center"></i>@endif
                </div>
                <div class="step-label small mt-2 {{ $current ? 'fw-bold text-success' : ($done ? 'fw-semibold' : 'text-muted') }}">{{ $label }}</div>
            </div>
        @endforeach
    </div>
    @if($pengajuan->isTerlambatLapor())
        <div class="alert alert-danger py-2.5 px-3 mb-0 mt-3 small d-flex align-items-center gap-2 rounded-3"><i class="bi bi-clock-history flex-shrink-0 fs-6"></i><div class="lh-sm"><strong>Terlambat lapor.</strong> Kegiatan sudah selesai {{ $pengajuan->terlambatSelama() }} lalu ({{ $pengajuan->waktu_selesai->format('d M Y, H:i') }}), bukti &amp; laporan belum masuk.</div></div>
    @elseif($pengajuan->laporanTerlambat())
        {{-- Laporan sudah masuk, tapi catatan keterlambatan tetap ditampilkan sebagai riwayat --}}
        <div class="alert alert-warning py-2.5 px-3 mb-0 mt-3 small d-flex align-items-center gap-2 rounded-3"><i class="bi bi-clock-history flex-shrink-0 fs-6"></i><div class="lh-sm"><strong>Laporan terlambat{{ $pengajuan->laporanTerlambatSelama() ? ' ' . $pengajuan->laporanTerlambatSelama() : '' }}.</strong> Batas laporan {{ optional($pengajuan->batasLapor())->format('d M Y, H:i') }} sudah terlewat saat bukti &amp; laporan dikirim.</div></div>
    @endif
    @if($pengajuan->status === 'revisi')
        <div class="alert alert-warning py-2.5 px-3 mb-0 mt-3 small d-flex align-items-center gap-2 rounded-3"><i class="bi bi-arrow-counterclockwise flex-shrink-0 fs-6"></i><div class="lh-sm"><strong>Perlu revisi.</strong> {{ $pengajuan->catatan_revisi }}</div></div>
    @elseif($pengajuan->status === 'ditolak')
        <div class="alert alert-secondary py-2.5 px-3 mb-0 mt-3 small d-flex align-items-center gap-2 rounded-3"><i class="bi bi-x-circle flex-shrink-0 fs-6"></i><div class="lh-sm"><strong>Pengajuan ditolak.</strong> {{ $pengajuan->catatan_revisi }}</div></div>
    @endif
</div>

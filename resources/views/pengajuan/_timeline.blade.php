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
                    @if($done)<i class="bi bi-check-lg"></i>@else<i class="bi {{ $icons[$i] }}"></i>@endif
                </div>
                <div class="step-label small mt-2 {{ $current ? 'fw-bold text-success' : ($done ? 'fw-semibold' : 'text-muted') }}">{{ $label }}</div>
            </div>
        @endforeach
    </div>
    @if($pengajuan->status === 'revisi')
        <div class="alert alert-warning py-2 mb-0 mt-2 small d-flex align-items-start gap-2"><i class="bi bi-arrow-counterclockwise mt-1"></i><div><strong>Perlu revisi.</strong> {{ $pengajuan->catatan_revisi }}</div></div>
    @elseif($pengajuan->status === 'ditolak')
        <div class="alert alert-secondary py-2 mb-0 mt-2 small d-flex align-items-start gap-2"><i class="bi bi-x-circle mt-1"></i><div><strong>Pengajuan ditolak.</strong> {{ $pengajuan->catatan_revisi }}</div></div>
    @endif
</div>

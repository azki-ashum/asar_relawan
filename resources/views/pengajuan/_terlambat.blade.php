{{--
    Badge penanda di luar jadwal (Terlambat Lapor / Terlewat Verifikasi /
    Lapor terlambat) — pendamping _status, bukan pengganti.
    Pemakaian: @include('pengajuan._terlambat', ['pengajuan' => $p])
--}}
@php $meta = $pengajuan->penandaMeta(); @endphp
@if($meta)
    <span class="badge {{ $meta['class'] }} d-inline-flex align-items-center justify-content-center"
          title="{{ $meta['title'] }}">
        <i class="bi {{ $meta['icon'] }} me-1"></i>{{ $meta['label'] }}
    </span>
@endif

{{--
    Badge status tunggal untuk seluruh halaman — pengganti kombinasi
    _status + _terlambat, cukup tampilkan kondisi terkini saja.
    Pemakaian: @include('pengajuan._status_akhir', ['pengajuan' => $p])
--}}
@php $meta = $pengajuan->statusAkhirMeta(); @endphp
<span class="badge {{ $meta['class'] }} d-inline-flex align-items-center justify-content-center"
      @if($meta['title']) title="{{ $meta['title'] }}" @endif>
    @if(!empty($meta['icon']))<i class="bi {{ $meta['icon'] }} me-1"></i>@endif{{ $meta['label'] }}
</span>

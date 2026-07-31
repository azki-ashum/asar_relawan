{{--
    Badge "Terlambat Lapor" — pendamping _status, bukan pengganti.
    Pemakaian: @include('pengajuan._terlambat', ['pengajuan' => $p])
--}}
@if($pengajuan->isTerlambatLapor())
    @php $meta = \App\Models\Pengajuan::TERLAMBAT_META; @endphp
    <span class="badge {{ $meta['class'] }} d-inline-flex align-items-center justify-content-center"
          title="Kegiatan selesai {{ $pengajuan->terlambatSelama() }} lalu, laporan belum masuk">
        <i class="bi {{ $meta['icon'] }} me-1"></i>{{ $meta['label'] }}
    </span>
@elseif($pengajuan->laporanTerlambat())
    <span class="badge badge-soft-secondary d-inline-flex align-items-center justify-content-center"
          title="Laporan masuk melewati batas waktu kegiatan">
        <i class="bi bi-clock-history me-1"></i>Lapor terlambat
    </span>
@endif

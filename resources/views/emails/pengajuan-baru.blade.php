@component('mail::message')
# Pengajuan Relawan Baru

Ada pengajuan kebutuhan relawan baru yang menunggu **verifikasi** Tim Ksatria.

**Nama Kegiatan:** {{ $pengajuan->judul }}
**Pengaju:** {{ $pengajuan->nama_pic ?? ($pengajuan->user->name ?? '-') }} ({{ $pengajuan->user->email ?? '-' }})
**Direktorat / Divisi:** {{ $pengajuan->direktorat ?? '-' }}{{ $pengajuan->divisi ? ' / '.$pengajuan->divisi : '' }}
**Waktu:** {{ optional($pengajuan->waktu_mulai)->format('d M Y H:i') ?? '-' }}
**Lokasi:** {{ $pengajuan->lokasi ?? '-' }}
**Jumlah kebutuhan:** {{ $pengajuan->kebutuhan->count() }} baris

@if($pengajuan->kebutuhan->count())
**Rincian kebutuhan:**
@foreach($pengajuan->kebutuhan as $i => $k)
{{ $i + 1 }}. {{ \App\Models\KebutuhanRelawan::JENIS[$k->jenis_relawan] ?? $k->jenis_relawan }} — {{ \App\Models\KebutuhanRelawan::JENIS_KELAMIN[$k->jenis_kelamin] ?? $k->jenis_kelamin }}@if($k->nominal_apresiasi) (Rp {{ number_format($k->nominal_apresiasi, 0, ',', '.') }})@endif

@endforeach
@endif

@component('mail::button', ['url' => route('admin.pengajuan.show', $pengajuan)])
Verifikasi Pengajuan
@endcomponent

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent

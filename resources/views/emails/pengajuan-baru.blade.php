@component('mail::message')
# Pengajuan Relawan Baru

@include('emails.partials.badge', ['tone' => 'warning', 'text' => 'Menunggu Verifikasi'])

Ada pengajuan kebutuhan relawan baru yang menunggu verifikasi Tim Ksatria.

@include('emails.partials.detail', ['rows' => [
    'Nama Kegiatan'      => $pengajuan->judul,
    'Pengaju'            => ($pengajuan->nama_pic ?? ($pengajuan->user->name ?? '-'))
                            . ' · ' . ($pengajuan->user->email ?? '-'),
    'Direktorat / Divisi'=> ($pengajuan->direktorat ?? '-') . ($pengajuan->divisi ? ' / '.$pengajuan->divisi : ''),
    'Waktu'              => optional($pengajuan->waktu_mulai)->format('d M Y · H:i') ?? '-',
    'Lokasi'             => $pengajuan->lokasi ?? '-',
]])

@if($pengajuan->kebutuhan->count())
**Rincian kebutuhan** ({{ $pengajuan->kebutuhan->count() }} baris)

@include('emails.partials.items', ['items' => $pengajuan->kebutuhan->map(fn ($k) => [
    'label' => $k->jenisLabel(),
    'meta'  => $k->jenisKelaminLabel() . ($k->detail_tugas ? ' · ' . $k->detail_tugas : ''),
])->all()])
@endif

@component('mail::button', ['url' => route('admin.pengajuan.show', $pengajuan), 'color' => 'success'])
Verifikasi Pengajuan
@endcomponent

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent

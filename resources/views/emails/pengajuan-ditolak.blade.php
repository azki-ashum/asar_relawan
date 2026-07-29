@component('mail::message')
# Pengajuan Ditolak

@include('emails.partials.badge', ['tone' => 'danger', 'text' => 'Ditolak'])

Halo {{ $pengajuan->nama_pic ?? ($pengajuan->user->name ?? '') }}, mohon maaf, pengajuan kebutuhan relawan berikut **tidak dapat kami proses**.

@include('emails.partials.detail', ['rows' => [
    'Nama Kegiatan' => $pengajuan->judul,
    'Waktu'         => optional($pengajuan->waktu_mulai)->format('d M Y · H:i') ?? '-',
    'Lokasi'        => $pengajuan->lokasi ?? '-',
]])

@if($pengajuan->catatan_revisi)
@include('emails.partials.note', [
    'tone'  => 'danger',
    'title' => 'Alasan dari Tim Ksatria',
    'body'  => $pengajuan->catatan_revisi,
])
@endif

Bila kegiatan tetap membutuhkan relawan, silakan buat pengajuan baru atau hubungi Tim Ksatria untuk mendiskusikannya.

@component('mail::button', ['url' => route('pengajuan.show', $pengajuan), 'color' => 'error'])
Lihat Pengajuan
@endcomponent

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent

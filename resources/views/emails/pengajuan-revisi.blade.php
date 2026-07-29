@component('mail::message')
# {{ $tahap === 'laporan' ? 'Revisi Laporan Diminta' : 'Pengajuan Perlu Revisi' }}

@include('emails.partials.badge', ['tone' => 'warning', 'text' => $tahap === 'laporan' ? 'Revisi Laporan' : 'Perlu Revisi'])

Halo {{ $pengajuan->nama_pic ?? ($pengajuan->user->name ?? '') }}, Tim Ksatria mengembalikan {{ $tahap === 'laporan' ? 'bukti implementasi / laporan' : 'pengajuan' }} berikut untuk Anda perbaiki.

@include('emails.partials.detail', ['rows' => [
    'Nama Kegiatan' => $pengajuan->judul,
    'Waktu'         => optional($pengajuan->waktu_mulai)->format('d M Y · H:i') ?? '-',
    'Lokasi'        => $pengajuan->lokasi ?? '-',
]])

@if($pengajuan->catatan_revisi)
@include('emails.partials.note', [
    'tone'  => 'warning',
    'title' => 'Catatan revisi',
    'body'  => $pengajuan->catatan_revisi,
])
@endif

@component('mail::button', ['url' => $tahap === 'laporan' ? route('pengajuan.show', $pengajuan) : route('pengajuan.edit', $pengajuan), 'color' => 'success'])
{{ $tahap === 'laporan' ? 'Perbaiki Laporan' : 'Perbaiki Pengajuan' }}
@endcomponent

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent

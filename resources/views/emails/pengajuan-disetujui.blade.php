@component('mail::message')
# Pengajuan Disetujui

@include('emails.partials.badge', ['tone' => 'success', 'text' => 'Disetujui'])

Halo {{ $pengajuan->nama_pic ?? ($pengajuan->user->name ?? '') }}, pengajuan kebutuhan relawan Anda sudah **diverifikasi dan disetujui** oleh Tim Ksatria. Selanjutnya kami akan mencarikan dan menugaskan relawan untuk setiap kebutuhan.

@include('emails.partials.detail', ['rows' => [
    'Nama Kegiatan'       => $pengajuan->judul,
    'Direktorat / Divisi' => ($pengajuan->direktorat ?? '-') . ($pengajuan->divisi ? ' / '.$pengajuan->divisi : ''),
    'Waktu'               => optional($pengajuan->waktu_mulai)->format('d M Y · H:i') ?? '-',
    'Lokasi'              => $pengajuan->lokasi ?? '-',
    'Jumlah Kebutuhan'    => $pengajuan->kebutuhan->count() . ' baris',
]])

Anda akan menerima email berikutnya begitu seluruh relawan selesai ditugaskan.

@component('mail::button', ['url' => route('pengajuan.show', $pengajuan), 'color' => 'success'])
Lihat Pengajuan
@endcomponent

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent

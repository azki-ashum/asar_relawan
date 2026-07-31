@component('mail::message')
# Laporan Kegiatan Belum Masuk

@include('emails.partials.badge', ['tone' => 'danger', 'text' => 'Terlambat Lapor'])

Halo {{ $pengajuan->nama_pic ?? ($pengajuan->user->name ?? '') }}, kegiatan berikut **sudah melewati waktu selesai** tetapi bukti implementasi &amp; laporan belum kami terima. Mohon segera dilengkapi agar pengajuan bisa ditutup dan relawan kembali tersedia untuk penugasan lain.

@include('emails.partials.detail', ['rows' => [
    'Nama Kegiatan' => $pengajuan->judul,
    'Waktu Selesai' => optional($pengajuan->waktu_selesai)->format('d M Y · H:i') ?? '-',
    'Terlambat'     => $pengajuan->terlambatSelama() ?? '-',
    'Lokasi'        => $pengajuan->lokasi ?? '-',
    'Relawan'       => $pengajuan->assignedCount() . ' orang ditugaskan',
]])

@include('emails.partials.note', [
    'tone'  => 'danger',
    'title' => 'Yang perlu dikirim',
    'body'  => "1. Foto bukti implementasi kegiatan\n2. Laporan singkat / evaluasi pelaksanaan",
])

@component('mail::button', ['url' => route('pengajuan.show', $pengajuan), 'color' => 'success'])
Kirim Laporan Sekarang
@endcomponent

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent

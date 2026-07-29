@component('mail::message')
# Relawan Siap Ditugaskan

@include('emails.partials.badge', ['tone' => 'info', 'text' => 'Siap Deployment'])

Halo {{ $pengajuan->nama_pic ?? ($pengajuan->user->name ?? '') }}, seluruh kebutuhan relawan pada pengajuan Anda **sudah terisi**. Silakan lanjutkan ke tahap deployment, lalu unggah bukti implementasi & laporan setelah kegiatan selesai.

@include('emails.partials.detail', ['rows' => [
    'Nama Kegiatan' => $pengajuan->judul,
    'Waktu'         => optional($pengajuan->waktu_mulai)->format('d M Y · H:i') ?? '-',
    'Lokasi'        => $pengajuan->lokasi ?? '-',
]])

@if($pengajuan->kebutuhan->count())
**Relawan yang ditugaskan**

@include('emails.partials.items', ['items' => $pengajuan->kebutuhan->map(fn ($k) => [
    'label' => $k->assignedName() ?? 'Belum diisi',
    'meta'  => $k->jenisLabel() . ($k->relawan_kontak ? ' · ' . $k->relawan_kontak : '')
               . ($k->relawan_domisili ? ' · ' . $k->relawan_domisili : ''),
])->all()])
@endif

@component('mail::button', ['url' => route('pengajuan.show', $pengajuan), 'color' => 'success'])
Lihat Detail & Kontak Relawan
@endcomponent

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent

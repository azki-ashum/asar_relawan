@component('mail::message')
# Pengajuan Relawan Baru

Ada pengajuan kebutuhan relawan baru yang menunggu untuk diproses.

**Judul:** {{ $pengajuan->judul }}
**Pengaju:** {{ $pengajuan->user->name ?? '-' }} ({{ $pengajuan->user->email ?? '-' }})
**Bidang:** {{ $pengajuan->bidang->nama ?? '-' }}
**Jumlah relawan dibutuhkan:** {{ $pengajuan->jumlah_relawan }} orang
**Tanggal kegiatan:** {{ optional($pengajuan->tanggal_kegiatan)->format('d M Y') ?? '-' }}
**Lokasi:** {{ $pengajuan->lokasi ?? '-' }}

**Kebutuhan:**
{{ $pengajuan->kebutuhan }}

@component('mail::button', ['url' => route('admin.pengajuan.assign_form', $pengajuan)])
Cari & Tugaskan Relawan
@endcomponent

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent

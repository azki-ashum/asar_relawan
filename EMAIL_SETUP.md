# Notifikasi Email — Asar Relawan

Provider: **Google Workspace SMTP** (`smtp.gmail.com`), memakai akun domain `@asarhumanity.org`.
Gratis, domain sudah terverifikasi di Google, kuota kirim ±2.000 email/hari per akun.

---

## 1. Siapkan akun pengirim

1. Login ke [Google Admin Console](https://admin.google.com) sebagai admin `asarhumanity.org`.
2. Buat user pengirim, disarankan **`noreply@asarhumanity.org`** (boleh juga pakai akun yang sudah ada).
3. Login ke akun tersebut → **Aktifkan Verifikasi 2 Langkah** di
   [myaccount.google.com/security](https://myaccount.google.com/security).
   App Password **tidak akan muncul** tanpa 2FA aktif.
4. Buka [myaccount.google.com/apppasswords](https://myaccount.google.com/apppasswords) →
   buat App Password baru (nama bebas, mis. `Asar Relawan`).
   Google menampilkan 16 karakter seperti `abcd efgh ijkl mnop` — **salin tanpa spasi**.

> App Password hanya ditampilkan sekali. Kalau hilang, hapus dan buat baru.

## 2. Isi `.env`

```env
APP_URL=http://localhost:8004        # WAJIB benar — link di email dibuat dari sini

MAIL_MAILER=smtp                     # ubah dari "log" ke "smtp"
MAIL_SCHEME=null                     # null = STARTTLS (pasangan port 587)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=noreply@asarhumanity.org
MAIL_PASSWORD=abcdefghijklmnop       # App Password 16 digit, tanpa spasi
MAIL_FROM_ADDRESS="noreply@asarhumanity.org"
MAIL_FROM_NAME="${APP_NAME}"
```

Lalu bersihkan cache config:

```bash
php artisan config:clear
```

> `MAIL_FROM_ADDRESS` harus sama dengan `MAIL_USERNAME`, atau alamat yang terdaftar sebagai
> *Send mail as* di akun tersebut. Kalau berbeda, Google menimpanya dengan alamat login.

**Alternatif port 465 (SSL):** `MAIL_PORT=465` + `MAIL_SCHEME=smtps`.

## 3. Uji kirim

```bash
php artisan mail:test nama.anda@asarhumanity.org
```

Command ini mencetak konfigurasi aktif lalu mengirim langsung (**tanpa queue**), sehingga
error SMTP langsung terlihat di terminal.

## 4. Jalankan queue worker

Semua notifikasi dikirim lewat queue (`QUEUE_CONNECTION=database`). Tanpa worker, email hanya
menumpuk di tabel `jobs` dan **tidak pernah terkirim**:

```bash
php artisan queue:work
```

Untuk produksi, jalankan sebagai service (systemd / Supervisor / `nssm` di Windows) agar
otomatis restart. Job yang gagal masuk ke tabel `failed_jobs`:

```bash
php artisan queue:failed     # lihat daftar gagal
php artisan queue:retry all  # coba kirim ulang
```

---

## Daftar notifikasi

| Pemicu | Aksi | Penerima | Mailable |
|---|---|---|---|
| Pengaju submit pengajuan | `PengajuanController@store` | Semua admin | `PengajuanBaruMail` |
| Admin menyetujui | `Admin\PengajuanController@approve` | Pengaju | `PengajuanDisetujuiMail` |
| Admin minta revisi pengajuan | `Admin\PengajuanController@revisi` | Pengaju | `PengajuanRevisiMail` (`tahap: pengajuan`) |
| Admin menolak | `Admin\PengajuanController@reject` | Pengaju | `PengajuanDitolakMail` |
| Semua kebutuhan terisi → siap deploy | `Admin\PengajuanController@tugaskan` | Pengaju | `PengajuanDitugaskanMail` |
| Admin minta revisi laporan | `Admin\PengajuanController@revisiLaporan` | Pengaju | `PengajuanRevisiMail` (`tahap: laporan`) |

Semua pengiriman lewat satu pintu: [`App\Services\PengajuanNotifier`](app/Services/PengajuanNotifier.php).
Template email ada di [`resources/views/emails/`](resources/views/emails/).

**Menambah notifikasi baru:** buat Mailable (`implements ShouldQueue`) + view markdown, tambahkan
satu method statis di `PengajuanNotifier`, lalu panggil dari controller. Kegagalan kirim sengaja
ditelan dan dicatat ke log — aksi admin/pengaju tidak boleh batal hanya karena email gagal.

## Troubleshooting

| Gejala | Penyebab & solusi |
|---|---|
| `535 Username and Password not accepted` | App Password salah / masih pakai password login biasa. Buat ulang App Password, salin tanpa spasi. |
| App Password tidak muncul di Google | Verifikasi 2 Langkah belum aktif, atau admin domain memblokir "Less secure app". |
| Email tidak terkirim, tanpa error | Queue worker mati. Jalankan `php artisan queue:work`, cek tabel `failed_jobs`. |
| Link di email mengarah ke `localhost` yang salah | `APP_URL` di `.env` belum sesuai. Perbaiki lalu `php artisan config:clear` **dan restart queue worker**. |
| Ganti `.env` tapi tidak berefek | Queue worker memuat config saat start — restart worker setiap kali `.env` berubah. |
| Masuk folder Spam | Pastikan SPF Google (`include:_spf.google.com`) ada di DNS `asarhumanity.org` dan DKIM aktif di Admin Console → Apps → Gmail → Authenticate email. |

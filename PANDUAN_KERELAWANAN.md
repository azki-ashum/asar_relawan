# Panduan Membangun Sistem Pengajuan Kerelawanan

> Dokumen ini adalah **tutorial + flow per fase** untuk mengubah base project `asar_booking`
> (sistem booking ruangan/aset) menjadi **Sistem Pengajuan Kerelawanan**.
> Semua langkah setup & coding dikerjakan **oleh Anda sendiri**. Dokumen ini panduannya.
>
> Base: **Laravel 12 · PHP 8.2 · Blade · Tailwind CSS 4 · Vite · Login Google (Socialite)**

---

## Daftar Isi
1. [Konsep & Keputusan Terkunci](#1-konsep--keputusan-terkunci)
2. [Peta Reuse: Apa yang Sudah Ada](#2-peta-reuse-apa-yang-sudah-ada)
3. [Pemetaan Konsep: Booking → Kerelawanan](#3-pemetaan-konsep-booking--kerelawanan)
4. [Flow & Status Baru](#4-flow--status-baru)
5. [Fase 0 — Setup Project Baru](#fase-0--setup-project-baru)
6. [Fase 1 — Data SDM Relawan](#fase-1--data-sdm-relawan)
7. [Fase 2 — Pengajuan](#fase-2--pengajuan)
8. [Fase 3 — Penugasan (Assign Relawan)](#fase-3--penugasan-assign-relawan)
9. [Fase 4 — Bukti Implementasi](#fase-4--bukti-implementasi)
10. [Fase 5 — Notifikasi Email (WA menyusul)](#fase-5--notifikasi-email-wa-menyusul)
11. [Catatan Penting](#catatan-penting)
12. [Checklist Progres](#checklist-progres)

---

## 1. Konsep & Keputusan Terkunci

Sistem ini menerima **Pengajuan kebutuhan relawan** dari user, lalu **Admin mencari & menugaskan (assign) relawan** yang cocok, dan pengajuan **ditutup dengan foto bukti implementasi**.

Empat keputusan arsitektur yang sudah disepakati:

| # | Keputusan | Konsekuensi |
|---|-----------|-------------|
| 1 | **Project baru** hasil salin dari `asar_booking` | `asar_booking` lama tetap utuh & jalan |
| 2 | **Relawan = data saja (tanpa login)**, dikelola admin | Memetakan langsung ke pola `Asset`; yang login hanya **Pengaju + Admin** |
| 3 | **Email dulu**, WhatsApp menyusul | Email gratis via SMTP; WA butuh gateway berbayar (Fonnte/Twilio) |
| 4 | **Pengaju yang upload** foto bukti | Alur `complete()` + revisi yang sudah ada dipakai hampir apa adanya |

---

## 2. Peta Reuse: Apa yang Sudah Ada

Base ini sudah menyediakan ~65–70% kebutuhan. **3 fitur tersulit sudah ada padanannya:**

| Kebutuhan Relawan | Sudah ada di base | Lokasi |
|---|---|---|
| Upload **foto bukti** untuk menyelesaikan pengajuan | `complete()` wajib upload image ≤5MB ke disk `public` | `app/Http/Controllers/AssetBookingController.php` (~baris 368) |
| Admin **review & kembalikan** kalau bukti kurang | Alur **revision** (rollback + resubmit) | `AssetBookingController.php` (~baris 520 & 570) |
| **SDM dikelola admin** + import belakangan | CRUD Asset + pola seeder/migration | `AssetController.php`, `database/migrations/` |
| **Role admin** (super + per-direktorat) | Konstanta `ROLES` + manajemen user | `app/Http/Controllers/Admin/UserController.php` |
| **Mobile friendly** | Semua view sudah Tailwind responsif | `resources/views/` |
| Login | Google Socialite | `app/Http/Controllers/GoogleLoginController.php` |

**Yang perlu DIBANGUN BARU:**
- Notifikasi email (base baru punya `config/mail.php`, belum ada `Mail::`/`Mailable` terpasang).
- Layar admin **"Cari & Assign Relawan"** (langkah penugasan).

**Yang bisa DIBUANG** (menyederhanakan):
- Logika kalender & bentrok slot waktu.
- Scheduler `ExpireBookings`, `MarkOverdueBookings`, `MarkStartedBookings` (`app/Console/Commands/`).
- Field khusus kendaraan: `driver`, `fuel`, `destination`, `checked_out_at`, `returned_at`.

---

## 3. Pemetaan Konsep: Booking → Kerelawanan

| Konsep lama (booking) | Konsep baru (relawan) | Aksi |
|---|---|---|
| `Booking` | **`Pengajuan`** | Rename model, buang field waktu/kendaraan |
| `Asset` / `AssetType` | **`Relawan`** / **`BidangRelawan`** | Ganti dataset (skill, domisili, kontak, ketersediaan) |
| User memilih slot waktu sendiri | Pengaju **mendeskripsikan kebutuhan** | Hapus self-service kalender |
| *(tidak ada)* | **`pengajuan.relawan_id`** (hasil assign) | **BARU** — inti sistem |
| Foto bukti saat `complete` | Foto **bukti implementasi** | Reuse |
| Status `booking→in_use→done` + `overdue` | Status `diajukan→dicari→ditugaskan→selesai` | Sederhanakan |
| Directorate/division admin | Kategori/bidang kebutuhan | Reuse struktur role |

> **Catatan penamaan:** Anda bebas memilih nama tabel/field (variabel menyusul). Panduan ini pakai
> `pengajuan`, `relawan`, `bidang_relawan`, `penugasan` sebagai contoh yang konsisten.

---

## 4. Flow & Status Baru

```
1. Pengaju membuat Pengajuan (deskripsi kebutuhan SDM)   → status: DIAJUKAN
   └─► NOTIF EMAIL ke Admin  ★ (fitur baru — Fase 5)
2. Admin membuka pengajuan & mencari relawan cocok       → status: DICARI
3. Admin meng-assign relawan                             → status: DITUGASKAN
   └─► NOTIF muncul di dashboard Pengaju  ★
4. Pengaju upload foto bukti implementasi                → status: SELESAI
   └─► (opsional) Admin revisi kalau kurang → balik ke DITUGASKAN
```

Layar yang tinggal ganti konten (semua sudah mobile-friendly):
dashboard user · index pengajuan · form create · hub admin · admin index/edit · manajemen user/role.

---

## Fase 0 — Setup Project Baru

**Tujuan:** salinan project berjalan dengan identitas & database sendiri.

> **Status saat ini:** folder `asar_relawan` **sudah ada** sebagai salinan mentah `asar_booking`
> (tanpa `.git`, belum dikonfigurasi). Anda bisa langsung mulai dari langkah **0.2**.
> Kalau ingin menyalin ulang dari nol, hapus dulu `asar_relawan` lalu jalankan 0.1.

### 0.1 (Opsional) Salin ulang project
Dari folder `...\ASAR HUMANITY` (PowerShell):
```powershell
robocopy ".\asar_booking" ".\asar_relawan" /E /XD ".\asar_booking\.git"
```
> `robocopy` mengembalikan exit code 1 saat sukses menyalin — itu **normal**, bukan error.

### 0.2 Inisialisasi Git baru
```powershell
cd "C:\Users\AsarHumanity\Documents\ASAR HUMANITY\asar_relawan"
git init
git add .
git commit -m "Initial commit: base copied from asar_booking"
```

### 0.3 Ganti identitas app di `.env`
Buka `asar_relawan\.env`, ubah:
```env
APP_NAME=asar_relawan
DB_DATABASE=asar_relawan
```
> Jangan pakai `DB_DATABASE=asar_sibook` (itu database booking lama — bisa bentrok).

### 0.4 Ganti nama package (opsional, kosmetik) di `composer.json`
```json
"name": "asar/relawan",
"description": "Sistem Pengajuan Kerelawanan",
```

### 0.5 Generate APP_KEY baru
```powershell
php artisan key:generate
```

### 0.6 Buat database & migrasi
Buat database MySQL `asar_relawan` (via phpMyAdmin / MySQL client), lalu:
```powershell
php artisan migrate
```

### 0.7 Install dependency front-end & jalankan
```powershell
composer install   # kalau folder vendor belum lengkap
npm install
php artisan storage:link   # agar foto bukti bisa diakses publik
```
Jalankan (dua terminal):
```powershell
php artisan serve --port=8003
npm run dev
```
Buka `http://localhost:8003`. **Verifikasi Fase 0 selesai** bila halaman login tampil & migrasi bersih.

> **Google OAuth:** `GOOGLE_REDIRECT` di `.env` memakai `http://localhost:8003/google/callback`.
> Agar login Google jalan, URL itu harus terdaftar di Google Cloud Console project OAuth Anda.
> Untuk testing awal tanpa Google, Anda bisa bikin seeder user admin manual.

---

## Fase 1 — Data SDM Relawan

**Tujuan:** admin bisa CRUD data relawan (pengganti Asset).

### 1.1 Buat model + migration
```powershell
php artisan make:model Relawan -m
php artisan make:model BidangRelawan -m
```

### 1.2 Skema migration `relawan` (contoh — sesuaikan field)
```php
Schema::create('relawan', function (Blueprint $table) {
    $table->id();
    $table->string('nama');
    $table->string('kontak')->nullable();       // no. HP / WA
    $table->string('email')->nullable();
    $table->string('domisili')->nullable();
    $table->foreignId('bidang_relawan_id')->nullable()->constrained('bidang_relawan');
    $table->text('keahlian')->nullable();        // skill
    $table->enum('status', ['tersedia','ditugaskan','nonaktif'])->default('tersedia');
    $table->text('catatan')->nullable();
    $table->timestamps();
});
```

### 1.3 Controller admin (contek pola `AssetController`)
```powershell
php artisan make:controller Admin/RelawanController --resource
```
Salin pola `adminIndex/create/store/edit/update/destroy` dari `AssetController.php`,
ganti `Asset` → `Relawan`. Tambahkan route di `routes/web.php` di dalam grup `middleware('auth')`,
meniru blok `admin.assets.*`.

### 1.4 View
Salin `resources/views/admin/assets/{index,create,edit}.blade.php` → folder `admin/relawan/`,
ganti field & label. Sudah responsif — cukup ganti kolom.

### 1.5 Import CSV (belakangan)
Rencanakan endpoint `import` sederhana: upload CSV → `League\Csv` atau `fgetcsv` → `Relawan::create()`.
Bisa ditambahkan setelah CRUD manual jalan.

---

## Fase 2 — Pengajuan

**Tujuan:** ubah `Booking` menjadi `Pengajuan`.

### 2.1 Pendekatan
Cara paling aman: **buat model & migration `Pengajuan` baru** meniru struktur `Booking` yang relevan
(bukan rename langsung, supaya tidak menyeret field kendaraan). 

### 2.2 Skema migration `pengajuan` (contoh)
```php
Schema::create('pengajuan', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users');       // pengaju
    $table->foreignId('relawan_id')->nullable()->constrained('relawan'); // diisi saat assign
    $table->string('judul');
    $table->text('kebutuhan');           // deskripsi kebutuhan SDM
    $table->foreignId('bidang_relawan_id')->nullable()->constrained('bidang_relawan');
    $table->integer('jumlah_relawan')->default(1);
    $table->date('tanggal_kegiatan')->nullable();
    $table->string('lokasi')->nullable();
    $table->enum('status', ['diajukan','dicari','ditugaskan','selesai','ditolak','revisi'])
          ->default('diajukan');
    $table->json('bukti_implementasi')->nullable();   // path foto (reuse pola asset_snapshot)
    $table->text('catatan_revisi')->nullable();
    $table->integer('revisi_count')->default(0);
    $table->timestamp('selesai_at')->nullable();
    $table->timestamps();
});
```

### 2.3 Controller pengaju (contek `AssetBookingController`)
```powershell
php artisan make:controller PengajuanController
```
Ambil pola `index/create/store/edit/update/cancel` dari `AssetBookingController.php`.
**Buang** logika kalender, overdue, snapshot-saat-create. Simpan pola upload foto untuk Fase 4.

### 2.4 View
Salin `resources/views/asset/{bookings,create,edit}.blade.php` → `pengajuan/`, sederhanakan field
menjadi: judul, kebutuhan, bidang, jumlah, tanggal, lokasi.

---

## Fase 3 — Penugasan (Assign Relawan)

**Tujuan (INTI BARU):** admin mencari relawan cocok lalu meng-assign ke pengajuan.

### 3.1 Route (admin)
Tambahkan di grup `middleware('auth')`:
```php
Route::get('/admin/pengajuan', [Admin\PengajuanController::class, 'index'])->name('admin.pengajuan.index');
Route::get('/admin/pengajuan/{pengajuan}/assign', [Admin\PengajuanController::class, 'assignForm'])->name('admin.pengajuan.assign_form');
Route::post('/admin/pengajuan/{pengajuan}/assign', [Admin\PengajuanController::class, 'assign'])->name('admin.pengajuan.assign');
```

### 3.2 Logika `assign` (contoh)
```php
public function assign(Request $request, Pengajuan $pengajuan)
{
    $data = $request->validate([
        'relawan_id' => 'required|exists:relawan,id',
    ]);
    $pengajuan->update([
        'relawan_id' => $data['relawan_id'],
        'status'     => 'ditugaskan',
    ]);
    Relawan::where('id', $data['relawan_id'])->update(['status' => 'ditugaskan']);

    // Notif muncul di dashboard pengaju (badge/status). Email opsional di sini.
    return redirect()->route('admin.pengajuan.index')
        ->with('success', 'Relawan berhasil ditugaskan.');
}
```

### 3.3 Layar "Cari Relawan"
Di `assignForm`, tampilkan daftar relawan **status = tersedia**, difilter berdasarkan
`bidang_relawan_id` / `domisili` / `keahlian` pengajuan. Reuse pola tabel + search dari
`admin/users/index.blade.php` (sudah ada kotak pencarian `q`).

### 3.4 Notif ke dashboard pengaju
Di dashboard user (`resources/views/dashboard.blade.php`), tampilkan badge/notifikasi untuk
pengajuan berstatus `ditugaskan` beserta nama relawan yang di-assign.

---

## Fase 4 — Bukti Implementasi

**Tujuan:** pengaju menutup pengajuan dengan foto bukti. **Reuse hampir apa adanya.**

### 4.1 Route
```php
Route::post('/pengajuan/{pengajuan}/selesai', [PengajuanController::class, 'selesai'])->name('pengajuan.selesai');
Route::post('/pengajuan/{pengajuan}/resubmit', [PengajuanController::class, 'resubmit'])->name('pengajuan.resubmit');
```

### 4.2 Logika `selesai` (adaptasi dari `complete()` yang sudah ada)
```php
public function selesai(Request $request, Pengajuan $pengajuan)
{
    $request->validate([
        'bukti_file' => 'required|image|max:5120',   // ≤5MB
    ]);
    $path = $request->file('bukti_file')->store('bukti_implementasi', 'public');

    $pengajuan->update([
        'bukti_implementasi' => ['path' => $path, 'uploaded_at' => now()->toDateTimeString()],
        'status'     => 'selesai',
        'selesai_at' => now(),
    ]);
    if ($pengajuan->relawan) {
        $pengajuan->relawan->update(['status' => 'tersedia']);
    }
    return back()->with('success', 'Pengajuan selesai. Terima kasih!');
}
```
> Contek langsung baris upload di `AssetBookingController::complete()` (~baris 368–390) &
> alur revisi (~520/570) untuk fungsi admin `revisi()` + user `resubmit()`.

### 4.3 View
Tombol "Selesaikan" + input file di halaman detail pengajuan (hanya muncul saat status `ditugaskan`).
Contek form upload dari view asset booking.

---

## Fase 5 — Notifikasi Email (WA menyusul)

**Tujuan:** saat pengajuan masuk, admin dapat email.

### 5.1 Buat Mailable
```powershell
php artisan make:mail PengajuanBaruMail --markdown=emails.pengajuan-baru
```

### 5.2 Kirim saat pengajuan dibuat
Di `PengajuanController::store()`, setelah `Pengajuan::create(...)`:
```php
use App\Mail\PengajuanBaruMail;
use Illuminate\Support\Facades\Mail;

$admins = \App\Models\User::whereIn('role', ['admin', /* + admin per-bidang */])->pluck('email');
Mail::to($admins)->queue(new PengajuanBaruMail($pengajuan));
```
> `->queue()` (bukan `->send()`) agar tidak memblok respons. `QUEUE_CONNECTION=database` sudah aktif,
> jalankan worker: `php artisan queue:work`.

### 5.3 Konfigurasi SMTP di `.env`
Untuk dev cepat, biarkan `MAIL_MAILER=log` (email masuk ke `storage/logs/laravel.log`).
Untuk kirim sungguhan, isi kredensial SMTP (mis. Gmail App Password atau Mailtrap):
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=email-anda@gmail.com
MAIL_PASSWORD=app-password-16-digit
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@asarhumanity.org"
MAIL_FROM_NAME="${APP_NAME}"
```

### 5.4 WhatsApp (fase lanjutan)
Butuh **gateway berbayar** (Fonnte / Twilio / WA Business API) + nomor terdaftar + API key.
Polanya: buat Notification channel / service kecil yang POST ke API gateway saat `store()` & `assign()`.
Siapkan akun + API key dulu sebelum tahap ini.

---

## Catatan Penting

- **Mobile friendly:** seluruh view sudah Tailwind responsif — pertahankan pola `class` yang ada saat menyalin.
- **`php artisan storage:link`** wajib agar foto bukti (disk `public`) bisa diakses lewat URL.
- **Jangan pakai DB `asar_sibook`** untuk project baru — itu database booking lama.
- **Google OAuth redirect** harus cocok dengan yang terdaftar di Google Cloud Console.
- **Queue worker** (`php artisan queue:work`) harus jalan agar email terkirim (karena pakai `->queue()`).
- Hapus command scheduler kendaraan (`app/Console/Commands/*Overdue*, *Expire*, *Started*`) & referensinya di `app/Console/Kernel.php` bila tak dipakai.

---

## Checklist Progres

- [x] **Fase 0** — Salinan jalan: `migrate` bersih, halaman login tampil di `:8003` (DB `asar_relawan`, APP_NAME "Asar Relawan")
- [x] **Fase 1** — CRUD Relawan + Bidang Relawan (admin). Import CSV belum (opsional)
- [x] **Fase 2** — Pengaju bisa membuat, melihat, edit, & batalkan Pengajuan
- [x] **Fase 3** — Admin bisa cari & assign relawan; status → `ditugaskan`; notif di beranda pengaju
- [x] **Fase 4** — Pengaju upload foto bukti → status `selesai`; alur revisi (admin ⇄ pengaju) jalan
- [x] **Fase 5** — Email `PengajuanBaruMail` di-queue ke admin saat pengajuan masuk (MAIL_MAILER=log)
- [ ] **Lanjutan** — Notifikasi WhatsApp via gateway; import CSV relawan
```

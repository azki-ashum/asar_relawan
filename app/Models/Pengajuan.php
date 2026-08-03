<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengajuan extends Model
{
    use HasFactory;

    protected $table = 'pengajuan';

    protected $fillable = [
        'user_id',
        'direktorat',
        'divisi',
        'nama_pic',
        'judul',
        'waktu_mulai',
        'waktu_selesai',
        'lokasi',
        'keterangan',
        'jumlah_relawan',
        'status',
        'catatan_revisi',
        'revisi_count',
        'bukti_implementasi',
        'laporan',
        'selesai_at',
        'terlambat_at',
        'terlambat_notified_at',
    ];

    protected $casts = [
        'bukti_implementasi'    => 'array',
        'waktu_mulai'           => 'datetime',
        'waktu_selesai'         => 'datetime',
        'selesai_at'            => 'datetime',
        'terlambat_at'          => 'datetime',
        'terlambat_notified_at' => 'datetime',
        'jumlah_relawan'        => 'integer',
        'revisi_count'          => 'integer',
    ];

    /** Peta status mengikuti SOP Relawan Ksatria. */
    public const STATUSES = [
        'diajukan'   => ['label' => 'Menunggu Verifikasi', 'class' => 'badge-soft-secondary', 'icon' => 'bi-inbox'],
        'revisi'     => ['label' => 'Perlu Revisi',        'class' => 'badge-soft-danger',    'icon' => 'bi-arrow-counterclockwise'],
        'disetujui'  => ['label' => 'Disetujui',           'class' => 'badge-soft-info',      'icon' => 'bi-check2-circle'],
        'ditugaskan' => ['label' => 'Relawan Ditugaskan',  'class' => 'badge-soft-warning',   'icon' => 'bi-person-check'],
        'selesai'    => ['label' => 'Selesai',             'class' => 'badge-soft-success',   'icon' => 'bi-flag'],
        'ditolak'    => ['label' => 'Ditolak',             'class' => 'badge-soft-danger',    'icon' => 'bi-x-circle'],
    ];

    /** Tonggak alur (untuk timeline visual). */
    public const MILESTONES = ['diajukan' => 'Diajukan', 'disetujui' => 'Disetujui', 'ditugaskan' => 'Ditugaskan', 'selesai' => 'Selesai'];

    /**
     * Penanda "Terlambat Lapor" — bukan status tersendiri, melainkan lapisan di
     * atas status "ditugaskan": acara sudah lewat tapi laporan belum masuk.
     * Nilai filter pada querystring halaman daftar memakai key ini juga.
     */
    public const FILTER_TERLAMBAT = 'terlambat';

    public const TERLAMBAT_META = ['label' => 'Terlambat Lapor', 'class' => 'badge-soft-danger', 'icon' => 'bi-clock-history'];

    public const LAPOR_TERLAMBAT_META = ['label' => 'Lapor terlambat', 'class' => 'badge-soft-secondary', 'icon' => 'bi-clock-history'];

    /**
     * Penanda "Terlewat Verifikasi" — lapisan di atas status pra-penugasan:
     * kegiatan sudah mulai berjalan tapi relawan belum juga ditugaskan.
     */
    public const FILTER_TERLEWAT = 'terlewat';

    public const TERLEWAT_META = ['label' => 'Terlewat Verifikasi', 'class' => 'badge-soft-danger', 'icon' => 'bi-calendar-x'];

    /** Status yang belum melewati tahap verifikasi & penugasan. */
    public const STATUS_PRA_PENUGASAN = ['diajukan', 'revisi', 'disetujui'];

    /**
     * Pilihan filter turunan pada dropdown daftar pengajuan — bukan nilai kolom
     * `status`, melainkan kondisi jadwal yang dihitung saat query.
     */
    public const FILTER_TURUNAN = [
        self::FILTER_TERLAMBAT => 'Terlambat Lapor',
        self::FILTER_TERLEWAT  => 'Terlewat Verifikasi',
    ];

    public function statusMeta(): array
    {
        return self::STATUSES[$this->status] ?? ['label' => ucfirst($this->status), 'class' => 'badge-soft-secondary', 'icon' => ''];
    }

    /**
     * Penanda pendamping status (bukan pengganti) untuk pengajuan yang keluar
     * jadwal: terlambat lapor, terlewat verifikasi, atau laporan masuk telat.
     * Null bila tidak ada yang berlaku.
     */
    public function penandaMeta(): ?array
    {
        if ($this->isTerlambatLapor()) {
            return self::TERLAMBAT_META + [
                'title' => 'Kegiatan selesai ' . $this->terlambatSelama() . ' lalu, laporan belum masuk',
            ];
        }

        if ($this->isTerlewatVerifikasi()) {
            return self::TERLEWAT_META + [
                'title' => 'Kegiatan sudah mulai ' . $this->terlewatSelama() . ' lalu, relawan belum ditugaskan',
            ];
        }

        if ($this->laporanTerlambat()) {
            return self::LAPOR_TERLAMBAT_META + [
                'title' => 'Laporan masuk melewati batas waktu kegiatan',
            ];
        }

        return null;
    }

    /**
     * Status terakhir dalam satu badge — dipakai seluruh halaman: penanda
     * kondisi terkini menggantikan status bila kegiatan sedang di luar jadwal.
     * Pengecualian "Lapor terlambat" — itu catatan historis, status "Selesai"
     * tetap lebih informatif untuk ditampilkan.
     */
    public function statusAkhirMeta(): array
    {
        $penanda = $this->penandaMeta();

        if ($penanda && !$this->laporanTerlambat()) {
            return $penanda;
        }

        return $this->statusMeta() + ['title' => null];
    }

    // ---- Terlambat lapor (SOP Bagian 3: batas Evaluasi & Pelaporan) ----

    /** Toleransi (menit) setelah waktu_selesai sebelum dihitung terlambat. */
    public static function graceMenit(): int
    {
        return max(0, (int) config('pengajuan.terlambat.grace_minutes', 0));
    }

    /** Batas akhir pengiriman laporan; null bila pengajuan tanpa waktu selesai. */
    public function batasLapor(): ?Carbon
    {
        return $this->waktu_selesai?->copy()->addMinutes(self::graceMenit());
    }

    /** Acara sudah lewat batas tapi laporan belum masuk. */
    public function isTerlambatLapor(): bool
    {
        return $this->status === 'ditugaskan' && $this->batasLapor()?->isPast() === true;
    }

    /**
     * Laporan akhirnya masuk tapi melewati batas. Catatan ini permanen: dihitung
     * dari selesai_at vs batas, jadi tetap berlaku untuk pengajuan lama yang
     * selesai sebelum penanda terlambat_at ada.
     */
    public function laporanTerlambat(): bool
    {
        if ($this->status !== 'selesai') {
            return false;
        }

        $batas = $this->batasLapor();

        return $batas !== null
            && ($this->terlambat_at !== null || $this->selesai_at?->gt($batas) === true);
    }

    /** Selisih antara batas lapor dan saat laporan benar-benar masuk. */
    public function laporanTerlambatSelama(): ?string
    {
        $batas = $this->batasLapor();
        if (!$batas || !$this->selesai_at || $this->selesai_at->lte($batas)) {
            return null;
        }

        return $batas->diffForHumans($this->selesai_at, ['parts' => 2, 'syntax' => Carbon::DIFF_ABSOLUTE]);
    }

    /** Lama keterlambatan dalam bahasa manusia, mis. "3 hari 2 jam". */
    public function terlambatSelama(): ?string
    {
        $batas = $this->batasLapor();
        if (!$batas || !$batas->isPast()) {
            return null;
        }

        return $batas->diffForHumans(now(), ['parts' => 2, 'syntax' => Carbon::DIFF_ABSOLUTE]);
    }

    /** Seluruh pengajuan yang saat ini terlambat lapor. */
    public function scopeTerlambatLapor(Builder $query): Builder
    {
        return $query->where('status', 'ditugaskan')
            ->whereNotNull('waktu_selesai')
            ->where('waktu_selesai', '<=', now()->subMinutes(self::graceMenit()));
    }

    // ---- Terlewat verifikasi (kegiatan jalan tanpa relawan ditugaskan) ----

    /** Kegiatan sudah mulai tapi pengajuan belum sampai tahap "ditugaskan". */
    public function isTerlewatVerifikasi(): bool
    {
        return in_array($this->status, self::STATUS_PRA_PENUGASAN, true)
            && $this->waktu_mulai?->isPast() === true;
    }

    /** Lama sejak kegiatan dimulai tanpa penugasan, mis. "2 hari 3 jam". */
    public function terlewatSelama(): ?string
    {
        if (!$this->waktu_mulai || !$this->waktu_mulai->isPast()) {
            return null;
        }

        return $this->waktu_mulai->diffForHumans(now(), ['parts' => 2, 'syntax' => Carbon::DIFF_ABSOLUTE]);
    }

    /** Seluruh pengajuan yang kegiatannya jalan tanpa relawan ditugaskan. */
    public function scopeTerlewatVerifikasi(Builder $query): Builder
    {
        return $query->whereIn('status', self::STATUS_PRA_PENUGASAN)
            ->whereNotNull('waktu_mulai')
            ->where('waktu_mulai', '<=', now());
    }

    /**
     * Terapkan pilihan dropdown "status": nilai kolom status apa adanya, atau
     * salah satu filter turunan pada FILTER_TURUNAN.
     */
    public function scopeFilterStatus(Builder $query, ?string $status): Builder
    {
        return match ($status) {
            null, ''               => $query,
            self::FILTER_TERLAMBAT => $query->terlambatLapor(),
            self::FILTER_TERLEWAT  => $query->terlewatVerifikasi(),
            default                => $query->where('status', $status),
        };
    }

    /**
     * Kegiatan yang beririsan dengan rentang tanggal (format Y-m-d), bukan yang
     * mulainya saja — supaya kegiatan lintas hari tetap ikut terjaring. Salah
     * satu ujung boleh kosong untuk rentang terbuka.
     */
    public function scopeDalamRentang(Builder $query, ?string $dari, ?string $sampai): Builder
    {
        $dari  = $dari ? Carbon::parse($dari)->startOfDay() : null;
        $sampai = $sampai ? Carbon::parse($sampai)->endOfDay() : null;

        if ($dari) {
            // waktu_selesai null = kegiatan sehari, pakai waktu_mulai sebagai ujung.
            $query->where(fn ($q) => $q->where('waktu_selesai', '>=', $dari)
                ->orWhere(fn ($q2) => $q2->whereNull('waktu_selesai')->where('waktu_mulai', '>=', $dari)));
        }

        if ($sampai) {
            $query->where('waktu_mulai', '<=', $sampai);
        }

        return $query;
    }

    /** Posisi progres 1..4 pada timeline; revisi = mundur ke tahap Diajukan. */
    public function progressStep(): int
    {
        return match ($this->status) {
            'diajukan', 'revisi' => 1,
            'disetujui'          => 2,
            'ditugaskan'         => 3,
            'selesai'            => 4,
            default              => 0, // ditolak = keluar alur
        };
    }

    public function isOffPath(): bool
    {
        return in_array($this->status, ['ditolak', 'revisi']);
    }

    public function assignedCount(): int
    {
        return $this->kebutuhan->filter(fn ($k) => $k->isAssigned())->count();
    }

    public function allAssigned(): bool
    {
        return $this->kebutuhan->count() > 0 && $this->assignedCount() === $this->kebutuhan->count();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kebutuhan()
    {
        return $this->hasMany(KebutuhanRelawan::class, 'pengajuan_id');
    }
}

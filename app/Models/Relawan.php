<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Relawan extends Model
{
    use HasFactory;

    protected $table = 'relawan';

    protected $fillable = [
        'nama',
        'jenis',
        'jenis_kelamin',
        'kontak',
        'email',
        'domisili',
        'tahun_bergabung',
        'provinsi',
        'kota',
        'bidang_relawan_id',
        'keahlian',
        'status',
        'catatan',
    ];

    protected $casts = [
        'tahun_bergabung' => 'integer',
    ];

    /**
     * Jenis relawan. Memuat seluruh taksonomi kebutuhan (agar pencocokan
     * penugasan tetap jalan) ditambah bidang kerelawanan Ksatria yang belum
     * terwakili di form pengajuan.
     */
    public const JENIS = [
        'driver'                 => 'Driver',
        'medis'                  => 'Medis',
        'implementasi'           => 'Implementasi',
        'media_dokumentasi'      => 'Media / Dokumentasi',
        'canvassing_booth'       => 'Canvassing / Booth',
        'rescue'                 => 'Rescue Emergency',
        'psikososial_pendidikan' => 'Psikososial / Pendidikan',
        'filantropi'             => 'Filantropi (Kemitraan Jejaring)',
        'lainnya'                => 'Lainnya',
    ];

    /** Jenis kelamin relawan (individu). */
    public const JENIS_KELAMIN = [
        'L' => 'Laki-laki',
        'P' => 'Perempuan',
    ];

    /** Status ketersediaan relawan. */
    public const STATUSES = [
        'tersedia'   => ['label' => 'Tersedia',   'class' => 'badge-soft-success'],
        'ditugaskan' => ['label' => 'Ditugaskan', 'class' => 'badge-soft-warning'],
        'nonaktif'   => ['label' => 'Nonaktif',   'class' => 'badge-soft-danger'],
    ];

    public function bidang()
    {
        return $this->belongsTo(BidangRelawan::class, 'bidang_relawan_id');
    }

    public function kebutuhan()
    {
        return $this->hasMany(KebutuhanRelawan::class, 'relawan_id');
    }

    public function jenisLabel(): string
    {
        return self::JENIS[$this->jenis] ?? ucfirst((string) $this->jenis);
    }
}

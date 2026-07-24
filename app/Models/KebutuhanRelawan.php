<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KebutuhanRelawan extends Model
{
    use HasFactory;

    protected $table = 'kebutuhan_relawan';

    protected $fillable = [
        'pengajuan_id',
        'jenis_relawan',
        'jenis_kelamin',
        'detail_tugas',
        'nominal_apresiasi',
        'relawan_id',
        'relawan_nama',
        'relawan_kontak',
        'relawan_domisili',
        'assigned_at',
    ];

    protected $casts = [
        'nominal_apresiasi' => 'integer',
        'assigned_at'       => 'datetime',
    ];

    /** Jenis relawan sesuai Form Pengajuan Relawan Ksatria. */
    public const JENIS = [
        'driver'            => 'Driver',
        'medis'             => 'Medis',
        'implementasi'      => 'Implementasi',
        'media_dokumentasi' => 'Media / Dokumentasi',
        'canvassing_booth'  => 'Canvassing / Booth',
        'lainnya'           => 'Lainnya',
    ];

    /** Preferensi jenis kelamin pada kebutuhan (LP = bebas). */
    public const JENIS_KELAMIN = [
        'L'  => 'Laki-laki',
        'P'  => 'Perempuan',
        'LP' => 'Laki-laki / Perempuan',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class, 'pengajuan_id');
    }

    public function relawan()
    {
        return $this->belongsTo(Relawan::class, 'relawan_id');
    }

    public function isAssigned(): bool
    {
        return !is_null($this->relawan_id) || !empty($this->relawan_nama);
    }

    public function jenisLabel(): string
    {
        return self::JENIS[$this->jenis_relawan] ?? ucfirst($this->jenis_relawan);
    }

    public function jenisKelaminLabel(): string
    {
        return self::JENIS_KELAMIN[$this->jenis_kelamin] ?? $this->jenis_kelamin;
    }

    /** Nama relawan yang ditugaskan (dari relasi atau entri manual). */
    public function assignedName(): ?string
    {
        return $this->relawan->nama ?? $this->relawan_nama;
    }
}

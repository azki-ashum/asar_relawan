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
        'bidang_relawan_id',
        'keahlian',
        'status',
        'catatan',
    ];

    /** Jenis relawan — sama dengan taksonomi kebutuhan. */
    public const JENIS = KebutuhanRelawan::JENIS;

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

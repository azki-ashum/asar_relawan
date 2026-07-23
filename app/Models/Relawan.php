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
        'kontak',
        'email',
        'domisili',
        'bidang_relawan_id',
        'keahlian',
        'status',
        'catatan',
    ];

    /** Label & style helper untuk status relawan. */
    public const STATUSES = [
        'tersedia'   => ['label' => 'Tersedia',   'class' => 'badge-soft-success'],
        'ditugaskan' => ['label' => 'Ditugaskan', 'class' => 'badge-soft-warning'],
        'nonaktif'   => ['label' => 'Nonaktif',   'class' => 'badge-soft-danger'],
    ];

    public function bidang()
    {
        return $this->belongsTo(BidangRelawan::class, 'bidang_relawan_id');
    }

    public function pengajuan()
    {
        return $this->hasMany(Pengajuan::class, 'relawan_id');
    }
}

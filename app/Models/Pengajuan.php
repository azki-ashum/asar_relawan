<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengajuan extends Model
{
    use HasFactory;

    protected $table = 'pengajuan';

    protected $fillable = [
        'user_id',
        'relawan_id',
        'judul',
        'kebutuhan',
        'bidang_relawan_id',
        'jumlah_relawan',
        'tanggal_kegiatan',
        'lokasi',
        'status',
        'bukti_implementasi',
        'catatan_revisi',
        'revisi_count',
        'selesai_at',
    ];

    protected $casts = [
        'bukti_implementasi' => 'array',
        'tanggal_kegiatan'   => 'date',
        'selesai_at'         => 'datetime',
        'jumlah_relawan'     => 'integer',
        'revisi_count'       => 'integer',
    ];

    /** Peta status pengajuan: label + kelas badge untuk view. */
    public const STATUSES = [
        'diajukan'   => ['label' => 'Diajukan',   'class' => 'badge-soft-secondary', 'icon' => 'bi-inbox'],
        'dicari'     => ['label' => 'Dicari',     'class' => 'badge-soft-info',      'icon' => 'bi-search'],
        'ditugaskan' => ['label' => 'Ditugaskan', 'class' => 'badge-soft-warning',   'icon' => 'bi-person-check'],
        'selesai'    => ['label' => 'Selesai',    'class' => 'badge-soft-success',   'icon' => 'bi-check-circle'],
        'ditolak'    => ['label' => 'Ditolak',    'class' => 'badge-soft-danger',    'icon' => 'bi-x-circle'],
        'revisi'     => ['label' => 'Revisi',     'class' => 'badge-soft-danger',    'icon' => 'bi-arrow-counterclockwise'],
    ];

    public function statusMeta(): array
    {
        return self::STATUSES[$this->status] ?? ['label' => ucfirst($this->status), 'class' => 'badge-soft-secondary', 'icon' => ''];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function relawan()
    {
        return $this->belongsTo(Relawan::class, 'relawan_id');
    }

    public function bidang()
    {
        return $this->belongsTo(BidangRelawan::class, 'bidang_relawan_id');
    }
}

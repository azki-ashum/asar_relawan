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
    ];

    protected $casts = [
        'bukti_implementasi' => 'array',
        'waktu_mulai'        => 'datetime',
        'waktu_selesai'      => 'datetime',
        'selesai_at'         => 'datetime',
        'jumlah_relawan'     => 'integer',
        'revisi_count'       => 'integer',
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

    public function statusMeta(): array
    {
        return self::STATUSES[$this->status] ?? ['label' => ucfirst($this->status), 'class' => 'badge-soft-secondary', 'icon' => ''];
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

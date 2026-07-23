<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BidangRelawan extends Model
{
    use HasFactory;

    protected $table = 'bidang_relawan';

    protected $fillable = [
        'nama',
        'deskripsi',
    ];

    public function relawan()
    {
        return $this->hasMany(Relawan::class, 'bidang_relawan_id');
    }

    public function pengajuan()
    {
        return $this->hasMany(Pengajuan::class, 'bidang_relawan_id');
    }
}

<?php

namespace Database\Seeders;

use App\Models\BidangRelawan;
use Illuminate\Database\Seeder;

class BidangRelawanSeeder extends Seeder
{
    /** Bidang kerelawanan resmi pada form pendaftaran Relawan Ksatria. */
    public const BIDANG = [
        'Relief (Aksi Implementasi Program Kemanusiaan)' => 'implementasi',
        'Media & Komunikasi'                            => 'media_dokumentasi',
        'Rescue Emergency'                              => 'rescue',
        'Medis'                                         => 'medis',
        'Psikososial / Pendidikan'                      => 'psikososial_pendidikan',
        'Filantropi (Kemitraan Jejaring)'               => 'filantropi',
        'Peminatan Umum'                                => 'lainnya',
    ];

    public function run(): void
    {
        foreach (array_keys(self::BIDANG) as $nama) {
            BidangRelawan::firstOrCreate(['nama' => $nama]);
        }
    }
}

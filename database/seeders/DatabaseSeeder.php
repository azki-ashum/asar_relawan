<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\BidangRelawan;
use App\Models\Relawan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed database untuk Sistem Pengajuan Relawan Ksatria.
     */
    public function run(): void
    {
        // --- Akun demo ---
        User::updateOrCreate(
            ['email' => 'admin@asarhumanity.org'],
            ['name' => 'Admin ASAR', 'role' => 'admin', 'password' => Hash::make('password')]
        );
        User::updateOrCreate(
            ['email' => 'pengaju@asarhumanity.org'],
            ['name' => 'Pengaju Demo', 'role' => 'user', 'password' => Hash::make('password')]
        );

        // --- Bidang relawan (pengelompokan opsional) ---
        foreach ([
            'Medis'       => 'Tenaga kesehatan: dokter, perawat, P3K.',
            'Logistik'    => 'Distribusi bantuan, gudang, angkutan.',
            'Dokumentasi' => 'Foto, video, dan peliputan kegiatan.',
        ] as $nama => $deskripsi) {
            BidangRelawan::updateOrCreate(['nama' => $nama], ['deskripsi' => $deskripsi]);
        }

        // --- Contoh data relawan (jenis mengikuti Form Ksatria) ---
        $relawan = [
            ['nama' => 'Budi Santoso',     'jenis' => 'medis',             'jk' => 'L', 'domisili' => 'Jakarta Timur', 'kontak' => '081200000001', 'keahlian' => 'Perawat, P3K, tanggap darurat'],
            ['nama' => 'Siti Nurhaliza',   'jenis' => 'medis',             'jk' => 'P', 'domisili' => 'Bekasi',        'kontak' => '081200000002', 'keahlian' => 'Dokter umum'],
            ['nama' => 'Andi Wijaya',      'jenis' => 'driver',            'jk' => 'L', 'domisili' => 'Depok',         'kontak' => '081200000003', 'keahlian' => 'SIM A/B, mobil operasional'],
            ['nama' => 'Eko Purnomo',      'jenis' => 'driver',            'jk' => 'L', 'domisili' => 'Bogor',         'kontak' => '081200000004', 'keahlian' => 'SIM A, ambulans'],
            ['nama' => 'Rina Melati',      'jenis' => 'media_dokumentasi', 'jk' => 'P', 'domisili' => 'Jakarta Pusat', 'kontak' => '081200000005', 'keahlian' => 'Fotografi, videografi'],
            ['nama' => 'Hana Salsabila',   'jenis' => 'media_dokumentasi', 'jk' => 'P', 'domisili' => 'Tangerang',     'kontak' => '081200000006', 'keahlian' => 'Editing, live report'],
            ['nama' => 'Joko Prasetyo',    'jenis' => 'implementasi',      'jk' => 'L', 'domisili' => 'Tangerang',     'kontak' => '081200000007', 'keahlian' => 'Koordinator lapangan'],
            ['nama' => 'Gilang Ramadhan',  'jenis' => 'implementasi',      'jk' => 'L', 'domisili' => 'Jakarta Barat', 'kontak' => '081200000008', 'keahlian' => 'Distribusi, setup lokasi'],
            ['nama' => 'Fitri Handayani',  'jenis' => 'canvassing_booth',  'jk' => 'P', 'domisili' => 'Jakarta Selatan','kontak' => '081200000009', 'keahlian' => 'Public speaking, booth'],
            ['nama' => 'Dewi Lestari',     'jenis' => 'lainnya',           'jk' => 'P', 'domisili' => 'Bogor',         'kontak' => '081200000010', 'keahlian' => 'Konsumsi, dapur umum'],
        ];
        foreach ($relawan as $r) {
            Relawan::updateOrCreate(
                ['nama' => $r['nama']],
                [
                    'jenis'         => $r['jenis'],
                    'jenis_kelamin' => $r['jk'],
                    'domisili'      => $r['domisili'],
                    'kontak'        => $r['kontak'],
                    'keahlian'      => $r['keahlian'],
                    'status'        => 'tersedia',
                ]
            );
        }
    }
}

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
     * Seed the application's database untuk Sistem Pengajuan Relawan.
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

        // --- Bidang relawan ---
        $bidangs = [
            'Medis'       => 'Tenaga kesehatan: dokter, perawat, P3K.',
            'Logistik'    => 'Distribusi bantuan, gudang, angkutan.',
            'Dokumentasi' => 'Foto, video, dan peliputan kegiatan.',
            'Psikososial' => 'Pendampingan psikologis dan trauma healing.',
            'Dapur Umum'  => 'Penyediaan konsumsi untuk kegiatan.',
        ];
        $bidangIds = [];
        foreach ($bidangs as $nama => $deskripsi) {
            $b = BidangRelawan::updateOrCreate(['nama' => $nama], ['deskripsi' => $deskripsi]);
            $bidangIds[$nama] = $b->id;
        }

        // --- Contoh data relawan ---
        $relawan = [
            ['nama' => 'Budi Santoso',   'bidang' => 'Medis',       'domisili' => 'Jakarta Timur', 'kontak' => '081200000001', 'keahlian' => 'Perawat, P3K, tanggap darurat'],
            ['nama' => 'Siti Nurhaliza', 'bidang' => 'Medis',       'domisili' => 'Bekasi',        'kontak' => '081200000002', 'keahlian' => 'Dokter umum'],
            ['nama' => 'Andi Wijaya',    'bidang' => 'Logistik',    'domisili' => 'Depok',         'kontak' => '081200000003', 'keahlian' => 'Manajemen gudang, sopir'],
            ['nama' => 'Rina Melati',    'bidang' => 'Dokumentasi', 'domisili' => 'Jakarta Pusat', 'kontak' => '081200000004', 'keahlian' => 'Fotografi, videografi'],
            ['nama' => 'Joko Prasetyo',  'bidang' => 'Psikososial', 'domisili' => 'Tangerang',     'kontak' => '081200000005', 'keahlian' => 'Konselor, trauma healing'],
            ['nama' => 'Dewi Lestari',   'bidang' => 'Dapur Umum',  'domisili' => 'Bogor',         'kontak' => '081200000006', 'keahlian' => 'Juru masak, koordinasi konsumsi'],
        ];
        foreach ($relawan as $r) {
            Relawan::updateOrCreate(
                ['nama' => $r['nama']],
                [
                    'bidang_relawan_id' => $bidangIds[$r['bidang']] ?? null,
                    'domisili'          => $r['domisili'],
                    'kontak'            => $r['kontak'],
                    'email'             => null,
                    'keahlian'          => $r['keahlian'],
                    'status'            => 'tersedia',
                ]
            );
        }
    }
}

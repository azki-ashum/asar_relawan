<?php

namespace App\Console\Commands;

use App\Models\BidangRelawan;
use App\Models\Relawan;
use Database\Seeders\BidangRelawanSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportRelawanKsatria extends Command
{
    protected $signature = 'relawan:import
                            {file : Path berkas .xlsx database relawan}
                            {--sheet=DATABASE SEMENTARA BUAT SYSTEM : Nama sheet yang dibaca}
                            {--dry-run : Hanya menampilkan ringkasan, tanpa menulis ke database}';

    protected $description = 'Impor data relawan dari berkas XLSX Database Relawan Ksatria';

    /** Kolom yang diharapkan, berurutan dari kolom A. */
    private const COL_TAHUN    = 0;
    private const COL_NAMA     = 1;
    private const COL_JK       = 2;
    private const COL_PROVINSI = 3;
    private const COL_KOTA     = 4;
    private const COL_BIDANG   = 5;
    private const COL_KONTAK   = 6;

    /** Awalan nilai bidang resmi => [nama bidang master, jenis]. */
    private const BIDANG_MAP = [
        'relief (aksi implementasi' => ['Relief (Aksi Implementasi Program Kemanusiaan)', 'implementasi'],
        'media & komunikasi'        => ['Media & Komunikasi', 'media_dokumentasi'],
        'rescue emergency'          => ['Rescue Emergency', 'rescue'],
        'medis ('                   => ['Medis', 'medis'],
        'psikososial / pendidikan'  => ['Psikososial / Pendidikan', 'psikososial_pendidikan'],
        'filantropi (kemitraan'     => ['Filantropi (Kemitraan Jejaring)', 'filantropi'],
        'peminatan umum'            => ['Peminatan Umum', 'lainnya'],
    ];

    public function handle(): int
    {
        $path = $this->argument('file');
        if (!is_file($path)) {
            $this->error("Berkas tidak ditemukan: {$path}");
            return self::FAILURE;
        }

        $rows = $this->readSheet($path, (string) $this->option('sheet'));
        if ($rows === null) {
            return self::FAILURE;
        }

        $header = array_shift($rows);
        $this->line('Header  : ' . implode(' | ', $header));
        $this->line('Baris   : ' . count($rows));
        $this->newLine();

        $records = [];
        $skipped = [];

        foreach ($rows as $i => $row) {
            $lineNo = $i + 2; // +1 header, +1 basis 1

            $nama = $this->cleanName($row[self::COL_NAMA] ?? '');
            if ($nama === '') {
                $skipped[] = "baris {$lineNo}: nama kosong";
                continue;
            }

            $kontak   = $this->normalizePhone($row[self::COL_KONTAK] ?? '');
            $bidangRaw = trim($row[self::COL_BIDANG] ?? '');
            [$bidangNama, $jenis] = $this->mapBidang($bidangRaw);

            $provinsi = $this->titleCase($row[self::COL_PROVINSI] ?? '');
            $kota     = $this->titleCase($row[self::COL_KOTA] ?? '');
            $domisili = trim(implode(', ', array_filter([$kota, $provinsi])));

            $data = [
                'nama'            => $nama,
                'jenis'           => $jenis,
                'jenis_kelamin'   => $this->normalizeGender($row[self::COL_JK] ?? ''),
                'kontak'          => $kontak,
                'domisili'        => $domisili ?: null,
                'tahun_bergabung' => $this->normalizeYear($row[self::COL_TAHUN] ?? ''),
                'provinsi'        => $provinsi ?: null,
                'kota'            => $kota ?: null,
                'bidang_nama'     => $bidangNama,
                // Bidang di luar daftar resmi disimpan apa adanya agar tidak hilang.
                'keahlian'        => $bidangNama === null && $bidangRaw !== '' ? $bidangRaw : null,
            ];

            $key = $kontak !== null ? 'tel:' . $kontak : 'nama:' . Str::slug($nama);

            $records[$key] = isset($records[$key])
                ? $this->mergeRecord($records[$key], $data)
                : $data;
        }

        $this->info('Ringkasan pemrosesan');
        $this->table(
            ['Metrik', 'Jumlah'],
            [
                ['Baris terbaca', count($rows)],
                ['Baris dilewati', count($skipped)],
                ['Record unik', count($records)],
                ['Tanpa kontak valid', count(array_filter($records, fn($r) => $r['kontak'] === null))],
                ['Bidang di luar daftar resmi', count(array_filter($records, fn($r) => $r['bidang_nama'] === null))],
            ]
        );

        $byJenis = array_count_values(array_column($records, 'jenis'));
        arsort($byJenis);
        $this->table(
            ['Jenis', 'Jumlah'],
            array_map(fn($k, $v) => [Relawan::JENIS[$k] ?? $k, $v], array_keys($byJenis), $byJenis)
        );

        foreach (array_slice($skipped, 0, 20) as $s) {
            $this->warn('  dilewati - ' . $s);
        }

        if ($this->option('dry-run')) {
            $this->newLine();
            $this->comment('Dry run: tidak ada data yang ditulis.');
            return self::SUCCESS;
        }

        $bidangIds = $this->ensureBidang();

        $created = 0;
        $updated = 0;

        foreach ($records as $data) {
            $bidangId = $data['bidang_nama'] !== null ? ($bidangIds[$data['bidang_nama']] ?? null) : null;

            $query = $data['kontak'] !== null
                ? Relawan::where('kontak', $data['kontak'])
                : Relawan::whereNull('kontak')->where('nama', $data['nama']);

            $relawan = $query->first();

            $attributes = [
                'nama'              => $data['nama'],
                'jenis'             => $data['jenis'],
                'jenis_kelamin'     => $data['jenis_kelamin'],
                'kontak'            => $data['kontak'],
                'domisili'          => $data['domisili'],
                'tahun_bergabung'   => $data['tahun_bergabung'],
                'provinsi'          => $data['provinsi'],
                'kota'              => $data['kota'],
                'bidang_relawan_id' => $bidangId,
                'keahlian'          => $data['keahlian'],
            ];

            if ($relawan) {
                $relawan->fill($attributes)->save();
                $updated++;
            } else {
                Relawan::create($attributes + ['status' => 'tersedia']);
                $created++;
            }
        }

        $this->newLine();
        $this->info("Selesai. Dibuat: {$created}, diperbarui: {$updated}.");

        return self::SUCCESS;
    }

    /** Pastikan master bidang tersedia, kembalikan peta nama => id. */
    private function ensureBidang(): array
    {
        foreach (array_keys(BidangRelawanSeeder::BIDANG) as $nama) {
            BidangRelawan::firstOrCreate(['nama' => $nama]);
        }

        return BidangRelawan::pluck('id', 'nama')->all();
    }

    /** Gabungkan dua baris duplikat: pertahankan tahun terawal & nilai terisi. */
    private function mergeRecord(array $a, array $b): array
    {
        $merged = $a;

        foreach ($b as $key => $value) {
            if ($key === 'tahun_bergabung') {
                $merged[$key] = min(array_filter([$a[$key], $value])) ?: null;
                continue;
            }
            if (($merged[$key] ?? null) === null || $merged[$key] === '') {
                $merged[$key] = $value;
            }
        }

        // Bidang resmi lebih diutamakan daripada teks bebas.
        if ($a['bidang_nama'] === null && $b['bidang_nama'] !== null) {
            $merged['bidang_nama'] = $b['bidang_nama'];
            $merged['jenis']       = $b['jenis'];
        }

        return $merged;
    }

    private function cleanName(string $value): string
    {
        $value = trim(preg_replace('/\s+/u', ' ', $value));
        if ($value === '') {
            return '';
        }

        // Kapitalisasi awal kata, apostrof tetap huruf kecil (mis. Ma'ruf).
        return preg_replace_callback(
            '/(^|[\s\-\.\(])(\p{L})/u',
            fn($m) => $m[1] . mb_strtoupper($m[2], 'UTF-8'),
            mb_strtolower($value, 'UTF-8')
        );
    }

    private function normalizeGender(string $value): ?string
    {
        $v = mb_strtolower(trim($value));

        return match (true) {
            str_starts_with($v, 'laki')     => 'L',
            str_starts_with($v, 'perempuan') => 'P',
            default                          => null,
        };
    }

    /** Ubah 08xx / 62xx / 8xx menjadi format 08xxxxxxxxx; null bila tidak valid. */
    private function normalizePhone(string $value): ?string
    {
        $digits = preg_replace('/\D+/', '', $value);
        if ($digits === '') {
            return null;
        }
        if (str_starts_with($digits, '62')) {
            $digits = '0' . substr($digits, 2);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '0' . $digits;
        }

        return preg_match('/^08\d{7,12}$/', $digits) ? $digits : null;
    }

    /** Nilai tersimpan sebagai float teks, mis. "2023.0". */
    private function normalizeYear(string $value): ?int
    {
        $year = (int) round((float) $value);

        return ($year >= 2000 && $year <= 2100) ? $year : null;
    }

    private function titleCase(string $value): string
    {
        $value = trim(preg_replace('/\s+/u', ' ', $value));
        if ($value === '') {
            return '';
        }
        $value = Str::title(mb_strtolower($value));

        return str_replace(['Dki', 'Diy'], ['DKI', 'DIY'], $value);
    }

    /** @return array{0: ?string, 1: string} [nama bidang resmi|null, jenis] */
    private function mapBidang(string $value): array
    {
        $v = mb_strtolower(trim($value));

        foreach (self::BIDANG_MAP as $prefix => [$nama, $jenis]) {
            if (str_starts_with($v, $prefix)) {
                return [$nama, $jenis];
            }
        }

        if ($v === 'pendidikan') {
            return ['Psikososial / Pendidikan', 'psikososial_pendidikan'];
        }

        return [null, 'lainnya'];
    }

    /**
     * Baca sheet XLSX tanpa pustaka eksternal (ZipArchive + XMLReader).
     *
     * @return array<int, array<int, string>>|null
     */
    private function readSheet(string $path, string $sheetName): ?array
    {
        $zip = new \ZipArchive;
        if ($zip->open($path) !== true) {
            $this->error('Gagal membuka berkas XLSX.');
            return null;
        }

        $workbook = simplexml_load_string($zip->getFromName('xl/workbook.xml'));
        $rels     = simplexml_load_string($zip->getFromName('xl/_rels/workbook.xml.rels'));
        $relNs    = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

        $target = null;
        foreach ($workbook->sheets->sheet as $sheet) {
            if ((string) $sheet->attributes()['name'] !== $sheetName) {
                continue;
            }
            $rid = (string) $sheet->attributes($relNs)['id'];
            foreach ($rels->Relationship as $rel) {
                if ((string) $rel['Id'] === $rid) {
                    $target = 'xl/' . ltrim((string) $rel['Target'], '/');
                }
            }
        }

        if ($target === null) {
            $names = [];
            foreach ($workbook->sheets->sheet as $sheet) {
                $names[] = (string) $sheet->attributes()['name'];
            }
            $this->error("Sheet \"{$sheetName}\" tidak ditemukan. Tersedia: " . implode(', ', $names));
            return null;
        }

        // Shared strings
        $strings = [];
        $reader  = new \XMLReader;
        $reader->XML((string) $zip->getFromName('xl/sharedStrings.xml'));
        while ($reader->read()) {
            if ($reader->nodeType === \XMLReader::ELEMENT && $reader->name === 'si') {
                $node = simplexml_load_string($reader->readOuterXml());
                $text = '';
                foreach ($node->xpath('//*[local-name()="t"]') as $t) {
                    $text .= (string) $t;
                }
                $strings[] = $text;
            }
        }
        $reader->close();

        $rows   = [];
        $reader = new \XMLReader;
        $reader->XML((string) $zip->getFromName($target));
        while ($reader->read()) {
            if ($reader->nodeType !== \XMLReader::ELEMENT || $reader->name !== 'row') {
                continue;
            }
            $node = simplexml_load_string($reader->readOuterXml());
            $row  = array_fill(0, 7, '');
            foreach ($node->c as $cell) {
                $type  = (string) $cell['t'];
                $value = isset($cell->v) ? (string) $cell->v : '';
                if ($type === 's') {
                    $value = $strings[(int) $value] ?? '';
                } elseif ($type === 'inlineStr') {
                    $value = (string) $cell->is->t;
                }
                $index = $this->columnIndex((string) $cell['r']);
                if ($index < 7) {
                    $row[$index] = trim($value);
                }
            }
            if (implode('', $row) !== '') {
                $rows[] = $row;
            }
        }
        $reader->close();
        $zip->close();

        return $rows;
    }

    private function columnIndex(string $ref): int
    {
        preg_match('/^([A-Z]+)/', $ref, $m);
        $index = 0;
        foreach (str_split($m[1] ?? 'A') as $char) {
            $index = $index * 26 + (ord($char) - 64);
        }

        return $index - 1;
    }
}

<?php

namespace App\Support;

use App\Models\Pengajuan;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Sumber tunggal data kalender pengajuan: dipakai Beranda Pengaju maupun
 * halaman Admin - Pengajuan supaya tanda titik & isi modal per tanggal sama.
 */
class JadwalPengajuan
{
    /** Seluruh pengajuan berjadwal (semua pengaju) dalam bentuk array siap-JSON. */
    public static function items(): Collection
    {
        return Pengajuan::with(['user', 'kebutuhan.relawan'])->withCount('kebutuhan')
            ->whereNotIn('status', ['ditolak'])
            ->whereNotNull('waktu_mulai')
            ->orderBy('waktu_mulai')
            ->get()
            ->map(function (Pengajuan $p) {
                // Satu badge saja: penanda di luar jadwal sudah menggantikan status.
                $meta = $p->statusAkhirMeta();

                return [
                    'id'             => $p->id,
                    'judul'          => $p->judul,
                    'pengaju'        => $p->nama_pic ?? ($p->user->name ?? null),
                    'divisi'         => $p->divisi,
                    'direktorat'     => $p->direktorat,
                    'lokasi'         => $p->lokasi,
                    'kebutuhan'      => $p->kebutuhan_count,
                    'relawan_names'  => $p->kebutuhan->filter->isAssigned()->map->assignedName()->filter()->values()->all(),
                    'waktu_mulai'    => $p->waktu_mulai->toIso8601String(),
                    'waktu_selesai'  => optional($p->waktu_selesai)->toIso8601String(),
                    'status'         => $p->status,
                    'status_label'   => $meta['label'],
                    'status_class'   => $meta['class'],
                    'status_icon'    => $meta['icon'],
                    'status_title'   => $meta['title'],
                    'url'            => route('pengajuan.show', $p),
                ];
            });
    }

    /**
     * Tanggal yang ditandai titik di kalender: setiap hari yang dilingkupi
     * rentang waktu_mulai..waktu_selesai suatu pengajuan.
     */
    public static function tanggalBertanda(Collection $items): array
    {
        $tanggal = [];

        foreach ($items as $item) {
            $day = Carbon::parse($item['waktu_mulai'])->startOfDay();
            $end = $item['waktu_selesai'] ? Carbon::parse($item['waktu_selesai'])->startOfDay() : $day->copy();
            while ($day->lte($end)) {
                $tanggal[$day->format('Y-m-d')] = true;
                $day->addDay();
            }
        }

        return $tanggal;
    }
}

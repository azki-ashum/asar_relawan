<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Terlambat Lapor
    |--------------------------------------------------------------------------
    |
    | Pengajuan dianggap terlambat lapor bila waktu_selesai sudah terlewat
    | tetapi bukti & laporan belum dikirim (status masih "ditugaskan").
    |
    | grace_minutes         : toleransi setelah waktu_selesai sebelum ditandai
    |                         terlambat. 0 = tepat saat waktu selesai lewat.
    | reminder_every_hours  : kirim ulang email pengingat tiap sekian jam
    |                         selama laporan belum masuk. 0 = kirim sekali saja.
    |
    */

    'terlambat' => [
        'grace_minutes'        => (int) env('PENGAJUAN_TERLAMBAT_GRACE_MINUTES', 0),
        'reminder_every_hours' => (int) env('PENGAJUAN_TERLAMBAT_REMINDER_EVERY_HOURS', 0),
    ],

];

<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| Di file ini kamu bisa daftarkan closure-based artisan commands
| atau schedule untuk command yang sudah ada.
|
*/

// contoh bawaan
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// tandai pengajuan relawan yang lewat waktu selesai tapi laporannya belum masuk
Schedule::command('pengajuan:mark-terlambat')->everyFiveMinutes()->withoutOverlapping()->after(function () {
    Log::info('Scheduler: pengajuan:mark-terlambat dijalankan pada ' . now());
});

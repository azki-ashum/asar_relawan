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

Schedule::command('bookings:mark-overdue')->everyMinute()->after(function () {
    Log::info('Scheduler: bookings:mark-overdue dijalankan pada ' . now());
});

Schedule::command('bookings:mark-started')->everyMinute()->after(function () {
    Log::info('Scheduler: bookings:mark-started dijalankan pada ' . now());
});

// jalanin command booking:expire tiap menit (untuk testing)
Schedule::command('booking:expire')
    ->everyMinute()
    ->after(function () {
        Log::info('Scheduler: booking:expire dijalankan pada ' . now());
    });

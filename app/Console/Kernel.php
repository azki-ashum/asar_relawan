<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // run every minute for testing; change back to everyFiveMinutes() for production
        $schedule->command('booking:expire')->everyMinute();

        // run the console wrapper for mark-overdue every minute as well
        $schedule->command('run:mark-overdue')->everyMinute()->after(function () {
            \Log::info('Scheduler: run:mark-overdue dijalankan pada ' . now());
        });

        // mark asset bookings as in_use when their start_at arrives
        $schedule->command('bookings:mark-started')->everyMinute()->after(function () {
            \Log::info('Scheduler: bookings:mark-started dijalankan pada ' . now());
        });

        // debug helper (remove after testing)
        $schedule->call(function () {
            \Log::info('schedule boot check at '.now());
        })->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        parent::commands();
    }
}

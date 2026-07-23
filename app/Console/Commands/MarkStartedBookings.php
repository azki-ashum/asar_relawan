<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use Carbon\Carbon;

class MarkStartedBookings extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bookings:mark-started';

    /**
     * The console command description.
     */
    protected $description = 'Mark asset bookings as in_use when start_at has arrived';

    public function handle()
    {
        $now = Carbon::now();

        $query = Booking::where('type', 'asset')
            ->whereIn('status', ['pending','approved'])
            ->whereNull('returned_at')
            ->where('start_at', '<=', $now);

        $count = $query->count();
        $this->info("Found {$count} candidate(s) to mark in_use");

        $nowStr = $now->toDateTimeString();

        $query->chunkById(200, function($rows) use ($nowStr) {
            foreach ($rows as $b) {
                try {
                    $b->status = 'in_use';
                    $b->checked_out_at = $nowStr;
                    $b->saveQuietly();
                } catch (\Throwable $e) {
                    \Log::warning('Failed to mark booking in_use', ['booking_id' => $b->id, 'error' => $e->getMessage()]);
                }
            }
        });

        $this->info("Marked in_use for {$count} booking(s)");
        return 0;
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use Carbon\Carbon;

class MarkOverdueBookings extends Command
{
    /**
     * The name and signature of the console command.
     * --grace minutes allow small tolerance after end_at before marking overdue
     */
    protected $signature = 'bookings:mark-overdue';

    /**
     * The console command description.
     */
    protected $description = 'Mark bookings overdue when end_at passed and not returned/finished';

    public function handle()
    {
    $now = Carbon::now();
    // No grace tolerance: threshold is now
    $threshold = $now;

        $query = Booking::whereIn('status', ['pending', 'approved', 'in_use'])
            ->whereNull('returned_at')
            ->where('end_at', '<', $threshold)
            ->where(function($q) {
                $q->where('is_overdue', '!=', true)->orWhereNull('is_overdue');
            });

        $count = $query->count();

        $this->info("Found {$count} candidate(s) to mark overdue");

        $nowStr = $now->toDateTimeString();

        $query->chunkById(200, function($rows) use ($nowStr) {
            foreach ($rows as $b) {
                try {
                    $b->is_overdue = true;
                    $b->overdue_at = $nowStr;
                    $b->saveQuietly();
                    // optional: dispatch notification job here
                } catch (\Throwable $e) {
                    \Log::warning('Failed to mark booking overdue', ['booking_id' => $b->id, 'error' => $e->getMessage()]);
                }
            }
        });

        $this->info("Marked overdue for {$count} booking(s)");
        return 0;
    }
}

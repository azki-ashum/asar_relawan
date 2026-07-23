<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ExpireBookings extends Command
{
    protected $signature = 'booking:expire';
    protected $description = 'Mark bookings as done/cancelled when end_at has passed';

    public function handle()
    {
        $now = Carbon::now();

        // 1) Bookings that were approved and already finished -> mark as done
        // only process room bookings for auto-done
        $toDone = Booking::where('type', 'room')
            ->where('status', 'approved')
            ->where('end_at', '<', $now)
            ->get();

        foreach ($toDone as $b) {
            $b->update(['status' => 'done']);

            // Reactivate room if there's no other ongoing approved/pending booking for the same room
            try {
                $room = $b->room;
                if ($room) {
                    $ongoing = Booking::where('room_id', $room->id)
                        ->where('id', '!=', $b->id)
                        ->whereIn('status', ['pending', 'approved'])
                        ->where('start_at', '<=', $now)
                        ->where('end_at', '>=', $now)
                        ->exists();

                    if (! $ongoing) {
                        // Recompute and persist active state from bookings instead of direct toggles
                        $room->refreshActiveStatus();
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to reactivate room after marking booking done', ['booking_id' => $b->id, 'error' => $e->getMessage()]);
            }

            Log::info('Booking auto-marked done by scheduler', ['booking_id' => $b->id]);
        }

        // 2) Bookings that were still pending but already passed -> cancel them
        // only process room bookings for auto-cancel
        $toCancel = Booking::where('type', 'room')
            ->where('status', 'pending')
            ->where('end_at', '<', $now)
            ->get();

        foreach ($toCancel as $b) {
            $b->update(['status' => 'cancelled']);
            // refresh room active status after change
            try {
                $room = $b->room;
                if ($room) {
                    $room->refreshActiveStatus();
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to refresh room active status after auto-cancel', ['booking_id' => $b->id, 'error' => $e->getMessage()]);
            }

            Log::info('Booking auto-cancelled by scheduler (pending expired)', ['booking_id' => $b->id]);
        }

        $count = $toDone->count() + $toCancel->count();
        $this->info('Expired bookings processed: ' . $count);
        return 0;
    }
}

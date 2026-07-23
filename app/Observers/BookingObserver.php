<?php

namespace App\Observers;

use App\Models\Booking;

class BookingObserver
{
    public function created(Booking $booking): void
    {
        $this->syncRoomActive($booking);
    }

    public function updated(Booking $booking): void
    {
        $this->syncRoomActive($booking);
        $this->maybeComputeEndDiff($booking);
        // Try to set asset booking to in_use when user extends end_at on an overdue booking
        $this->maybeMarkInUseOnExtend($booking);

        $this->maybeClearOverdue($booking);
    }

    /**
     * If an asset booking was previously marked overdue and the user updates the booking
     * extending the end_at beyond the original end_at, mark it as in_use and clear overdue flags.
     */
    protected function maybeMarkInUseOnExtend(Booking $booking): void
    {
        try {
            if ($booking->type !== 'asset') {
                return;
            }

            $originalOverdue = $booking->getOriginal('is_overdue');
            if (! $originalOverdue) {
                return;
            }

            $origEnd = $booking->getOriginal('end_at');
            $newEnd = $booking->end_at;
            if (! $origEnd || ! $newEnd) {
                return;
            }

            $origEndDt = \Carbon\Carbon::parse($origEnd);
            $newEndDt = $newEnd instanceof \Carbon\Carbon ? $newEnd : \Carbon\Carbon::parse($newEnd);

            if ($newEndDt->gt($origEndDt)) {
                // user extended the booking; set to in_use and clear overdue
                $booking->status = 'in_use';
                $booking->is_overdue = false;
                $booking->overdue_at = null;
                if (! $booking->checked_out_at) {
                    $booking->checked_out_at = now();
                }
                $booking->saveQuietly();
                \Log::info('Marked overdue asset booking in_use after end_at extension', ['booking_id' => $booking->id]);
            }
        } catch (\Throwable $e) {
            \Log::warning('Failed maybeMarkInUseOnExtend', ['booking_id' => $booking->id, 'error' => $e->getMessage()]);
        }
    }

    protected function syncRoomActive(Booking $booking): void
    {
        if (! $booking->room) {
            return;
        }

        // Sesuaikan status sesuai aplikasi Anda
        $inactiveStatuses = ['approved', 'Sedang Digunakan', 'booking', 'in_use'];
        $activeStatuses   = ['cancelled', 'done', 'completed', 'available'];

        $room = $booking->room;

        // Recompute active state from current bookings to avoid race/edge cases
        try {
            $room->refreshActiveStatus();
        } catch (\Throwable $e) {
            // swallow to avoid breaking booking flow
            \Log::warning('Failed to refresh room active status', ['room_id' => $room->id, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Compute and persist the difference in minutes between actual finish time and scheduled end time.
     * This will run when booking is updated; it only persists if status is 'done' or a finished timestamp exists.
     */
    protected function maybeComputeEndDiff(Booking $booking): void
    {
        // only compute when we have an end_at and booking is finished
        $doneStatuses = ['done', 'completed'];

        if (! $booking->end_at) {
            return;
        }

        if (! in_array($booking->status, $doneStatuses, true)) {
            return;
        }

        // Use now as finished time if no explicit finished_at exists. If you have a finished_at column, prefer that.
        $finishedAt = $booking->finished_at ?? now();

        try {
            $diff = $finishedAt->diffInMinutes($booking->end_at, false);
            // diffInMinutes with false returns negative when finished before end_at; keep sign to indicate early/late
            if ($booking->end_diff_minutes !== $diff) {
                $booking->end_diff_minutes = $diff;
                // avoid infinite loop by saving quietly
                $booking->saveQuietly();
            }
        } catch (\Throwable $e) {
            \Log::warning('Failed to compute end_diff_minutes', ['booking_id' => $booking->id, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Clear overdue flag when booking finished/cancelled/returned.
     */
    protected function maybeClearOverdue(Booking $booking): void
    {
        try {
            if (in_array($booking->status, ['done', 'cancelled', 'completed'], true) || $booking->returned_at) {
                if ($booking->is_overdue) {
                    $booking->is_overdue = false;
                    $booking->overdue_at = null;
                    $booking->saveQuietly();
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('Failed to clear overdue flag', ['booking_id' => $booking->id, 'error' => $e->getMessage()]);
        }
    }
}

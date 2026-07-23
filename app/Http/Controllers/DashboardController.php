<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Booking;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $roomsCount = Room::where('is_active', 1)->count();

        // bookings today (exclude cancelled & done)
        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();

        // Count only bookings that start today
        $bookingsToday = Booking::where('type', 'room')->whereNotIn('status', ['cancelled'])
            ->whereBetween('start_at', [$todayStart, $todayEnd])
            ->count();

        // next booking time
        $nextBooking = Booking::where('type', 'room')->whereNotIn('status', ['cancelled', 'done'])
            ->where('start_at', '>', Carbon::now())
            ->orderBy('start_at', 'asc')
            ->first();

        $nextBookingTime = '-';
        if ($nextBooking) {
            try {
                $nextBookingTime = $nextBooking->start_at->locale('id')->isoFormat('dddd, D MMM YYYY HH:mm');
            } catch (\Throwable $e) {
                $nextBookingTime = $nextBooking->start_at->format('Y-m-d H:i');
            }
        }

        // week bookings (7 days starting today)
        $start = Carbon::today()->startOfDay();
        $end = (clone $start)->addDays(6)->endOfDay();

        // Include bookings that overlap with the 7-day window (start_at <= end AND end_at >= start).
        $weekBookings = Booking::with('room', 'user')
            ->where('type', 'room')
            ->whereNotIn('status', ['cancelled', 'done'])
            ->where('start_at', '<=', $end)
            ->where('end_at', '>=', $start)
            ->orderBy('start_at')
            ->get();

        // get all bookings from all time (for calendar display)
        $allTimeBookings = Booking::with('room', 'user')
            ->where('type', 'room')
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('start_at', 'desc')
            ->get();

        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $day = (clone $start)->addDays($i);
            $dayStart = $day->copy()->startOfDay();
            $dayEnd = $day->copy()->endOfDay();

            // Include bookings that overlap with this day (not just those starting on this day)
            $bookingsForDay = $weekBookings->filter(function ($b) use ($dayStart, $dayEnd) {
                return $b->start_at <= $dayEnd && $b->end_at >= $dayStart;
            })->map(function ($b) {
                return [
                    'id' => $b->id,
                    'room_name' => $b->room->name ?? '-',
                    'title' => $b->title ?? '-',
                    'division' => $b->division ?? null,
                    'directorate' => $b->directorate ?? null,
                    'partner' => $b->partner ?? null,
                    'participants_internal' => $b->participants_internal ?? null,
                    'participants_external' => $b->participants_external ?? null,
                    // keep facilities as stored (string) — view will handle presentation
                    'facilities' => $b->facilities ?? null,
                    'user_name' => $b->user->name ?? ($b->user->email ?? '-'),
                    'start' => $b->start_at->format('Y-m-d H:i'),
                    'end' => $b->end_at->format('Y-m-d H:i'),
                    'start_at' => $b->start_at,
                    'end_at' => $b->end_at,
                    'status' => $b->status,
                ];
            })->values();

            $weekDays[$day->format('Y-m-d')] = [
                'label' => $day->locale('id')->isoFormat('dddd, D MMM YYYY'),
                'bookings' => $bookingsForDay,
            ];
        }

        return view('dashboard', [
            'roomsCount' => $roomsCount,
            'bookingsToday' => $bookingsToday,
            'nextBookingTime' => $nextBookingTime,
            'weekDays' => $weekDays,
            'availableRooms' => Room::where('is_active', 1)->orderBy('name', 'asc')->get(),
            // include only active rooms for the dashboard widget
            'rooms' => Room::where('is_active', 1)->orderBy('name', 'asc')->get(),
            'allTimeBookings' => $allTimeBookings,
        ]);
    }
}

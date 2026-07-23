<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\Booking;
use Carbon\Carbon;

class AssetDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Recompute any derived asset flags if needed (skipped for assets)

        // assets count (active ones preferred)
        $assetsCount = Asset::where('status', 'active')->count();

        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();

        // bookings today (asset bookings only) - include bookings that overlap today
        $bookingsToday = Booking::where('type', 'asset')
            ->whereNotIn('status', ['cancelled'])
            ->where('start_at', '<=', $todayEnd)
            ->where('end_at', '>=', $todayStart)
            ->count();

        // next booking time
        $nextBooking = Booking::where('type', 'asset')
            ->whereNotIn('status', ['cancelled', 'done'])
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

        // week bookings (7 days starting today) filtered for assets
        $start = Carbon::today()->startOfDay();
        $end = (clone $start)->addDays(6)->endOfDay();

        // select bookings that overlap the 7-day window
        $weekBookings = Booking::with('asset', 'user')
            ->where('type', 'asset')
            ->whereNotIn('status', ['cancelled', 'done'])
            ->where('start_at', '<=', $end)
            ->where('end_at', '>=', $start)
            ->orderBy('created_at', 'desc')
            ->get();

        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $day = (clone $start)->addDays($i);
            $dayStart = $day->copy()->startOfDay();
            $dayEnd = $day->copy()->endOfDay();

            $bookingsForDay = $weekBookings->filter(function ($b) use ($dayStart, $dayEnd) {
                // include bookings that overlap the day (multi-day bookings will be present on each overlapped day)
                return $b->start_at <= $dayEnd && $b->end_at >= $dayStart;
            })->map(function ($b) {
                return [
                    'id' => $b->id,
                    'asset_name' => $b->asset->name ?? ($b->asset_name ?? '-'),
                    'title' => $b->title ?? '-',
                    // driver/pic info (store pic_name and driver directly)
                        'driver' => $b->driver ?? null,
                        'pic_name' => $b->pic_name ?? null,
                        'pic' => $b->pic_name ?? null,
                    // personnel and destination fields
                    'personnel' => $b->personnel ?? null,
                    'destination_text' => $b->destination_text ?? null,
                    'destination' => $b->destination ?? null,
                    'purpose' => $b->purpose ?? null,
                    'user_name' => $b->user->name ?? ($b->user->email ?? '-'),
                    'start' => $b->start_at->format('Y-m-d H:i'),
                    'end' => $b->end_at->format('Y-m-d H:i'),
                    'start_at' => $b->start_at,
                    'end_at' => $b->end_at,
                    'status' => $b->status,
                    'is_overdue' => (bool) $b->is_overdue,
                    'overdue_at' => $b->overdue_at ? $b->overdue_at : null,
                ];
            })->values();

            $weekDays[$day->format('Y-m-d')] = [
                'label' => $day->locale('id')->isoFormat('dddd, D MMM YYYY'),
                'bookings' => $bookingsForDay,
            ];
        }

            // additionally include overdue bookings: ended before now (includes earlier today) OR explicitly flagged is_overdue
            $overdueBookingsQuery = Booking::with('asset', 'user')
                ->where('type', 'asset')
                ->whereNotIn('status', ['cancelled', 'done', 'revision'])
                ->where(function($q) {
                    $q->where('is_overdue', true)
                      ->orWhere('end_at', '<', Carbon::now());
                })
                ->orderBy('end_at', 'desc')
                ->get();

            $overdueBookings = $overdueBookingsQuery->map(function ($b) {
                return [
                    'id' => $b->id,
                    'asset_name' => $b->asset->name ?? ($b->asset_name ?? '-'),
                    'title' => $b->title ?? '-',
                    'driver' => $b->driver ?? null,
                    'pic_name' => $b->pic_name ?? null,
                    'pic' => $b->pic_name ?? null,
                    'personnel' => $b->personnel ?? null,
                    'destination_text' => $b->destination_text ?? null,
                    'destination' => $b->destination ?? null,
                    'purpose' => $b->purpose ?? null,
                    'user_name' => $b->user->name ?? ($b->user->email ?? '-'),
                    'start_at' => $b->start_at,
                    'end_at' => $b->end_at,
                    'status' => $b->status,
                    'is_overdue' => (bool) $b->is_overdue,
                ];
            })->values();

        return view('dashboard_asset', [
            'assetsCount' => $assetsCount,
            'bookingsToday' => $bookingsToday,
            'nextBookingTime' => $nextBookingTime,
            'weekDays' => $weekDays,
            'overdueBookings' => $overdueBookings,
            'overdueCount' => $overdueBookings->count(),
            'availableAssets' => Asset::where('status', 'active')->orderBy('name', 'asc')->get(),
            'assets' => Asset::orderBy('name', 'asc')->get(),
        ]);
    }

    // overdue list rendering removed — handled inline in dashboard modal
}

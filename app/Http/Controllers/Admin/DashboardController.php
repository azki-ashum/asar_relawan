<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Room;
use App\Models\Asset;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function hub(Request $request)
    {
        $user = $request->user();
        if (! $user || ($user->role ?? '') !== 'admin') {
            abort(403, 'Access denied');
        }
        return view('admin.hub');
    }

    public function index(Request $request)
    {
        // simple admin check: expect `role` column on users (assumption)
        $user = $request->user();
        if (! $user || ($user->role ?? '') !== 'admin') {
            abort(403, 'Access denied');
        }

    $range = $request->get('range', 'month'); // day, week, month, year
    $type = $request->get('type', 'all'); // all, room, asset
        $now = Carbon::now();

        switch ($range) {
            case 'day':
                $start = $now->copy()->startOfDay();
                $end = $now->copy()->endOfDay();
                $step = 'hour';
                break;
            case 'week':
                $start = $now->copy()->startOfWeek();
                $end = $now->copy()->endOfWeek();
                $step = 'day';
                break;
            case 'year':
                $start = $now->copy()->startOfYear();
                $end = $now->copy()->endOfYear();
                $step = 'month';
                break;
            case 'month':
            default:
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                $step = 'day';
                break;
        }

        // Total bookings in range
        $totalBookings = Booking::whereNotIn('status', ['cancelled'])
            ->whereBetween('start_at', [$start, $end])
            ->count();

        $roomBookings = Booking::where('type', 'room')
            ->whereNotIn('status', ['cancelled'])
            ->whereBetween('start_at', [$start, $end])
            ->count();

        $assetBookings = Booking::where('type', 'asset')
            ->whereNotIn('status', ['cancelled'])
            ->whereBetween('start_at', [$start, $end])
            ->count();

        $roomsCount = Room::count();
        $assetsCount = Asset::count();

        // Top booked rooms in the range
        $topRooms = DB::table('bookings')
            ->select('room_id', DB::raw('count(*) as total'))
            ->where('type', 'room')
            ->whereNotIn('status', ['cancelled'])
            ->whereBetween('start_at', [$start, $end])
            ->whereNotNull('room_id')
            ->groupBy('room_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(function ($r) {
                $room = Room::find($r->room_id);
                return [
                    'room_id' => $r->room_id,
                    'name' => $room ? $room->name : 'Unknown',
                    'total' => (int) $r->total,
                ];
            });

        // Top booked assets in the range
        $topAssets = DB::table('bookings')
            ->select('asset_id', DB::raw('count(*) as total'))
            ->where('type', 'asset')
            ->whereNotIn('status', ['cancelled'])
            ->whereBetween('start_at', [$start, $end])
            ->whereNotNull('asset_id')
            ->groupBy('asset_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(function ($r) {
                $asset = Asset::find($r->asset_id);
                return [
                    'asset_id' => $r->asset_id,
                    'name' => $asset ? $asset->name : 'Unknown',
                    'total' => (int) $r->total,
                ];
            });

        // Top users by bookings with breakdown per type (room/asset)
        $topUsers = DB::table('bookings')
            ->select('user_id', DB::raw("count(*) as total"), DB::raw("SUM(CASE WHEN `type` = 'room' THEN 1 ELSE 0 END) as room_count"), DB::raw("SUM(CASE WHEN `type` = 'asset' THEN 1 ELSE 0 END) as asset_count"))
            ->whereNotIn('status', ['cancelled'])
            ->whereBetween('start_at', [$start, $end])
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(function ($r) {
                $user = User::find($r->user_id);
                return [
                    'user_id' => $r->user_id,
                    'name' => $user ? $user->name : 'Unknown',
                    'email' => $user ? $user->email : null,
                    'total' => (int) $r->total,
                    'room_count' => isset($r->room_count) ? (int) $r->room_count : 0,
                    'asset_count' => isset($r->asset_count) ? (int) $r->asset_count : 0,
                ];
            });

        // Top users specifically for rooms
        $topUsersRoom = DB::table('bookings')
            ->select('user_id', DB::raw('count(*) as total'))
            ->where('type', 'room')
            ->whereNotIn('status', ['cancelled'])
            ->whereBetween('start_at', [$start, $end])
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(function ($r) {
                $user = User::find($r->user_id);
                return [
                    'user_id' => $r->user_id,
                    'name' => $user ? $user->name : 'Unknown',
                    'email' => $user ? $user->email : null,
                    'total' => (int) $r->total,
                ];
            });

        // Top users specifically for assets (kendaraan)
        $topUsersAsset = DB::table('bookings')
            ->select('user_id', DB::raw('count(*) as total'))
            ->where('type', 'asset')
            ->whereNotIn('status', ['cancelled'])
            ->whereBetween('start_at', [$start, $end])
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(function ($r) {
                $user = User::find($r->user_id);
                return [
                    'user_id' => $r->user_id,
                    'name' => $user ? $user->name : 'Unknown',
                    'email' => $user ? $user->email : null,
                    'total' => (int) $r->total,
                ];
            });

        // Bookings over time series
        $labels = [];
        $series = [];

        $cursor = $start->copy();
        while ($cursor <= $end) {
            if ($step === 'hour') {
                $label = $cursor->format('H:00');
                $next = $cursor->copy()->addHour();
            } elseif ($step === 'day') {
                $label = $cursor->locale('id')->isoFormat('D MMM');
                $next = $cursor->copy()->addDay();
            } else { // month
                $label = $cursor->locale('id')->isoFormat('MMM');
                $next = $cursor->copy()->addMonth();
            }

            $q = Booking::whereNotIn('status', ['cancelled'])
                ->whereBetween('start_at', [$cursor, $next->copy()->subSecond()]);

            if ($type !== 'all') {
                $q->where('type', $type);
            }

            $count = $q->count();

            $labels[] = $label;
            $series[] = $count;

            $cursor = $next;
        }

        return view('admin.dashboard', [
            'range' => $range,
            'type' => $type,
            'start' => $start,
            'end' => $end,
            'totalBookings' => $totalBookings,
            'roomBookings' => $roomBookings,
            'assetBookings' => $assetBookings,
            'roomsCount' => $roomsCount,
            'assetsCount' => $assetsCount,
            'topRooms' => $topRooms,
            'topAssets' => $topAssets,
            'topUsers' => $topUsers,
            'topUsersRoom' => $topUsersRoom,
            'topUsersAsset' => $topUsersAsset,
            'chartLabels' => $labels,
            'chartSeries' => $series,
        ]);
    }
}

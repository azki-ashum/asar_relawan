<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        // return calendar view; bookings loaded via ajax (simple inline here)
        $rooms = Room::where('is_active', 1)->get();
        $userBookings = collect();
        if (Auth::check()) {
            // build user's bookings query and apply optional filters from request
            $query = Booking::with('room')
                ->where('user_id', Auth::id())
                ->where('type', 'room')
                ->orderBy('created_at', 'desc');

            // Filter behavior:
            // - If only one filter is provided, filter only by that field.
            // - If two filters are provided, require both (AND) for a more specific result.
            $titleProvided = $request->filled('title');
            $title = $request->query('title');

            // normalize start_date from common display formats to Y-m-d so whereDate works
            $sdRaw = $request->query('start_date');
            $startDate = null;
            if (!empty($sdRaw)) {
                try {
                    if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $sdRaw)) {
                        $startDate = Carbon::createFromFormat('d-m-Y', $sdRaw)->toDateString();
                    } elseif (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $sdRaw)) {
                        $startDate = Carbon::createFromFormat('d/m/Y', $sdRaw)->toDateString();
                    } else {
                        $startDate = Carbon::parse($sdRaw)->toDateString();
                    }
                } catch (\Exception $e) {
                    // parsing failed, ignore date filter
                    $startDate = null;
                }
            }

            $dateProvided = !empty($startDate);

            if ($titleProvided && $dateProvided) {
                // both provided: apply both filters (AND)
                $like = '%'.str_replace('%','\\%',$title).'%';
                $query->where('title', 'like', $like)
                      ->whereDate('start_at', $startDate);
            } elseif ($titleProvided) {
                $like = '%'.str_replace('%','\\%',$title).'%';
                $query->where('title', 'like', $like);
            } elseif ($dateProvided) {
                $query->whereDate('start_at', $startDate);
            }

            // paginate and preserve query parameters
            $userBookings = $query->paginate(10)->appends($request->query());
        }
        return view('bookings.index', compact('rooms', 'userBookings'));
    }

    public function create(Request $request)
    {
        // show only active rooms; non-admin users cannot see admin-only rooms
        $roomQuery = Room::where('is_active', 1)->orderBy('name');
        if (!str_starts_with(auth()->user()->role, 'admin')) {
            $roomQuery->where('is_admin_only', false);
        }
        $rooms = $roomQuery->get();
        $availableRooms = $rooms;
        // compute small dashboard-like stats for the left column
        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();

        $bookingsToday = Booking::whereNotIn('status', ['cancelled'])
            ->where('type', 'room')
            ->whereBetween('start_at', [$todayStart, $todayEnd])
            ->count();

        $nextBooking = Booking::whereNotIn('status', ['cancelled', 'done'])
            ->where('type', 'room')
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

        $availableRooms = $rooms;

        return view('bookings.create', compact('rooms', 'bookingsToday', 'nextBookingTime', 'availableRooms'));
    }

    public function store(Request $request) {
        $data = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'title' => 'nullable|string|max:255',
            'purpose' => 'nullable|string',
            'division' => 'nullable|string|max:128',
            'directorate' => 'nullable|string|max:128',
            'partner' => 'nullable|string|max:128',
            'participants_internal' => 'nullable|integer|min:0',
            'participants_external' => 'nullable|integer|min:0',
            'facilities' => 'nullable|string',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
        ]);

        // Guard: non-admin cannot book admin-only rooms
        $room = Room::findOrFail($data['room_id']);
        if ($room->is_admin_only && !str_starts_with(auth()->user()->role, 'admin')) {
            return back()->withInput()->with('error', 'Ruangan ini hanya dapat dibooking oleh Admin.');
        }

        // overlap validation
                // overlap if existing.start_at < new.end_at AND existing.end_at > new.start_at
                // this allows touching intervals where end == start to be valid (not overlap)
                $overlap = Booking::where('room_id', $data['room_id'])
                        ->where('type', 'room')
                        ->whereIn('status', ['pending', 'approved'])
                        ->where(function ($q) use ($data) {
                                $q->where('start_at', '<', $data['end_at'])
                                    ->where('end_at', '>', $data['start_at']);
                        })->exists();

        if ($overlap) {
            return back()->withInput()->with('error', 'Slot waktu berbenturan. Silakan pilih waktu lain.');
        }

        // create booking inside a transaction with a re-check (lock) to avoid race conditions
        $booking = null;
        try {
            DB::transaction(function () use ($data, &$booking) {
                // re-check overlap while locking matching booking rows to prevent concurrent inserts
                                // transactional re-check using strict interval overlap check
                                $overlap = Booking::where('room_id', $data['room_id'])
                                        ->where('type', 'room')
                                        ->whereIn('status', ['pending', 'approved'])
                                        ->where(function ($q) use ($data) {
                                                $q->where('start_at', '<', $data['end_at'])
                                                    ->where('end_at', '>', $data['start_at']);
                                        })->lockForUpdate()->exists();

                if ($overlap) {
                    throw new \Exception('overlap');
                }

                $booking = Booking::create([
                    'room_id' => $data['room_id'],
                    'user_id' => Auth::id(),
                    'title' => $data['title'] ?? null,
                    'purpose' => $data['purpose'] ?? null,
                    'division' => $data['division'] ?? null,
                    'directorate' => $data['directorate'] ?? null,
                    'partner' => $data['partner'] ?? null,
                    'participants_internal' => $data['participants_internal'] ?? null,
                    'participants_external' => $data['participants_external'] ?? null,
                    'facilities' => $data['facilities'] ?? null,
                    'start_at' => $data['start_at'],
                    'end_at' => $data['end_at'],
                    'type' => 'room',
                    // follow Google Sheet flow: user bookings are immediately approved
                    'status' => 'approved',
                ]);
                // Recompute room active state from bookings (preferred over direct toggles)
                $room = Room::find($booking->room_id);
                if ($room) {
                    $room->refreshActiveStatus();
                }
            });
        } catch (\Exception $e) {
            if ($e->getMessage() === 'overlap') {
                return back()->withInput()->with('error', 'Slot waktu berbenturan. Silakan pilih waktu lain.');
            }
            throw $e;
        }

        Log::info('Booking created', ['booking_id' => $booking->id, 'user_id' => Auth::id(), 'room_id' => $booking->room_id]);
        return redirect()->route('bookings.index')->with('success', 'Booking disimpan.');
    }

    public function edit(Booking $booking) {
        // only owner can edit
        // show only active rooms in edit form
        $rooms = Room::where('is_active', 1)->orderBy('name')->get();
        // compute small dashboard-like stats for the left column (same as create)
        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();

        $bookingsToday = Booking::whereNotIn('status', ['cancelled'])
            ->where('type', 'room')
            ->whereBetween('start_at', [$todayStart, $todayEnd])
            ->count();

        $nextBooking = Booking::whereNotIn('status', ['cancelled', 'done'])
            ->where('type', 'room')
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

        $availableRooms = Room::where('is_active', 1)->orderBy('name', 'asc')->get();

        return view('bookings.edit', compact('booking', 'rooms', 'bookingsToday', 'nextBookingTime', 'availableRooms'));
    }

    public function update(Request $request, Booking $booking) {

        $data = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'title' => 'nullable|string|max:255',
            'purpose' => 'nullable|string',
            'division' => 'nullable|string|max:128',
            'directorate' => 'nullable|string|max:128',
            'partner' => 'nullable|string|max:128',
            'participants_internal' => 'nullable|integer|min:0',
            'participants_external' => 'nullable|integer|min:0',
            'facilities' => 'nullable|string',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
        ]);

        // perform update inside transaction and re-check overlap with lock to avoid race
        try {
            DB::transaction(function () use ($data, $booking) {
                                // overlap if existing.start < new.end AND existing.end > new.start
                                $overlap = Booking::where('room_id', $data['room_id'])
                                        ->where('type', 'room')
                                        ->where('id', '!=', $booking->id)
                                        ->whereIn('status', ['pending', 'approved'])
                                        ->where(function ($q) use ($data) {
                                                $q->where('start_at', '<', $data['end_at'])
                                                    ->where('end_at', '>', $data['start_at']);
                                        })->lockForUpdate()->exists();

                if ($overlap) {
                    throw new \Exception('overlap');
                }

                // only update allowed fields (fillable)
                $booking->update(array_intersect_key($data, array_flip((new Booking)->getFillable())));
            });
        } catch (\Exception $e) {
            if ($e->getMessage() === 'overlap') {
                return back()->withInput()->with('error', 'Slot waktu berbenturan. Silakan pilih waktu lain.');
            }
            throw $e;
        }

    Log::info('Booking updated', ['booking_id' => $booking->id, 'user_id' => Auth::id(), 'room_id' => $booking->room_id]);
    return redirect()->route('bookings.index')->with('success', 'Booking diperbarui.');
    }

    public function cancel(Booking $booking)
    {
        $booking->update(['status' => 'cancelled']);

        // Recompute room active status after cancelling the booking
        $room = $booking->room;
        if ($room) {
            $room->refreshActiveStatus();
        }

        Log::info('Booking cancelled by user', ['booking_id' => $booking->id, 'user_id' => Auth::id()]);
        return redirect()->route('bookings.index')->with('success', 'Booking dibatalkan.');
    }

    /**
     * Mark a booking as done (completed) by the booking owner.
     */
    public function complete(Booking $booking)
    {
        // only owner may mark their booking as done
        // if (!Auth::check() || Auth::id() !== $booking->user_id) {
        //     Log::warning('Unauthorized attempt to complete booking', ['booking_id' => $booking->id, 'user_id' => Auth::id() ?? null]);
        //     return redirect()->route('bookings.index')->with('error', 'Anda tidak berwenang melakukan tindakan ini.');
        // }

        // update status and refresh room active state
        $booking->update(['status' => 'done']);

        $room = $booking->room;
        if ($room) {
            $room->refreshActiveStatus();
        }

        Log::info('Booking marked as done by user', ['booking_id' => $booking->id, 'user_id' => Auth::id()]);
        return redirect()->route('bookings.index')->with('success', 'Booking ditandai sebagai selesai.');
    }

    // admin functions
    public function adminIndex()
    {
        $request = request();

        $query = Booking::with('room', 'user')->where('type', 'room')->orderBy('created_at', 'desc');

        $hasQ = $request->filled('q');

        // normalize created_date from common display formats to Y-m-d so whereDate works
        $cdRaw = $request->query('created_date');
        $cd = null;
        if (!empty($cdRaw)) {
            try {
                if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $cdRaw)) {
                    $cd = Carbon::createFromFormat('d-m-Y', $cdRaw)->toDateString();
                } elseif (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $cdRaw)) {
                    $cd = Carbon::createFromFormat('d/m/Y', $cdRaw)->toDateString();
                } else {
                    $cd = Carbon::parse($cdRaw)->toDateString();
                }
            } catch (\Exception $e) {
                $cd = null;
            }
        }

        // New behavior: single filter -> filter only by that; both present -> require both (AND)
        if ($hasQ && $cd) {
            $qText = $request->query('q');
            $like = '%'.str_replace('%','\\%',$qText).'%';
            $query->where(function($q) use ($like, $cd) {
                $q->where(function($qq) use ($like) {
                    $qq->where('title', 'like', $like)
                       ->orWhere('purpose', 'like', $like)
                       ->orWhereHas('user', function ($uq) use ($like) {
                           $uq->where('name', 'like', $like);
                       });
                })->whereDate('created_at', $cd);
            });
        } elseif ($hasQ) {
            $qText = $request->query('q');
            $like = '%'.str_replace('%','\\%',$qText).'%';
            $query->where(function($q) use ($like) {
                $q->where('title', 'like', $like)
                  ->orWhere('purpose', 'like', $like)
                  ->orWhereHas('user', function ($uq) use ($like) {
                      $uq->where('name', 'like', $like);
                  });
            });
        } elseif ($cd) {
            $query->whereDate('created_at', $cd);
        }

        $bookings = $query->paginate(10)->appends($request->query());

        return view('admin.bookings.index', compact('bookings'));
    }

    public function adminOverride(Booking $booking)
    {
        // toggle approved/cancelled for simplicity
        $newStatus = $booking->status === 'approved' ? 'cancelled' : 'approved';
        $booking->update(['status' => $newStatus]);

        // Recompute room active state after admin override
        $room = $booking->room;
        if ($room) {
            $room->refreshActiveStatus();
        }

        Log::info('Booking status toggled by admin', ['booking_id' => $booking->id, 'admin_id' => Auth::id(), 'new_status' => $newStatus]);
        return redirect()->back()->with('success', 'Status booking diperbarui oleh admin.');
    }

    // Admin edit booking
    public function adminEdit(Booking $booking)
    {
        $rooms = Room::orderBy('name')->get();
        return view('admin.bookings.edit', compact('booking', 'rooms'));
    }

    public function adminUpdate(Request $request, Booking $booking)
    {
        // accept both the visible select (purpose_select) and the hidden 'purpose' field
        // so the server can derive the final value even if client JS fails to sync them.
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'title' => 'nullable|string|max:255',
            'purpose' => 'nullable|string',
            'purpose_select' => 'nullable|string',
            'purpose_other' => 'nullable|string',
            'division' => 'nullable|string|max:128',
            'directorate' => 'nullable|string|max:128',
            'partner' => 'nullable|string|max:128',
            'facilities' => 'nullable|string',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'status' => 'nullable|string',
            'participants_internal' => 'nullable|integer|min:0',
            'participants_external' => 'nullable|integer|min:0',
        ]);

        // Derive final purpose value prioritizing explicit fields:
        // - if purpose_select exists and is not '__other__', use it
        // - if purpose_select === '__other__', prefer purpose (hidden) or purpose_other
        // - otherwise fallback to validated['purpose'] if present
        $finalPurpose = null;
        if (!empty($validated['purpose_select'])) {
            if ($validated['purpose_select'] === '__other__') {
                $finalPurpose = $validated['purpose'] ?? $validated['purpose_other'] ?? null;
            } else {
                $finalPurpose = $validated['purpose_select'];
            }
        } else {
            $finalPurpose = $validated['purpose'] ?? null;
        }

        // Normalize into $data used for update
        $data = $validated;
        $data['purpose'] = $finalPurpose;
        // remove helper keys to avoid accidental mass-assignment
        unset($data['purpose_select'], $data['purpose_other']);

        // overlap validation excluding current booking
                // overlap if existing.start < new.end AND existing.end > new.start (touching endpoints allowed)
                $overlap = Booking::where('room_id', $data['room_id'])
                        ->where('id', '!=', $booking->id)
                        ->where('type', 'room')
                        ->whereIn('status', ['pending', 'approved'])
                        ->where(function ($q) use ($data) {
                                $q->where('start_at', '<', $data['end_at'])
                                    ->where('end_at', '>', $data['start_at']);
                        })->exists();

        if ($overlap) {
            return back()->withInput()->with('error', 'Slot waktu berbenturan.');
        }

        $booking->update($data);

        // If status was provided, ensure room flag follows status
        if (isset($data['status'])) {
            $room = $booking->room;
            if ($data['status'] === 'approved') {
                if ($room) {
                    $room->refreshActiveStatus();
                }
            } else {
                $ongoing = Booking::where('room_id', $booking->room_id)
                    ->where('id', '!=', $booking->id)
                    ->where('type', 'room')
                    ->whereIn('status', ['pending', 'approved'])
                    ->where('start_at', '<=', now())
                    ->where('end_at', '>', now())
                    ->exists();

                if ($room) {
                    $room->refreshActiveStatus();
                }
            }
        }

        Log::info('Booking updated by admin', ['booking_id' => $booking->id, 'admin_id' => Auth::id()]);
        return redirect()->route('admin.bookings.index')->with('success', 'Booking updated.');
    }

    public function adminDestroy(Booking $booking)
    {
        $roomId = $booking->room_id;
    $wasActive = ! in_array($booking->status, ['cancelled', 'done']);
        $booking->delete();

        // if deleted booking was active, ensure room active flag reflects current usage
        if ($wasActive) {
            $ongoing = Booking::where('room_id', $roomId)
                ->where('type', 'room')
                ->whereNotIn('status', ['cancelled', 'done'])
                ->where('start_at', '<=', now())
                ->where('end_at', '>=', now())
                ->exists();
            // Note: do not toggle room.is_active when deleting bookings; availability is computed from bookings.
        }

        Log::info('Booking deleted by admin', ['booking_id' => $booking->id, 'admin_id' => Auth::id()]);
        return redirect()->route('admin.bookings.index')->with('success', 'Booking deleted.');
    }

    public function events()
    {
    $bookings = Booking::with('room')->where('type', 'room')->whereNotIn('status', ['cancelled', 'done'])->get();
        $events = $bookings->map(function ($b) {
            return [
                'id' => $b->id,
                'title' => $b->title ?: $b->room->name,
                'start' => $b->start_at->toIso8601String(),
                'end' => $b->end_at->toIso8601String(),
                'purpose' => $b->purpose,
                'room' => $b->room->name,
                'color' => '#378006',
            ];
        })->toArray();

        return response()->json($events);
    }

    // return dates that have at least one approved booking (no time bound)
    public function calendarDates(Request $request)
    {
        $dates = [];
        $bookings = Booking::whereIn('status', ['approved', 'done'])
            ->where('type', 'room')
            ->select(['start_at', 'end_at'])
            ->get();

        foreach ($bookings as $b) {
            try {
                $start = \Carbon\Carbon::parse($b->start_at)->startOfDay();
                $end = \Carbon\Carbon::parse($b->end_at)->startOfDay();
            } catch (\Throwable $e) {
                continue;
            }

            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $dates[] = $d->format('Y-m-d');
            }
        }

        $dates = array_values(array_unique($dates));
        return response()->json($dates);
    }

    // return bookings for a given date (all bookings on that calendar day)
    public function bookingsByDate(Request $request)
    {
        $date = $request->query('date');
        if (empty($date)) {
            return response()->json(['error' => 'date required'], 400);
        }

        $startOfDay = date('Y-m-d 00:00:00', strtotime($date));
        $endOfDay = date('Y-m-d 23:59:59', strtotime($date));

        $bookings = Booking::with(['room:id,name', 'user:id,name,email'])
            ->where('type', 'room')
            ->whereIn('status', ['approved', 'done'])
            ->where(function ($q) use ($startOfDay, $endOfDay) {
                $q->whereBetween('start_at', [$startOfDay, $endOfDay])
                  ->orWhereBetween('end_at', [$startOfDay, $endOfDay])
                  ->orWhere(function ($q2) use ($startOfDay, $endOfDay) {
                      $q2->where('start_at', '<=', $startOfDay)
                         ->where('end_at', '>=', $endOfDay);
                  });
            })->get()->map(function ($b) {
                // Get user name from relationship or fallback to pic_name if relationship is null
                $userName = null;
                if ($b->user) {
                    $userName = $b->user->name;
                } elseif (!empty($b->pic_name)) {
                    $userName = $b->pic_name;
                }
                
                return [
                    'id' => $b->id,
                    'title' => $b->title,
                    'room_name' => $b->room ? $b->room->name : null,
                    'start_at' => $b->start_at->toDateTimeString(),
                    'end_at' => $b->end_at->toDateTimeString(),
                    'user_name' => $userName,
                    'user_id' => $b->user_id,
                    'pic_name' => $b->pic_name,
                    'purpose' => $b->purpose,
                    'division' => $b->division,
                    'directorate' => $b->directorate,
                    'status' => $b->status,
                ];
            })->toArray();

        return response()->json($bookings);
    }
}

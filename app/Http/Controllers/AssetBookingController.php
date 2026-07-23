<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Room;
use App\Models\Asset;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AssetBookingController extends Controller
{
    public function index(Request $request)
    {
        // asset bookings list for current user
        $userBookings = collect();
        if (Auth::check()) {
            $query = Booking::with('user')
                ->where('user_id', Auth::id())
                ->where('type', 'asset')
                ->orderBy('created_at', 'desc');

            $titleProvided = $request->filled('title');
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
                    $startDate = null;
                }
            }

            $dateProvided = !empty($startDate);

            if ($titleProvided && $dateProvided) {
                $like = '%'.str_replace('%','\\%',$request->query('title')).'%';
                $query->where('title', 'like', $like)->whereDate('start_at', $startDate);
            } elseif ($titleProvided) {
                $like = '%'.str_replace('%','\\%',$request->query('title')).'%';
                $query->where('title', 'like', $like);
            } elseif ($dateProvided) {
                $query->whereDate('start_at', $startDate);
            }

            $userBookings = $query->paginate(10)->appends($request->query());
        }

        // Check if the user has any pending revision (to disable the Book button)
        $pendingRevisions = Auth::check()
            ? Booking::with('asset')->where('user_id', Auth::id())->where('type', 'asset')->where('status', 'revision')->get()
            : collect();

        return view('asset.bookings', compact('userBookings', 'pendingRevisions'));
    }

    public function create(Request $request)
    {
        // Block user from accessing create form if they have a pending revision
        $pendingRevisions = Booking::with('asset')->where('user_id', Auth::id())
            ->where('type', 'asset')
            ->where('status', 'revision')
            ->get();

        if ($pendingRevisions->isNotEmpty()) {
            $details = $pendingRevisions->map(function ($rev) {
                $assetName = $rev->asset->name ?? 'kendaraan';
                $notes = $rev->revision_notes ? " — Catatan: {$rev->revision_notes}" : '';
                return "ID #{$rev->id} ({$assetName}){$notes}";
            })->implode('; ');
            return redirect()->route('asset.bookings.index')->with('error',
                "Anda tidak dapat membuat booking baru karena ada booking kendaraan yang masih dalam status revisi: {$details}. Silakan upload ulang bukti foto terlebih dahulu."
            );
        }

        // prepare small dashboard stats similar to room flow
        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();

        $bookingsToday = Booking::where('type', 'asset')
            ->whereNotIn('status', ['cancelled'])
            ->whereBetween('start_at', [$todayStart, $todayEnd])
            ->count();

        $nextBooking = Booking::where('type', 'asset')
            ->whereNotIn('status', ['cancelled', 'done'])
            ->where('start_at', '>', Carbon::now())
            ->orderBy('start_at', 'asc')
            ->first();

        $nextBookingTime = '-';
        if ($nextBooking) {
            try { $nextBookingTime = $nextBooking->start_at->locale('id')->isoFormat('dddd, D MMM YYYY HH:mm'); } catch (\Throwable $e) { $nextBookingTime = $nextBooking->start_at->format('Y-m-d H:i'); }
        }

        // load only active assets; non-admin users cannot see admin-only assets
        $assetQuery = Asset::where('status', 'active')->orderBy('name');
        if (!str_starts_with(auth()->user()->role, 'admin')) {
            $assetQuery->where('is_admin_only', false);
        }
        $assets = $assetQuery->get();
        $users = User::orderBy('name')->get();

        return view('asset.create', compact('bookingsToday', 'nextBookingTime', 'assets', 'users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'asset_id' => 'nullable|exists:assets,id',
            'asset_name' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'driver' => 'required|string|min:3|max:255|regex:/^(?![\ \-]*$).+$/',
            'purpose' => 'nullable|string',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'division' => 'nullable|string|max:128',
            'directorate' => 'nullable|string|max:128',
            'destination' => 'nullable',
            'destination_text' => 'nullable|string|max:255',
            'destination_lat' => 'nullable|numeric',
            'destination_lng' => 'nullable|numeric',
            'pic_name' => 'nullable|string|max:255',
            'personnel' => 'nullable|array',
            'personnel.*' => 'nullable|string',
        ], [
            'driver.required' => 'Driver wajib diisi.',
            'driver.min' => 'Driver minimal 3 karakter.',
            'driver.regex' => 'Driver harus diisi dengan teks yang bermakna, tidak boleh hanya "-" atau spasi.',
        ]);

        // Block user if they have any pending revision booking that hasn't been resolved
        $pendingRevisions = Booking::with('asset')->where('user_id', Auth::id())
            ->where('type', 'asset')
            ->where('status', 'revision')
            ->get();

        if ($pendingRevisions->isNotEmpty()) {
            $details = $pendingRevisions->map(function ($rev) {
                $assetName = $rev->asset->name ?? 'kendaraan';
                $notes = $rev->revision_notes ? " — Catatan: {$rev->revision_notes}" : '';
                return "ID #{$rev->id} ({$assetName}){$notes}";
            })->implode('; ');
            return back()->withInput()->with('error',
                "Anda tidak dapat membuat booking baru karena ada booking kendaraan yang masih dalam status revisi: {$details}. Silakan upload ulang bukti foto terlebih dahulu."
            );
        }

        // determine asset id: prefer asset_id, otherwise create asset from asset_name
        if (empty($data['asset_id']) && !empty($data['asset_name'])) {
            $asset = Asset::firstOrCreate(['name' => $data['asset_name']], ['status' => 'active']);
            $data['asset_id'] = $asset->id;
        }

        if (empty($data['asset_id'])) {
            return back()->withInput()->with('error', 'Asset harus dipilih atau diberi nama.');
        }

        // Guard: non-admin cannot book admin-only assets
        $asset = Asset::findOrFail($data['asset_id']);
        if ($asset->is_admin_only && !str_starts_with(auth()->user()->role, 'admin')) {
            return back()->withInput()->with('error', 'Kendaraan ini hanya dapat dibooking oleh Admin.');
        }

        // overlap check for same asset
        // Block if there's an overlapping pending/approved booking OR any booking currently in use / marked overdue and not returned
        $overlap = Booking::where('type', 'asset')
                ->where('asset_id', $data['asset_id'])
                ->where(function ($q) use ($data) {
                    $q->where(function ($qq) use ($data) {
                        $qq->whereIn('status', ['pending', 'approved'])
                           ->where('start_at', '<', $data['end_at'])
                           ->where('end_at', '>', $data['start_at']);
                    })
                    ->orWhere(function ($qq) {
                        $qq->whereIn('status', ['in_use'])
                           ->whereNull('returned_at');
                    })
                    ->orWhere(function ($qq) {
                        $qq->where('is_overdue', true)
                           ->whereNull('returned_at');
                    });
                })->exists();

        if ($overlap) {
            return back()->withInput()->with('error', 'Tidak dapat membuat booking: kendaraan sudah dipesan, sedang digunakan, atau belum dikembalikan. Silakan periksa ketersediaan atau pilih waktu lain.');
        }

        try {
            DB::transaction(function () use ($data, &$booking, $request) {
                $overlap = Booking::where('type', 'asset')->where('asset_id', $data['asset_id'])
                    ->where(function ($q) use ($data) {
                        $q->where(function ($qq) use ($data) {
                            $qq->whereIn('status', ['pending', 'approved'])
                               ->where('start_at', '<', $data['end_at'])
                               ->where('end_at', '>', $data['start_at']);
                        })
                        ->orWhere(function ($qq) {
                            $qq->whereIn('status', ['in_use'])
                               ->whereNull('returned_at');
                        })
                        ->orWhere(function ($qq) {
                            $qq->where('is_overdue', true)
                               ->whereNull('returned_at');
                        });
                    })->lockForUpdate()->exists();

                if ($overlap) {
                    throw new \Exception('overlap');
                }

                // snapshot upload removed from booking creation; capture on completion instead

                // normalize personnel array (remove empty)
                $personnel = $data['personnel'] ?? [];
                if (is_array($personnel)) {
                    $personnel = array_values(array_filter(array_map(function($v){ return is_string($v) ? trim($v) : $v; }, $personnel), function($v){ return !(is_null($v) || $v === ''); }));
                }

                $booking = Booking::create([
                    'asset_id' => $data['asset_id'],
                    'user_id' => Auth::id(),
                    'driver' => $data['driver'] ?? null,
                    'pic_name' => $data['pic_name'] ?? null,
                    'title' => $data['title'] ?? null,
                    'purpose' => $data['purpose'] ?? null,
                    'division' => $data['division'] ?? null,
                    'directorate' => $data['directorate'] ?? null,
                    'destination' => $data['destination'] ?? null,
                    'destination_text' => $data['destination_text'] ?? null,
                    'destination_lat' => $data['destination_lat'] ?? null,
                    'destination_lng' => $data['destination_lng'] ?? null,
                    'personnel' => $personnel,
                    'start_at' => $data['start_at'],
                    'end_at' => $data['end_at'],
                    'status' => 'approved',
                    'type' => 'asset',
                ]);
            });
        } catch (\Exception $e) {
            if ($e->getMessage() === 'overlap') {
                return back()->withInput()->with('error', 'Slot waktu berbenturan. Silakan pilih waktu lain.');
            }
            throw $e;
        }

        Log::info('Asset booking created', ['booking_id' => $booking->id, 'user_id' => Auth::id(), 'asset_id' => $booking->asset_id]);
        return redirect()->route('asset.bookings.index')->with('success', 'Booking kendaraan disimpan.');
    }

    public function edit(Booking $booking)
    {
        // only allow editing asset bookings
        if ($booking->type !== 'asset') abort(404);
        // prepare small dashboard stats and lists so the view is driven by controller
        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();

        $bookingsToday = Booking::where('type', 'asset')
            ->whereNotIn('status', ['cancelled'])
            ->whereBetween('start_at', [$todayStart, $todayEnd])
            ->count();

        $nextBooking = Booking::where('type', 'asset')
            ->whereNotIn('status', ['cancelled', 'done'])
            ->where('start_at', '>', Carbon::now())
            ->orderBy('start_at', 'asc')
            ->first();

        $nextBookingTime = '-';
        if ($nextBooking) {
            try { $nextBookingTime = $nextBooking->start_at->locale('id')->isoFormat('dddd, D MMM YYYY HH:mm'); } catch (\Throwable $e) { $nextBookingTime = $nextBooking->start_at->format('Y-m-d H:i'); }
        }

        // load only active assets and users for selects and the dashboard partial
        $assets = Asset::where('status', 'active')->orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('asset.edit', compact('booking', 'assets', 'users', 'bookingsToday', 'nextBookingTime'));
    }

    public function update(Request $request, Booking $booking)
    {
        if ($booking->type !== 'asset') abort(404);
        $data = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'title' => 'nullable|string|max:255',
            'driver' => 'required|string|min:3|max:255|regex:/^(?![\ \-]*$).+$/',
            'purpose' => 'nullable|string',
            'division' => 'nullable|string|max:128',
            'directorate' => 'nullable|string|max:128',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'destination' => 'nullable',
            'destination_text' => 'nullable|string|max:255',
            'destination_lat' => 'nullable|numeric',
            'destination_lng' => 'nullable|numeric',
            'pic_name' => 'nullable|string|max:255',
            'personnel' => 'nullable|array',
            'personnel.*' => 'nullable|string',
        ], [
            'driver.required' => 'Driver wajib diisi.',
            'driver.min' => 'Driver minimal 3 karakter.',
            'driver.regex' => 'Driver harus diisi dengan teks yang bermakna, tidak boleh hanya "-" atau spasi.',
        ]);

        try {
            // normalize personnel in $data
            if (isset($data['personnel']) && is_array($data['personnel'])) {
                $data['personnel'] = array_values(array_filter(array_map(function($v){ return is_string($v) ? trim($v) : $v; }, $data['personnel']), function($v){ return !(is_null($v) || $v === ''); }));
            }

            DB::transaction(function () use ($data, $booking) {
                $overlap = Booking::where('type', 'asset')->where('asset_id', $data['asset_id'])
                    ->where('id', '!=', $booking->id)
                    ->where(function ($q) use ($data) {
                        $q->where(function ($qq) use ($data) {
                            $qq->whereIn('status', ['pending', 'approved'])
                               ->where('start_at', '<', $data['end_at'])
                               ->where('end_at', '>', $data['start_at']);
                        })
                        ->orWhere(function ($qq) {
                            $qq->whereIn('status', ['in_use'])
                               ->whereNull('returned_at');
                        })
                        ->orWhere(function ($qq) {
                            $qq->where('is_overdue', true)
                               ->whereNull('returned_at');
                        });
                    })->lockForUpdate()->exists();

                if ($overlap) throw new \Exception('overlap');

                $booking->update(array_intersect_key($data, array_flip((new Booking)->getFillable())));
            });
        } catch (\Exception $e) {
            if ($e->getMessage() === 'overlap') {
                return back()->withInput()->with('error', 'Slot waktu berbenturan. Silakan pilih waktu lain.');
            }
            throw $e;
        }

        Log::info('Asset booking updated', ['booking_id' => $booking->id, 'user_id' => Auth::id(), 'asset_id' => $booking->asset_id]);
        return redirect()->route('asset.bookings.index')->with('success', 'Booking kendaraan diperbarui.');
    }

    public function cancel(Booking $booking)
    {
        if ($booking->type !== 'asset') abort(404);
        $booking->update(['status' => 'cancelled']);
        Log::info('Asset booking cancelled', ['booking_id' => $booking->id, 'user_id' => Auth::id()]);
        return redirect()->route('asset.bookings.index')->with('success', 'Booking kendaraan dibatalkan.');
    }

    // ...checkout functionality removed; scheduler will set status to in_use at start_at

    public function complete(Request $request, Booking $booking)
    {
        if ($booking->type !== 'asset') abort(404);

        // Require a snapshot file when marking an asset booking as complete
        $data = $request->validate([
            'asset_snapshot_file' => 'required|image|max:5120', // max 5MB
        ]);

        // handle uploaded snapshot file if provided at completion time
        $now = Carbon::now();
        if ($request->hasFile('asset_snapshot_file')) {
            $f = $request->file('asset_snapshot_file');
            $path = $f->store('asset_snapshots', 'public');
            $snapshot = [
                'file' => $path,
                'original' => $f->getClientOriginalName(),
                'uploaded_by' => Auth::id(),
                'uploaded_at' => $now->toDateTimeString(),
            ];

            $booking->update([
                'asset_snapshot' => $snapshot,
                'status' => 'done',
                'returned_at' => $now,
                'finished_at' => $now,
                'is_overdue' => false,
            ]);
        } else {
            $booking->update(['status' => 'done', 'returned_at' => $now, 'finished_at' => $now, 'is_overdue' => false]);
        }

        Log::info('Asset booking marked done', ['booking_id' => $booking->id, 'user_id' => Auth::id()]);
        return redirect()->route('asset.bookings.index')->with('success', 'Booking kendaraan ditandai selesai.');
    }

    // admin listing for asset bookings
    public function adminIndex()
    {
        $request = request();
        $query = Booking::with(['user','asset'])->where('type', 'asset')->orderBy('created_at', 'desc');

        $hasQ = $request->filled('q');
        $cdRaw = $request->query('created_date');
        $cd = null;
        if (!empty($cdRaw)) {
            try {
                if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $cdRaw)) $cd = Carbon::createFromFormat('d-m-Y', $cdRaw)->toDateString();
                elseif (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $cdRaw)) $cd = Carbon::createFromFormat('d/m/Y', $cdRaw)->toDateString();
                else $cd = Carbon::parse($cdRaw)->toDateString();
            } catch (\Exception $e) { $cd = null; }
        }

        if ($hasQ && $cd) {
            $qText = $request->query('q');
            $like = '%'.str_replace('%','\\%',$qText).'%';
            $query->where(function($q) use ($like, $cd) {
                $q->where(function($qq) use ($like) {
                    $qq->where('title', 'like', $like)
                       ->orWhere('purpose', 'like', $like)
                       ->orWhereHas('user', function ($uq) use ($like) { $uq->where('name', 'like', $like); });
                })->whereDate('created_at', $cd);
            });
        } elseif ($hasQ) {
            $qText = $request->query('q');
            $like = '%'.str_replace('%','\\%',$qText).'%';
            $query->where(function($q) use ($like) {
                $q->where('title', 'like', $like)
                  ->orWhere('purpose', 'like', $like)
                  ->orWhereHas('user', function ($uq) use ($like) { $uq->where('name', 'like', $like); });
            });
        } elseif ($cd) {
            $query->whereDate('created_at', $cd);
        }

    $bookings = $query->paginate(10)->appends($request->query());
    return view('admin.asset_bookings.index', compact('bookings'));
    }

    // show admin edit form for asset booking
    public function adminEdit(Booking $booking)
    {
        if ($booking->type !== 'asset') abort(404);
        // load active assets + the currently booked asset (even if inactive/retired)
        $assets = Asset::where('status', 'active')
            ->orWhere('id', $booking->asset_id)
            ->orderBy('name')
            ->get();
        $users = User::orderBy('name')->get();
        $statuses = ['pending','approved','in_use','cancelled','done','revision'];
        return view('admin.asset_bookings.edit', compact('booking','assets','users','statuses'));
    }

    // admin update allowed to change status
    public function adminUpdate(Request $request, Booking $booking)
    {
        if ($booking->type !== 'asset') abort(404);
        $data = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'title' => 'nullable|string|max:255',
            'driver' => 'required|string|min:3|max:255|regex:/^(?![\ \-]*$).+$/',
            'purpose' => 'nullable|string',
            'division' => 'nullable|string|max:128',
            'directorate' => 'nullable|string|max:128',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'destination' => 'nullable',
            'destination_text' => 'nullable|string|max:255',
            'destination_lat' => 'nullable|numeric',
            'destination_lng' => 'nullable|numeric',
            'pic_name' => 'nullable|string|max:255',
            'personnel' => 'nullable|array',
            'personnel.*' => 'nullable|string',
            'status' => 'required|in:pending,approved,in_use,cancelled,done,revision',
        ], [
            'driver.required' => 'Driver wajib diisi.',
            'driver.min' => 'Driver minimal 3 karakter.',
            'driver.regex' => 'Driver harus diisi dengan teks yang bermakna, tidak boleh hanya "-" atau spasi.',
        ]);

        if (isset($data['personnel']) && is_array($data['personnel'])) {
            $data['personnel'] = array_values(array_filter(array_map(function($v){ return is_string($v) ? trim($v) : $v; }, $data['personnel']), function($v){ return !(is_null($v) || $v === ''); }));
        }

        DB::transaction(function() use ($data, $booking) {
            $overlap = Booking::where('type', 'asset')->where('asset_id', $data['asset_id'])
                ->where('id', '!=', $booking->id)
                ->where(function ($q) use ($data) {
                    $q->where(function ($qq) use ($data) {
                        $qq->whereIn('status', ['pending', 'approved'])
                           ->where('start_at', '<', $data['end_at'])
                           ->where('end_at', '>', $data['start_at']);
                    })
                    ->orWhere(function ($qq) {
                        $qq->whereIn('status', ['in_use'])
                           ->whereNull('returned_at');
                    })
                    ->orWhere(function ($qq) {
                        $qq->where('is_overdue', true)
                           ->whereNull('returned_at');
                    });
                })->lockForUpdate()->exists();

            if ($overlap) throw new \Exception('overlap');

            $booking->update(array_intersect_key($data, array_flip((new Booking)->getFillable())));
        });

        Log::info('Admin updated asset booking', ['booking_id' => $booking->id, 'admin_id' => Auth::id()]);
        return redirect()->route('asset.admin.bookings.index')->with('success', 'Booking kendaraan diperbarui oleh admin.');
    }

    // Admin revision – rollback a completed booking for photo re-upload
    public function adminRevision(Request $request, Booking $booking)
    {
        if ($booking->type !== 'asset') abort(404);

        // Only allow revision on bookings that are currently 'done'
        if ($booking->status !== 'done') {
            return back()->with('error', 'Hanya booking dengan status selesai yang bisa direvisi.');
        }

        $data = $request->validate([
            'revision_notes' => 'nullable|string|max:1000',
        ]);

        $booking->update([
            'status' => 'revision',
            'revision_notes' => $data['revision_notes'] ?? null,
            'revision_count' => ($booking->revision_count ?? 0) + 1,
            // Clear the snapshot so user must re-upload
            'asset_snapshot' => null,
            'returned_at' => null,
            'finished_at' => null,
        ]);

        Log::info('Admin set booking to revision', ['booking_id' => $booking->id, 'admin_id' => Auth::id(), 'revision_count' => $booking->revision_count]);
        return redirect()->route('asset.admin.bookings.index')->with('success', 'Booking telah direvisi. User harus mengunggah ulang bukti foto.');
    }

    // Admin cancel revision – revert a revision booking back to done
    public function cancelRevision(Booking $booking)
    {
        if ($booking->type !== 'asset') abort(404);

        if ($booking->status !== 'revision') {
            return back()->with('error', 'Booking ini tidak dalam status revisi.');
        }

        $now = Carbon::now();
        $booking->update([
            'status'         => 'done',
            'revision_notes' => null,
            'revision_count' => max(0, ($booking->revision_count ?? 1) - 1),
            'returned_at'    => $booking->returned_at ?? $now,
            'finished_at'    => $booking->finished_at ?? $now,
        ]);

        Log::info('Admin cancelled revision', ['booking_id' => $booking->id, 'admin_id' => Auth::id()]);
        return redirect()->route('asset.admin.bookings.index')->with('success', 'Revisi dibatalkan. Booking dikembalikan ke status selesai.');
    }

    // User resubmit – re-upload photo proof after admin revision
    public function resubmit(Request $request, Booking $booking)
    {
        if ($booking->type !== 'asset') abort(404);

        // Only allow resubmit when status is 'revision'
        if ($booking->status !== 'revision') {
            return back()->with('error', 'Booking ini tidak dalam status revisi.');
        }

        // Only the booking owner can resubmit
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'asset_snapshot_file' => 'required|image|max:5120',
        ]);

        $now = Carbon::now();
        $f = $request->file('asset_snapshot_file');
        $path = $f->store('asset_snapshots', 'public');
        $snapshot = [
            'file' => $path,
            'original' => $f->getClientOriginalName(),
            'uploaded_by' => Auth::id(),
            'uploaded_at' => $now->toDateTimeString(),
        ];

        $booking->update([
            'asset_snapshot' => $snapshot,
            'status' => 'done',
            'returned_at' => $now,
            'finished_at' => $now,
            'revision_notes' => null, // Clear revision notes after resubmit
        ]);

        Log::info('User resubmitted booking after revision', ['booking_id' => $booking->id, 'user_id' => Auth::id()]);
        return redirect()->route('asset.bookings.index')->with('success', 'Bukti foto berhasil diunggah ulang. Booking ditandai selesai.');
    }

    // Admin destroy - delete an asset booking
    public function adminDestroy(Booking $booking)
    {
        if ($booking->type !== 'asset') abort(404);
        $booking->delete();
        Log::info('Admin deleted asset booking', ['booking_id' => $booking->id, 'admin_id' => Auth::id()]);
        return redirect()->route('asset.admin.bookings.index')->with('success', 'Booking kendaraan dihapus.');
    }

    // return dates that have at least one approved asset booking (no time bound)
    public function calendarDates(Request $request)
    {
        // For multi-day bookings we need to mark every calendar day that the booking overlaps.
        // Query approved asset bookings and expand each booking into its covered Y-m-d dates.
        $dates = [];
                // include approved, in_use, done (past), and overdue bookings so calendar shows all booking dates
                $bookings = Booking::where('type', 'asset')
                        ->where(function($q){
                                $q->whereIn('status', ['approved', 'in_use', 'done'])
                                    ->orWhere('is_overdue', true);
                        })
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

    // return asset bookings for a given date (all bookings on that calendar day)
    public function bookingsByDate(Request $request)
    {
        $date = $request->query('date');
        if (empty($date)) {
            return response()->json(['error' => 'date required'], 400);
        }

        $startOfDay = date('Y-m-d 00:00:00', strtotime($date));
        $endOfDay = date('Y-m-d 23:59:59', strtotime($date));

                // include approved, in_use, done (past), and overdue bookings when returning bookings for a date
                $bookings = Booking::with(['asset:id,name', 'user:id,name,email'])
                        ->where('type', 'asset')
                        ->where(function($q){
                                $q->whereIn('status', ['approved', 'in_use', 'done'])
                                    ->orWhere('is_overdue', true);
                        })
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
                    'asset_name' => $b->asset ? $b->asset->name : null,
                    'start_at' => $b->start_at->toDateTimeString(),
                    'end_at' => $b->end_at->toDateTimeString(),
                    'user_name' => $userName,
                    'pic_name' => $b->pic_name,
                    'purpose' => $b->purpose,
                    'division' => $b->division,
                    'directorate' => $b->directorate,
                    'status' => $b->status,
                    'is_overdue' => (bool) $b->is_overdue,
                    'overdue_at' => $b->overdue_at ? $b->overdue_at->toDateTimeString() : null,
                ];
            })->toArray();

        return response()->json($bookings);
    }

    // Stream snapshot image for a booking from public disk
    public function snapshot(Booking $booking)
    {
        if ($booking->type !== 'asset') abort(404);
        $snap = is_array($booking->asset_snapshot)
            ? $booking->asset_snapshot
            : (is_string($booking->asset_snapshot) ? json_decode($booking->asset_snapshot, true) : null);
        if (!$snap || empty($snap['file'])) abort(404);

        $path = $snap['file'];
        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) abort(404);

        return \Illuminate\Support\Facades\Storage::disk('public')->response($path);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use Illuminate\Support\Facades\Log;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::where('is_active', 1)->get();
        return view('rooms.index', compact('rooms'));
    }

    // Admin CMS methods
    public function adminIndex()
    {
        $rooms = Room::orderBy('name')->paginate(20);
        return view('admin.rooms.index', compact('rooms'));
    }

    public function create()
    {
        return view('admin.rooms.create');
    }

    public function store(Request $request)
    {
    Log::info('Room store request received', ['user_id' => auth()->id(), 'input' => $request->all()]);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1',
            'is_active' => 'sometimes|boolean',
        ]);

        $data['is_active'] = $request->has('is_active') ? 1 : 0;
        $data['is_admin_only'] = $request->has('is_admin_only') ? 1 : 0;
    $room = Room::create($data);
    Log::info('Room created', ['room_id' => $room->id, 'user_id' => auth()->id()]);
    return redirect()->route('admin.rooms.index')->with('success', 'Room created.');
    }

    public function edit(Room $room)
    {
        return view('admin.rooms.edit', compact('room'));
    }

    public function update(Request $request, Room $room)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1',
            // do not validate is_active here; we will persist it only when explicitly provided
        ]);

        // Explicitly set is_active based on checkbox presence
        // If checkbox is checked, it sends "1", otherwise it's not sent at all
        $data['is_active'] = $request->has('is_active') ? 1 : 0;
        $data['is_admin_only'] = $request->has('is_admin_only') ? 1 : 0;

        $room->update($data);

        Log::info('Room updated', ['room_id' => $room->id, 'user_id' => auth()->id()]);
        return redirect()->route('admin.rooms.index')->with('success', 'Room updated.');
    }

    public function destroy(Room $room)
    {
    $room->delete();
    Log::info('Room deleted', ['room_id' => $room->id, 'user_id' => auth()->id()]);
    return redirect()->route('admin.rooms.index')->with('success', 'Room deleted.');
    }
}

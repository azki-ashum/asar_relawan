<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;

class UpdateRoomNamesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * We'll update first four rooms ordered by id to the requested names.
     */
    public function run()
    {
        $names = [
            'BoD · Lantai 4',
            'Dutaqu · Lantai 2',
            'VIP · Lantai 2',
            'Studio · Lantai 2',
        ];

        $rooms = Room::orderBy('id')->take(4)->get();
        foreach ($rooms as $i => $room) {
            if (isset($names[$i])) {
                $room->name = $names[$i];
                $room->save();
            }
        }
    }
}

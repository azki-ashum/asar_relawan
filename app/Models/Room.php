<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_active',
        'is_admin_only',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'is_admin_only' => 'boolean',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Recompute and persist is_active based on current bookings.
     * If there is any booking currently running (approved or pending) overlapping now,
     * room should be inactive (0). Otherwise active (1).
     */
    public function refreshActiveStatus(): void
    {
        // $now = now();
        // $ongoing = $this->bookings()
        //     ->whereIn('status', ['pending', 'approved'])
        //     ->where('start_at', '<=', $now)
        //     ->where('end_at', '>', $now)
        //     ->exists();

        // $shouldBeActive = $ongoing ? 0 : 1;
        // if ($this->is_active != $shouldBeActive) {
        //     $this->is_active = $shouldBeActive;
        //     $this->save();
        // }
    }
}

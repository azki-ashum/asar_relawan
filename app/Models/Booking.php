<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'asset_id',
        'pic_name',
        'user_id',
        'title',
        'purpose',
        'division',
        'directorate',
        'driver',
        'partner',
        'participants_internal',
        'participants_external',
        'facilities',
        'start_at',
        'end_at',
        'checked_out_at',
        'returned_at',
        'finished_at',
        'is_overdue',
        'overdue_at',
        'status',
        'type',
        'destination',
        'destination_text',
        'destination_lat',
        'destination_lng',
        'asset_snapshot',
        'personnel',
        'end_diff_minutes',
        'revision_notes',
        'revision_count',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'checked_out_at' => 'datetime',
        'returned_at' => 'datetime',
        'finished_at' => 'datetime',
        'is_overdue' => 'boolean',
        'overdue_at' => 'datetime',
        'end_diff_minutes' => 'integer',
        'revision_count' => 'integer',
        'participants_internal' => 'integer',
        'participants_external' => 'integer',
        'destination' => 'array',
        'asset_snapshot' => 'array',
        'personnel' => 'array',
        'destination_lat' => 'decimal:7',
        'destination_lng' => 'decimal:7',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function fuelPhotos()
    {
        return $this->hasMany(\App\Models\BookingFuelPhoto::class);
    }
}

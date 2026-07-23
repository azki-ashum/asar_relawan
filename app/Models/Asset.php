<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'type_id',
        'code',
        'name',
        'status',
        'is_admin_only',
    ];

    protected $casts = [
        'is_admin_only' => 'boolean',
    ];

    public function type()
    {
        return $this->belongsTo(AssetType::class, 'type_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Generate a unique asset code in format: {TypeInitial}{TypeId}{NameInitials}
     * Example: Kendaraan (id=1) + "Budi V." => K1BV
     * If code exists, append _01, _02, ... to ensure uniqueness.
     */
    public static function generateCode(?\App\Models\AssetType $type, string $name, ?int $assetId = null)
    {
        // Type initial: first alpha char of display_name
        $typeInitial = 'X';
        $typeId = '0';
        if ($type) {
            $display = $type->display_name ?? '';
            // find first alphanumeric character
            preg_match('/[A-Z0-9]/i', $display, $m);
            if (!empty($m[0])) {
                $typeInitial = strtoupper($m[0]);
            }
            $typeId = (int) ($type->id ?? 0);
        }

        // Name initials: take first letter of each word (up to 4 letters)
        $words = preg_split('/[^A-Za-z0-9]+/', $name, -1, PREG_SPLIT_NO_EMPTY);
        $initials = '';
        foreach ($words as $w) {
            $initials .= strtoupper(substr($w, 0, 1));
            if (strlen($initials) >= 4) break;
        }
        $initials = $initials ?: 'A';

        if ($assetId) {
            // include asset id between type and initials
            $candidate = $typeInitial . $typeId . '-' . $assetId . $initials;
        } else {
            $candidate = $typeInitial . $typeId . $initials;
        }
        // ensure reasonable length
        $candidate = substr($candidate, 0, 64);

        $final = $candidate;
        $i = 1;
        while (self::where('code', $final)->exists()) {
            $final = $candidate . '_' . sprintf('%02d', $i);
            $i++;
        }

        return $final;
    }
}

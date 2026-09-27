<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvailabilityOverride extends Model
{
    protected $fillable = [
        'makeup_artist_id',
        'override_date',
        'start_time',
        'end_time',
        'reason',
    ];

    protected $casts = [
        'override_date' => 'date',
    ];

    public function makeupArtist(): BelongsTo
    {
        return $this->belongsTo(MakeupArtist::class);
    }
}
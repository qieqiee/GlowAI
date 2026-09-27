<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Availability extends Model
{
    protected $fillable = [
        'makeup_artist_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_working',
    ];

    protected $casts = [
        'is_working' => 'boolean',
    ];

    public function makeupArtist(): BelongsTo
    {
        return $this->belongsTo(MakeupArtist::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\BlockedDate;

class BlockedDate extends Model
{
    protected $fillable = [
        'makeup_artist_id',
        'blocked_date',
        'reason',
    ];

    protected $casts = [
        'blocked_date' => 'date',
    ];

    public function makeupArtist(): BelongsTo
    {
        return $this->belongsTo(MakeupArtist::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    protected $fillable = [
        'makeup_artist_id',
        'service_name',
        'category',
        'description',
        'price',
        'deposit_amount',
        'duration',
        'service_included',
    ];

    public function makeupArtist(): BelongsTo
    {
        return $this->belongsTo(MakeupArtist::class);
    }
}
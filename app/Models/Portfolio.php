<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Portfolio extends Model
{
    protected $fillable = [
        'makeup_artist_id',
        'look_title',
        'category',
        'picture',
        'description',
        'products_used',
    ];

    public function makeupArtist(): BelongsTo
    {
        return $this->belongsTo(MakeupArtist::class);
    }
}
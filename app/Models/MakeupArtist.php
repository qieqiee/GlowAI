<?php

namespace App\Models;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MakeupArtist extends Model
{
    protected $fillable = [
        'user_id',
        'studio_brand_name',
        'phone',
        'studio_address',
        'willing_to_travel',
        'service_states',
        'service_areas',
        'profile_picture',
        'instagram',
        'tiktok',
        'years_experience',
        'specialized_makeup_look',
        'description',
        'is_published',
    ];

    protected $casts = [
        'service_states' => 'array',
        'service_areas' => 'array',
        'willing_to_travel' => 'boolean',
        'is_published' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function portfolios(): HasMany
    {
        return $this->hasMany(Portfolio::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(Availability::class);
    }

    public function blockedDates(): HasMany
    {
        return $this->hasMany(BlockedDate::class);
    }

    public function availabilityOverrides(): HasMany
    {
        return $this->hasMany(AvailabilityOverride::class);
    }

        public function bookings()
    {
        return $this->hasMany(Booking::class, 'makeup_artist_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $fillable = [
        'customer_id',
        'makeup_artist_id',
        'service_id',
        'booking_date',
        'booking_time',
        'event_type',
        'appointment_location_type',
        'appointment_address',
        'customer_phone',
        'additional_notes',
        'status',
        'service_price',
        'deposit_amount',
        'balance_amount',
        'payment_status',
        'toyyibpay_bill_code',
        'toyyibpay_transaction_id',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'service_price' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function makeupArtist(): BelongsTo
    {
        return $this->belongsTo(MakeupArtist::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

        public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

        public function unreadMessagesFor($userId)
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false);
    }
}
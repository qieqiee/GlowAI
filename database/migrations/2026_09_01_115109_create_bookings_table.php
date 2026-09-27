<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
        
            // Customer
            $table->foreignId('customer_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
        
            // Makeup Artist
            $table->foreignId('makeup_artist_id')
                  ->constrained('makeup_artists')
                  ->cascadeOnDelete();
        
            // Selected Service
            $table->foreignId('service_id')
                  ->constrained('services')
                  ->cascadeOnDelete();
        
            // Appointment
            $table->date('booking_date');
            $table->time('booking_time');
        
            $table->string('event_type');
        
            // Location
            $table->string('appointment_location_type');
            $table->text('appointment_address')->nullable();
        
            // Customer contact
            $table->string('customer_phone');
        
            // Optional notes
            $table->text('additional_notes')->nullable();
        
            // Booking status
            $table->string('status')->default('pending');
        
            // Price snapshot
            $table->decimal('service_price', 10, 2);
            $table->decimal('deposit_amount', 10, 2);
            $table->decimal('balance_amount', 10, 2);
        
            // Payment later
            $table->string('payment_status')->default('unpaid');
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

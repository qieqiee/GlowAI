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
        Schema::create('availabilities', function (Blueprint $table) {
            $table->id();
        
            $table->foreignId('makeup_artist_id')
                  ->constrained('makeup_artists')
                  ->cascadeOnDelete();
        
            $table->string('day_of_week');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->boolean('is_working')->default(true);
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('availabilities');
    }
};

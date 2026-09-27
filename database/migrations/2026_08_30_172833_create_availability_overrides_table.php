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
        Schema::create('availability_overrides', function (Blueprint $table) {
            $table->id();
        
            $table->foreignId('makeup_artist_id')
                  ->constrained('makeup_artists')
                  ->cascadeOnDelete();
        
            $table->date('override_date');
        
            $table->time('start_time');
            $table->time('end_time');
        
            $table->string('reason')->nullable();
        
            $table->timestamps();
        
            $table->unique(['makeup_artist_id', 'override_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('availability_overrides');
    }
};

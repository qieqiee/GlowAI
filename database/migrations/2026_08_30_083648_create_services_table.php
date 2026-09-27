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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
        
            $table->foreignId('makeup_artist_id')
                  ->constrained('makeup_artists')
                  ->cascadeOnDelete();
        
            $table->string('service_name');
            $table->string('category');
            $table->text('description')->nullable();
        
            $table->decimal('price', 10, 2);
            $table->decimal('deposit_amount', 10, 2)->nullable();
        
            $table->integer('duration');
        
            $table->text('service_included')->nullable();
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};

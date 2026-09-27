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
        Schema::create('portfolios', function (Blueprint $table) {
            $table->id();
    
            $table->foreignId('makeup_artist_id')
                  ->constrained('makeup_artists')
                  ->cascadeOnDelete();
    
            $table->string('look_title');
            $table->string('category');
            $table->string('picture');
            $table->text('description');
            $table->text('products_used')->nullable();
    
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portfolios');
    }
};

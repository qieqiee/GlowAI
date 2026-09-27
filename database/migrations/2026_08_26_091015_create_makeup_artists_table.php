<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('makeup_artists', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
            ->constrained('users')
            ->cascadeOnDelete();

        $table->string('studio_brand_name');
        
        $table->text('studio_address');
        
        $table->boolean('willing_to_travel')->default(false);
        $table->string('travel_location')->nullable();
        
        $table->string('profile_picture')->nullable();
        
        $table->string('instagram')->nullable();
        $table->string('tiktok')->nullable();
        
        $table->unsignedInteger('years_experience');
        $table->string('specialized_makeup_look');
        $table->text('description')->nullable();
        
        $table->timestamps();
            
        });
}
    


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('makeup_artists');
    }
};

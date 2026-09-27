<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('makeup_artists', function (Blueprint $table) {
            $table->dropColumn(['state', 'city']);
        });

        Schema::table('makeup_artists', function (Blueprint $table) {
            $table->json('service_states')->nullable()->after('studio_address');
            $table->json('service_areas')->nullable()->after('service_states');
        });
    }

    public function down(): void
    {
        Schema::table('makeup_artists', function (Blueprint $table) {
            $table->dropColumn(['service_states', 'service_areas']);
        });

        Schema::table('makeup_artists', function (Blueprint $table) {
            $table->string('state')->nullable()->after('studio_address');
            $table->string('city')->nullable()->after('state');
        });
    }
};

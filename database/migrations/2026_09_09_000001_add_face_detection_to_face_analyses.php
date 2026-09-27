<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('face_analyses', function (Blueprint $table) {
            $table->boolean('face_detected')->nullable();
            $table->unsignedInteger('face_count')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('face_analyses', function (Blueprint $table) {
            $table->dropColumn(['face_detected', 'face_count']);
        });
    }
};

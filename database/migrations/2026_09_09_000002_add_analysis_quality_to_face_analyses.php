<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('face_analyses', function (Blueprint $table) {
            $table->json('analysis_quality')->nullable();
        });
    }
    public function down(): void {
        Schema::table('face_analyses', function (Blueprint $table) {
            $table->dropColumn('analysis_quality');
        });
    }
};

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
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('toyyibpay_bill_code')->nullable()->after('payment_status');
            $table->string('toyyibpay_transaction_id')->nullable()->after('toyyibpay_bill_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'toyyibpay_bill_code',
                'toyyibpay_transaction_id',
            ]);
        });
    }
};

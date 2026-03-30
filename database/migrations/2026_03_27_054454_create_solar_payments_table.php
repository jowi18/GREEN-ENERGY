<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add PayPal tracking columns to solar_payments
        Schema::table('solar_payments', function (Blueprint $table) {
            $table->string('paypal_order_id')->nullable()->after('or_number');
            $table->string('paypal_transaction_id')->nullable()->after('paypal_order_id');
            $table->string('paypal_payer_id')->nullable()->after('paypal_transaction_id');
            $table->string('paypal_payer_email')->nullable()->after('paypal_payer_id');
            $table->enum('payment_source', ['paypal', 'manual'])->default('manual')->after('payment_method');
        });

        // Add PayPal pending tracking to solar_payment_schedules
        Schema::table('solar_payment_schedules', function (Blueprint $table) {
            $table->string('pending_paypal_order_id')->nullable()->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('solar_payments', function (Blueprint $table) {
            $table->dropColumn([
                'paypal_order_id', 'paypal_transaction_id',
                'paypal_payer_id', 'paypal_payer_email', 'payment_source',
            ]);
        });
        Schema::table('solar_payment_schedules', function (Blueprint $table) {
            $table->dropColumn('pending_paypal_order_id');
        });
    }
};

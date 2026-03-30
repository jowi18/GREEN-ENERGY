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
        Schema::create('subscriptions', function (Blueprint $table) {
           $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->constrained('subscription_plans');

            // PayPal references
            $table->string('paypal_subscription_id')->nullable()->unique(); // PAYPAL sub ID
            $table->string('paypal_order_id')->nullable();                  // one-time order ID
            $table->string('paypal_payer_id')->nullable();

            $table->enum('status', [
                'pending',      // payment initiated
                'active',       // payment confirmed, vendor is active
                'cancelled',    // vendor cancelled
                'expired',      // duration elapsed
                'failed',       // payment failed
                'suspended',    // admin suspended
            ])->default('pending');

            $table->decimal('amount_paid', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            // Renewal tracking
            $table->boolean('auto_renew')->default(true);
            $table->timestamp('last_renewed_at')->nullable();
            $table->timestamp('next_renewal_at')->nullable();

            $table->json('paypal_response')->nullable(); // raw PayPal webhook/callback payload

            $table->timestamps();

            $table->index(['vendor_id', 'status']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};

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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();  // e.g. ORD-20240115-0001
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();

            $table->enum('status', [
                'pending',      // placed, awaiting payment
                'paid',         // payment confirmed
                'processing',   // vendor is preparing
                'shipped',      // dispatched
                'delivered',    // confirmed received
                'completed',    // order closed
                'cancelled',    // cancelled by customer or vendor
                'refunded',     // refund issued
            ])->default('pending');

            // Pricing
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('shipping_fee', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->string('currency', 3)->default('PHP');

            // Shipping address (snapshot at time of order)
            $table->string('shipping_address_line1');
            $table->string('shipping_address_line2')->nullable();
            $table->string('shipping_city');
            $table->string('shipping_province');
            $table->string('shipping_postal_code');
            $table->string('shipping_country')->default('Philippines');

            // Payment
            $table->enum('payment_method', ['paypal', 'cash', 'gcash', 'bank_transfer'])->nullable();
            $table->enum('payment_status', ['unpaid', 'paid', 'partially_refunded', 'refunded'])->default('unpaid');
            $table->string('paypal_order_id')->nullable();
            $table->string('paypal_payer_id')->nullable();
            $table->json('paypal_response')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->text('customer_notes')->nullable();
            $table->text('vendor_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['customer_id', 'status']);
            $table->index(['vendor_id', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

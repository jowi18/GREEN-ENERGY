<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique(); // e.g. POS-20240115-0001
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();

            // Walk-in customer may or may not be registered
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('walk_in_name')->nullable();  // for unregistered walk-ins

            // Cashier / employee who processed the transaction
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('status', [
                'open',       // cart in progress
                'completed',  // payment received
                'voided',     // cancelled/voided
                'refunded',   // full refund
            ])->default('open');

            // Pricing
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('currency', 3)->default('PHP');

            // Payment
            $table->enum('payment_method', ['cash', 'paypal', 'gcash', 'card', 'other'])->nullable();
            $table->decimal('amount_tendered', 12, 2)->nullable();  // cash given by customer
            $table->decimal('change_given', 12, 2)->nullable();     // cash change
            $table->string('paypal_order_id')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->text('notes')->nullable();
            $table->string('receipt_number')->nullable()->unique();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['vendor_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_transactions');
    }
};

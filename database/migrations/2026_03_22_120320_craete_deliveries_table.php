<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_number')->unique()->nullable();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();

            // Assigned delivery personnel (employee with Technician/Staff role)
            $table->foreignId('assigned_to')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();

            $table->enum('status', [
                'pending',          // order paid, awaiting dispatch
                'preparing',        // vendor preparing the package
                'out_for_delivery', // in transit
                'delivered',        // customer confirmed receipt
                'failed',           // delivery attempt failed
                'returned',         // returned to vendor
            ])->default('pending');

            // Delivery address (copied from order, can be overridden)
            $table->string('delivery_address_line1');
            $table->string('delivery_address_line2')->nullable();
            $table->string('delivery_city');
            $table->string('delivery_province');
            $table->string('delivery_postal_code');
            $table->decimal('delivery_latitude', 10, 8)->nullable();
            $table->decimal('delivery_longitude', 11, 8)->nullable();

            $table->text('delivery_notes')->nullable();
            $table->string('proof_of_delivery')->nullable(); // photo path
            $table->string('recipient_name')->nullable();    // who signed/received

            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            $table->timestamps();

            $table->index(['vendor_id', 'status']);
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};

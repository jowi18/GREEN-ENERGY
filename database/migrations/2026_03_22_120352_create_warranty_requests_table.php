<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warranty_requests', function (Blueprint $table) {

            $table->id();

            // Example: WR-20240115-0001
            $table->string('request_number')->unique();

            // Relationships
            $table->foreignId('customer_id')
                  ->constrained('customers')
                  ->cascadeOnDelete();

            $table->foreignId('vendor_id')
                  ->constrained('vendors')
                  ->cascadeOnDelete();

            $table->foreignId('order_item_id')
                  ->constrained('order_items');

            $table->foreignId('product_id')
                  ->constrained('products');

            // Request Type
            $table->enum('request_type', [
                'warranty_repair',
                'warranty_replacement',
                'warranty_inspection',
            ]);

            // Workflow Status
            $table->enum('status', [
                'submitted',
                'under_review',
                'approved',
                'rejected',
                'scheduled',
                'in_progress',
                'completed',
                'closed',
            ])->default('submitted');

            // Issue Details
            $table->text('issue_description');
            $table->string('serial_number', 100)->nullable();

            $table->json('issue_images')->nullable();
            $table->json('product_images')->nullable();
            $table->json('proof_of_purchase_images')->nullable();

            $table->string('purchase_receipt_path', 500)->nullable();

            // Customer Notes
            $table->text('customer_notes')->nullable();

            // Technician Assignment
            $table->foreignId('assigned_technician_id')
                  ->nullable()
                  ->constrained('employees')
                  ->nullOnDelete();

            $table->timestamp('scheduled_at')->nullable();
            $table->date('service_date')->nullable();
            $table->string('service_time_slot', 30)->nullable();

            // Technician Work
            $table->timestamp('completed_at')->nullable();
            $table->text('technician_notes')->nullable();

            // Resolution Details
            $table->text('resolution_notes')->nullable();

            $table->enum('resolution_type', [
                'repaired',
                'replaced',
                'refunded',
                'no_fault_found',
            ])->nullable();

            $table->json('completion_images')->nullable();

            // Rejection Info
            $table->text('rejection_reason')->nullable();

            // Warranty Validation Snapshot
            $table->date('warranty_expires_at')->nullable();

            $table->boolean('is_within_warranty')
                  ->default(true);

            // Approval Tracking
            $table->foreignId('approved_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();

            // Closing Tracking
            $table->foreignId('closed_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('closed_at')->nullable();

            // Service History Log (JSON timeline)
            $table->json('service_history')->nullable();

            $table->timestamps();

            // Indexes for Performance
            $table->index(['vendor_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index('assigned_technician_id');
            $table->index('request_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warranty_requests');
    }
};

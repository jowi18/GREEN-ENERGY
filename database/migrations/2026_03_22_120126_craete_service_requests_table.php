<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Service Requests ──────────────────────────────────────────────
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number', 30)->unique();   // SR-20240318-0001

            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();

            // Service details
            $table->string('service_type', 50);   // installation, maintenance, repair…
            $table->string('service_title', 200);
            $table->text('description');

            // Scheduling
            $table->date('preferred_date')->nullable();
            $table->string('preferred_time', 20)->nullable();   // morning, afternoon, etc.
            $table->date('scheduled_date')->nullable();
            $table->string('scheduled_time', 20)->nullable();
            $table->string('assigned_to', 100)->nullable();     // technician name

            // Location (where service will be performed)
            $table->string('service_address', 255);
            $table->string('service_city', 100);
            $table->string('service_province', 100);
            $table->string('service_postal', 20)->nullable();
            $table->decimal('service_latitude',  10, 7)->nullable();
            $table->decimal('service_longitude', 10, 7)->nullable();

            // Status pipeline
            $table->enum('status', [
                'submitted',
                'reviewing',
                'quoted',
                'quote_accepted',
                'quote_rejected',
                'scheduled',
                'in_progress',
                'completed',
                'rejected',
                'cancelled',
            ])->default('submitted');

            // Quoting
            $table->decimal('quoted_amount', 12, 2)->nullable();
            $table->text('quote_details')->nullable();
            $table->date('quote_valid_until')->nullable();
            $table->timestamp('quoted_at')->nullable();

            // Notes
            $table->text('customer_notes')->nullable();
            $table->text('vendor_notes')->nullable();
            $table->text('rejection_reason')->nullable();

            // Timestamps
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['vendor_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index('scheduled_date');
        });

        // ── Proof of Completion Images ─────────────────────────────────────
        Schema::create('service_request_proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')
                  ->constrained('service_requests')
                  ->cascadeOnDelete();

            $table->string('file_path', 500);
            $table->string('file_original_name', 255)->nullable();
            $table->string('file_mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();   // bytes
            $table->string('caption', 300)->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_request_proofs');
        Schema::dropIfExists('service_requests');
    }
};

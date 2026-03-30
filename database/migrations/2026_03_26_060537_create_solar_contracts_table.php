<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Solar Contracts ───────────────────────────────────────────────
        Schema::create('solar_contracts', function (Blueprint $table) {
            $table->id();

            $table->string('contract_number')->unique(); // CN-20240115-0001

            $table->foreignId('solar_project_id')
                  ->constrained('solar_projects')->cascadeOnDelete();
            $table->foreignId('solar_quotation_id')
                  ->nullable()->constrained('solar_quotations')->nullOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            $table->unsignedTinyInteger('version')->default(1);

            $table->enum('status', [
                'draft',            // vendor is building it
                'sent',             // sent to customer for review
                'adjustment_requested', // customer requested changes
                'approved',         // customer approved
                'signed',           // both parties signed
                'cancelled',
            ])->default('draft');

            // ── Fixed vendor sections ─────────────────────────────────────
            $table->text('scope_of_work');           // what will be installed
            $table->text('warranty_terms');          // warranty coverage
            $table->text('penalties_cancellation');  // penalty clauses
            $table->text('custom_clauses')->nullable(); // any extra vendor terms

            // ── Payment configuration ─────────────────────────────────────
            $table->enum('payment_mode', ['full', 'installment', 'progress_based']);
            $table->decimal('contract_amount', 12, 2); // total contract value
            $table->date('payment_start_date')->nullable();

            // For installment: number of payments
            $table->unsignedTinyInteger('installment_count')->nullable();
            $table->enum('installment_frequency', ['weekly','monthly','quarterly'])->nullable();

            // ── Customer interaction ──────────────────────────────────────
            $table->text('customer_adjustment_notes')->nullable();
            $table->timestamp('customer_viewed_at')->nullable();
            $table->timestamp('customer_approved_at')->nullable();
            $table->string('customer_signature')->nullable(); // base64 or path

            // ── Vendor signature / finalization ───────────────────────────
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('signed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['solar_project_id', 'status']);
            $table->index('contract_number');
        });

        // ── Contract Adjustment Requests ──────────────────────────────────
        Schema::create('solar_contract_adjustments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('solar_contract_id')
                  ->constrained('solar_contracts')->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();

            $table->enum('status', ['pending', 'addressed', 'dismissed'])->default('pending');
            $table->text('section');     // which section needs adjustment
            $table->text('notes');       // customer's requested change
            $table->text('vendor_response')->nullable();
            $table->timestamp('vendor_responded_at')->nullable();

            $table->timestamps();
        });

        // ── Payment Schedules ─────────────────────────────────────────────
        Schema::create('solar_payment_schedules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('solar_contract_id')
                  ->constrained('solar_contracts')->cascadeOnDelete();
            $table->foreignId('solar_project_id')
                  ->constrained('solar_projects')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();

            $table->string('reference_number')->unique(); // PAY-20240115-0001

            $table->unsignedTinyInteger('installment_number'); // 1, 2, 3…
            $table->string('label');          // e.g. "Down Payment", "2nd Payment", "Upon Activation"

            // For progress-based: which project milestone triggers this payment
            $table->string('milestone_status')->nullable(); // e.g. 'contract_signed'

            $table->decimal('amount', 12, 2);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->date('due_date')->nullable();

            $table->enum('status', ['pending', 'partial', 'paid', 'overdue', 'waived'])->default('pending');

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['solar_contract_id', 'status']);
        });

        // ── Payment Records (actual payments logged) ───────────────────────
        Schema::create('solar_payment_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('solar_payment_schedule_id')
                  ->constrained('solar_payment_schedules')->cascadeOnDelete();
            $table->foreignId('solar_contract_id')
                  ->constrained('solar_contracts')->cascadeOnDelete();
            $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();

            $table->string('receipt_number')->unique(); // REC-20240115-0001
            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', ['cash','bank_transfer','gcash','maya','paypal','check','other']);
            $table->string('reference_code')->nullable(); // bank ref, GCash ref, etc.
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->string('proof_path')->nullable(); // uploaded receipt image

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solar_payment_records');
        Schema::dropIfExists('solar_payment_schedules');
        Schema::dropIfExists('solar_contract_adjustments');
        Schema::dropIfExists('solar_contracts');
    }
};

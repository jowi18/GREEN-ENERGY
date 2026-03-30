<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Solar Projects ────────────────────────────────────────────────
        Schema::create('solar_projects', function (Blueprint $table) {
            $table->id();

            // Auto-generated: SP-20240115-0001
            $table->string('project_number')->unique();

            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();

            // Assigned engineer/technician (employee)
            $table->foreignId('assigned_engineer_id')
                  ->nullable()
                  ->constrained('employees')
                  ->nullOnDelete();

            // ── Inquiry details ───────────────────────────────────────────
            $table->enum('property_type', [
                'residential', 'commercial', 'industrial', 'agricultural',
            ]);

            $table->enum('system_type', [
                'grid_tie',       // connects to utility grid
                'off_grid',       // standalone battery system
                'hybrid',         // grid + battery backup
            ])->nullable();

            $table->decimal('estimated_monthly_bill', 10, 2)->nullable(); // PHP
            $table->decimal('desired_system_size_kw', 8, 2)->nullable();  // kW
            $table->integer('roof_area_sqm')->nullable();

            $table->enum('roof_type', [
                'concrete', 'metal', 'asphalt_shingle', 'clay_tile', 'other',
            ])->nullable();

            // Installation address (may differ from customer's home address)
            $table->text('installation_address');
            $table->string('installation_city')->nullable();
            $table->string('installation_province')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->text('customer_notes')->nullable();

            // ── Workflow status ───────────────────────────────────────────
            $table->enum('status', [
                'inquiry_submitted',
                'consultation_completed',
                'site_survey_scheduled',
                'site_survey_completed',
                'system_designed',
                'quotation_generated',
                'proposal_approved',
                'proposal_rejected',
                'contract_signed',
                'permit_processing',
                'equipment_prepared',
                'installation_scheduled',
                'installation_completed',
                'testing_commissioning',
                'utility_inspection',
                'system_activated',
                'project_turnover',
                'warranty_support_active',
                'cancelled',
            ])->default('inquiry_submitted');

            // ── Scheduling ────────────────────────────────────────────────
            $table->date('survey_date')->nullable();
            $table->string('survey_time_slot', 30)->nullable();
            $table->date('installation_date')->nullable();
            $table->string('installation_time_slot', 30)->nullable();

            // ── Completion ────────────────────────────────────────────────
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('turned_over_at')->nullable();
            $table->text('completion_notes')->nullable();
            $table->json('completion_images')->nullable();

            // ── Permit & utility ──────────────────────────────────────────
            $table->string('permit_number')->nullable();
            $table->string('utility_application_number')->nullable();
            $table->date('net_metering_approval_date')->nullable();

            // ── Internal tracking ─────────────────────────────────────────
            $table->json('status_history')->nullable(); // timestamped log
            $table->text('vendor_notes')->nullable();

            // ── Approval ──────────────────────────────────────────────────
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['vendor_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index('project_number');
        });

        // ── Solar Quotations ──────────────────────────────────────────────
        Schema::create('solar_quotations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('solar_project_id')
                  ->constrained('solar_projects')
                  ->cascadeOnDelete();

            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            $table->string('quotation_number')->unique(); // QT-20240115-0001

            $table->enum('status', [
                'draft', 'sent', 'approved', 'rejected', 'expired', 'superseded',
            ])->default('draft');

            // Financial summary (computed from items)
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);

            $table->date('valid_until')->nullable();

            // Customer response
            $table->text('customer_rejection_reason')->nullable();
            $table->timestamp('customer_responded_at')->nullable();

            $table->text('notes')->nullable();         // vendor notes on quotation
            $table->text('terms_and_conditions')->nullable();

            $table->json('attachments')->nullable();   // PDF path etc.

            $table->timestamps();
            $table->softDeletes();

            $table->index(['solar_project_id', 'status']);
        });

        // ── Solar Quotation Line Items ─────────────────────────────────────
        Schema::create('solar_quotation_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('solar_quotation_id')
                  ->constrained('solar_quotations')
                  ->cascadeOnDelete();

            $table->enum('item_type', [
                'equipment',  // solar panel, inverter, battery, etc.
                'labor',      // installation, wiring, mounting
                'permit',     // permit fees
                'other',
            ]);

            $table->string('description');             // "330W Monocrystalline Panel"
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('unit')->default('pc');     // pc, set, lot, hr
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2)->default(0); // qty * unit_price
            $table->integer('sort_order')->default(0);
            $table->text('notes')->nullable();

            $table->timestamps();
        });

        // ── Solar Project Documents ───────────────────────────────────────
        Schema::create('solar_project_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('solar_project_id')
                  ->constrained('solar_projects')
                  ->cascadeOnDelete();

            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();

            $table->enum('document_type', [
                // Customer uploads
                'site_photo',
                'electric_bill',
                'government_id',
                'lot_plan',
                'barangay_clearance',
                // Vendor uploads
                'site_survey_report',
                'system_design',
                'contract',
                'permit',
                'completion_photo',
                'commissioning_report',
                'net_metering_certificate',
                'other',
            ]);

            $table->enum('uploaded_by_role', ['customer', 'vendor', 'admin']);

            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable(); // bytes
            $table->text('description')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['solar_project_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solar_project_documents');
        Schema::dropIfExists('solar_quotation_items');
        Schema::dropIfExists('solar_quotations');
        Schema::dropIfExists('solar_projects');
    }
};

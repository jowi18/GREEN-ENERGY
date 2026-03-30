<?php

// ═══════════════════════════════════════════════════════════════════════════
// database/migrations/xxxx_create_vendor_suppliers_table.php
// ═══════════════════════════════════════════════════════════════════════════

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_suppliers', function (Blueprint $table) {
            $table->id();

            // Owner
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();

            // Optional link to a portal supplier account
            // NULL = manually managed, not yet on the portal
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();

            // ── Core contact info ─────────────────────────────────────────
            $table->string('company_name');
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address_line1')->nullable();
            $table->string('city')->nullable();
            $table->string('province_state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('Philippines');

            // ── Business relationship ─────────────────────────────────────
            $table->string('account_number')->nullable()
                ->comment('Vendor\'s account number with this supplier');
            $table->enum('payment_terms', ['cod', 'net7', 'net15', 'net30', 'net60', 'custom'])
                ->default('cod');
            $table->string('payment_terms_custom')->nullable()
                ->comment('Free-text when payment_terms = custom');
            $table->decimal('credit_limit', 14, 2)->nullable();

            // ── Flags & metadata ──────────────────────────────────────────
            $table->boolean('is_preferred')->default(false);
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('active');
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // A vendor cannot add the same company twice (same email per vendor)
            $table->unique(['vendor_id', 'email'], 'vendor_supplier_email_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_suppliers');
    }
};

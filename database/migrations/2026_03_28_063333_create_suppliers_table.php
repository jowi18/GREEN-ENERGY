<?php

// ═══════════════════════════════════════════════════════════════════════════
// Run these in order:
//   php artisan migrate
// ═══════════════════════════════════════════════════════════════════════════

// ── 1. create_suppliers_table ─────────────────────────────────────────────
// database/migrations/xxxx_create_suppliers_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('company_name');
            $table->string('contact_person');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('province_state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('Philippines');
            $table->string('business_permit')->nullable();   // file path
            $table->string('logo')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'active', 'suspended'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });

        // ── 2. supplier_products ─────────────────────────────────────────
        Schema::create('supplier_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->decimal('unit_price', 12, 2);
            $table->string('currency', 10)->default('PHP');
            $table->string('unit_of_measure', 50)->default('piece');
            $table->integer('minimum_order_qty')->default(1);
            $table->integer('stock_available')->default(0);
            $table->integer('lead_time_days')->default(7)->comment('Estimated days to deliver');
            $table->string('product_type')->default('physical');   // physical | service
            $table->decimal('weight_kg', 8, 3)->nullable();
            $table->integer('warranty_months')->nullable();
            $table->string('spec_sheet')->nullable();              // file path
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        // ── 3. supplier_product_images ───────────────────────────────────
        Schema::create('supplier_product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_product_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->string('alt_text')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // ── 4. purchase_requests ─────────────────────────────────────────
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->string('pr_number')->unique();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users');   // vendor employee/user
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->enum('status', [
                'draft',
                'submitted',
                'approved',
                'rejected',
                'converted',   // converted to PO
                'cancelled',
            ])->default('draft');
            $table->date('requested_delivery_date')->nullable();
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // ── 5. purchase_request_items ────────────────────────────────────
        Schema::create('purchase_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_product_id')->constrained();
            $table->string('product_name');          // snapshot at time of request
            $table->string('sku')->nullable();
            $table->decimal('unit_price', 12, 2);
            $table->integer('quantity_requested');
            $table->string('unit_of_measure', 50)->default('piece');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ── 6. purchase_orders ───────────────────────────────────────────
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->foreignId('purchase_request_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->enum('status', [
                'draft',
                'submitted',       // sent to supplier
                'approved',        // supplier approved
                'rejected',        // supplier rejected
                'processing',      // supplier preparing
                'shipped',         // in transit
                'delivered',       // supplier marked delivered
                'confirmed',       // vendor confirmed receipt
                'cancelled',
            ])->default('draft');
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('shipping_cost', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->string('currency', 10)->default('PHP');
            $table->date('expected_delivery_date')->nullable();
            $table->date('actual_delivery_date')->nullable();
            $table->text('delivery_address')->nullable();
            $table->text('supplier_notes')->nullable();
            $table->text('vendor_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('courier')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('supplier_responded_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // ── 7. purchase_order_items ──────────────────────────────────────
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_product_id')->constrained();
            $table->string('product_name');
            $table->string('sku')->nullable();
            $table->decimal('unit_price', 12, 2);
            $table->integer('quantity_ordered');
            $table->integer('quantity_received')->default(0);
            $table->string('unit_of_measure', 50)->default('piece');
            $table->decimal('line_total', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('purchase_request_items');
        Schema::dropIfExists('purchase_requests');
        Schema::dropIfExists('supplier_product_images');
        Schema::dropIfExists('supplier_products');
        Schema::dropIfExists('suppliers');
    }
};

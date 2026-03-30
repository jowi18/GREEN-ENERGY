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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained('inventories')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();

            $table->enum('movement_type', [
                'purchase',        // stock received from supplier
                'sale',            // sold via online order
                'pos_sale',        // sold via POS walk-in
                'adjustment_add',  // manual stock increase
                'adjustment_sub',  // manual stock decrease
                'return',          // customer return
                'damage',          // damaged/written off
                'transfer_in',     // transferred from another location
                'transfer_out',    // transferred to another location
                'opening_stock',   // initial stock entry
            ]);

            $table->integer('quantity_change');   // positive = in, negative = out
            $table->unsignedInteger('quantity_before');
            $table->unsignedInteger('quantity_after');

            // Optional reference to source document
            $table->string('reference_type')->nullable();  // order, pos_transaction, etc.
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->text('notes')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['inventory_id', 'movement_type']);
            $table->index(['reference_type', 'reference_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};

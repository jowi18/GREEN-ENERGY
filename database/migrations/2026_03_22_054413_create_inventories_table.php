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
        Schema::create('inventories', function (Blueprint $table) {
             $table->id();
            $table->foreignId('product_id')->unique()->constrained('products')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();

            $table->unsignedInteger('quantity_on_hand')->default(0);
            $table->unsignedInteger('quantity_reserved')->default(0);  // pending orders
            $table->unsignedInteger('quantity_available')->virtualAs('quantity_on_hand - quantity_reserved');

            $table->unsignedInteger('reorder_point')->default(5);     // low stock alert threshold
            $table->unsignedInteger('reorder_quantity')->default(10); // suggested reorder qty
            $table->unsignedInteger('max_stock_level')->nullable();

            $table->string('storage_location')->nullable();           // e.g. "Shelf A3"
            $table->string('unit_of_measure')->default('piece');      // piece, box, pallet

            $table->timestamp('last_stock_update')->nullable();
            $table->timestamps();

            $table->index(['vendor_id', 'quantity_on_hand']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};

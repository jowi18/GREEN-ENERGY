<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_services', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vendor_id')
                  ->constrained('vendors')
                  ->cascadeOnDelete();

            // Basic info
            $table->string('name', 200);
            $table->string('slug', 220)->nullable();
            $table->string('service_type', 60);      // installation, maintenance, repair…
            $table->text('short_description')->nullable();
            $table->text('description')->nullable();

            // Pricing
            $table->enum('pricing_type', ['fixed', 'starting_from', 'per_hour', 'custom_quote'])
                  ->default('starting_from');
            $table->decimal('price', 12, 2)->nullable();       // base / starting price
            $table->decimal('price_max', 12, 2)->nullable();   // for range pricing
            $table->string('currency', 10)->default('PHP');

            // Duration estimate
            $table->unsignedSmallInteger('duration_hours_min')->nullable();
            $table->unsignedSmallInteger('duration_hours_max')->nullable();

            // Availability & visibility
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);

            // Service area / coverage notes
            $table->text('coverage_notes')->nullable();   // e.g. "Metro Manila only"

            // What's included / requirements
            $table->text('inclusions')->nullable();    // bullet points (stored as text)
            $table->text('requirements')->nullable();  // what customer must provide

            // Ordering
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['vendor_id', 'is_active']);
            $table->index('service_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_services');
    }
};

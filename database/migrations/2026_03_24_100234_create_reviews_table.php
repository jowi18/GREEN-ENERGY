<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();

            // Polymorphic target: vendor, product, service_request
            $table->string('reviewable_type');
            $table->unsignedBigInteger('reviewable_id');

            $table->unsignedTinyInteger('rating');       // 1–5
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->json('predefined_tags')->nullable(); // moved here — no ->after() needed
            $table->json('images')->nullable();

            // Purchase proof
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();

            // Service link
            $table->foreignId('service_request_id')->nullable()->constrained('service_requests')->nullOnDelete();

            // Review classification
            $table->enum('review_type', ['order', 'service', 'vendor'])->default('order');

            // Vendor response
            $table->text('vendor_reply')->nullable();
            $table->timestamp('vendor_replied_at')->nullable();

            $table->boolean('is_verified_purchase')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_flagged')->default(false);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['reviewable_type', 'reviewable_id']);
            $table->index(['vendor_id', 'rating']);
            $table->unique(['customer_id', 'reviewable_type', 'reviewable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};

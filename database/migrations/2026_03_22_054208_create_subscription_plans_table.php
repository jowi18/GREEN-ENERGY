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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');               // e.g. "Monthly", "Annual"
            $table->string('slug')->unique();     // e.g. "monthly", "annual"
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'annual']);
            $table->unsignedInteger('duration_days'); // 30, 90, 365

            // PayPal plan ID (created via PayPal Subscriptions API)
            $table->string('paypal_plan_id')->nullable();

            // Feature limits (null = unlimited)
            $table->unsignedInteger('max_products')->nullable();
            $table->unsignedInteger('max_employees')->nullable();

            // Display
            $table->json('features')->nullable();     // list of bullet points for landing page
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};

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
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Owner personal info
            $table->string('owner_first_name');
            $table->string('owner_last_name');
            $table->string('owner_phone');
            $table->string('avatar')->nullable();

            // Business info
            $table->string('business_name');
            $table->enum('business_type', [
                'sole_proprietorship',
                'partnership',
                'corporation',
                'cooperative',
                'sme',
                'other',
            ]);
            $table->string('business_registration_number')->unique();
            $table->string('business_phone');
            $table->string('business_email')->nullable();
            $table->string('business_website')->nullable();

            // Address
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('city');
            $table->string('province_state');
            $table->string('postal_code');
            $table->string('country')->default('Philippines');

            // Geolocation (for Leaflet.js nearby search)
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Branding
            $table->string('shop_logo')->nullable();
            $table->string('shop_banner')->nullable();
            $table->text('shop_description')->nullable();

            // Vendor status pipeline
            // pending → under_review → approved → subscription_required → active → suspended
            $table->enum('status', [
                'pending',
                'under_review',
                'approved',
                'subscription_required',
                'active',
                'suspended',
                'rejected',
            ])->default('pending');

            $table->text('admin_notes')->nullable();       // internal notes by admin
            $table->text('rejection_reason')->nullable();  // shown to vendor on rejection
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('suspended_at')->nullable();

            // Rating cache (updated via observer when review is submitted)
            $table->decimal('average_rating', 3, 2)->default(0.00);
            $table->unsignedInteger('total_reviews')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['latitude', 'longitude']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};

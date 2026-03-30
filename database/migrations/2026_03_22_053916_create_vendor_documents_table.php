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
        Schema::create('vendor_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();

            $table->enum('document_type', [
                'business_permit',
                'government_id',
                'proof_of_address',
                'sme_certificate',
                'dti_registration',
                'sec_registration',
                'bir_registration',
                'other',
            ]);

            $table->string('document_label');         // human-readable name
            $table->string('file_path');              // storage path
            $table->string('file_original_name');     // original upload filename
            $table->string('file_mime_type');
            $table->unsignedBigInteger('file_size');  // bytes

            $table->enum('review_status', [
                'pending',
                'accepted',
                'rejected',
            ])->default('pending');

            $table->text('reviewer_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            $table->index(['vendor_id', 'document_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_documents');
    }
};

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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();

            // vendor_id = null means system/global role (owner)
            // vendor_id = set means vendor-scoped custom role
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->cascadeOnDelete();

            $table->string('name');                   // e.g. "HR Officer"
            $table->string('slug');                   // e.g. "hr_officer"
            $table->text('description')->nullable();

            // System roles cannot be deleted or renamed
            $table->boolean('is_system')->default(false);

            $table->timestamps();

            $table->unique(['vendor_id', 'slug']);
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_request_assignees', function (Blueprint $table) {
            $table->id();

            $table->foreignId('service_request_id')
                  ->constrained('service_requests')
                  ->cascadeOnDelete();

            $table->foreignId('employee_id')
                  ->constrained('employees')
                  ->cascadeOnDelete();

            $table->string('role', 100)->nullable();  // e.g. Lead Technician, Assistant
            $table->timestamp('assigned_at')->nullable();
            $table->foreignId('assigned_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();

            $table->unique(['service_request_id', 'employee_id']);
            $table->index('service_request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_request_assignees');
    }
};

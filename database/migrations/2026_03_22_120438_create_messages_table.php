<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_room_id')->constrained('chat_rooms')->cascadeOnDelete();

            // Sender is polymorphic: vendor, customer, or employee
            $table->foreignId('sender_user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('sender_type', ['vendor', 'customer', 'employee']);

            $table->enum('message_type', [
                'text',
                'image',
                'file',
                'order_link',    // auto-message linking an order
                'service_link',  // auto-message linking a service request
                'system',        // system-generated (e.g. "Order #123 was placed")
            ])->default('text');

            $table->text('body')->nullable();           // text content
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->string('attachment_mime')->nullable();

            // Read receipts
            $table->timestamp('read_at')->nullable();

            // Soft delete only for sender (message shows "deleted" placeholder to other party)
            $table->timestamp('deleted_by_sender_at')->nullable();

            $table->timestamps();

            $table->index(['chat_room_id', 'created_at']);
            $table->index('sender_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};

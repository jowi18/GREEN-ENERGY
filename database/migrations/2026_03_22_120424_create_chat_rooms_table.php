<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Chat Rooms (one per customer–vendor pair) ────────────────────
        Schema::create('chat_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_key', 100)->unique(); // "vendor_{vid}_customer_{cid}"
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->timestamp('last_message_at')->nullable();
            $table->text('last_message_preview')->nullable();

            // Unread counters (denormalized for performance)
            $table->unsignedInteger('vendor_unread')->default(0);
            $table->unsignedInteger('customer_unread')->default(0);

            // Online / typing indicators
            $table->timestamp('vendor_last_seen')->nullable();
            $table->timestamp('customer_last_seen')->nullable();
            $table->timestamp('vendor_typing_at')->nullable();
            $table->timestamp('customer_typing_at')->nullable();

            $table->boolean('is_archived')->default(false);
            $table->timestamps();

            $table->index(['vendor_id', 'last_message_at']);
            $table->index(['customer_id', 'last_message_at']);
        });

        // ── Chat Messages ────────────────────────────────────────────────
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->enum('sender_type', ['vendor', 'customer', 'system'])->default('customer');

            $table->text('body')->nullable();                   // text content
            $table->enum('message_type', [
                'text', 'image', 'file', 'auto_reply', 'system'
            ])->default('text');

            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->boolean('is_auto_reply')->default(false);  // sent by auto-reply engine
            $table->string('auto_reply_trigger', 100)->nullable(); // which predefined Q triggered it

            $table->timestamps();
            $table->softDeletes();

            $table->index(['chat_room_id', 'created_at']);
            $table->index(['chat_room_id', 'is_read']);
        });

        // ── Chat Attachments ────────────────────────────────────────────
        Schema::create('chat_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_message_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chat_room_id')->constrained()->cascadeOnDelete();
            $table->string('file_path', 500);
            $table->string('file_name', 255);
            $table->string('file_mime', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->enum('attachment_type', ['image', 'document', 'other'])->default('other');
            $table->timestamps();
        });

        // ── Predefined Replies (per vendor) ─────────────────────────────
        Schema::create('chat_predefined_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->string('trigger_key', 100);      // e.g. "services", "pricing", "schedule"
            $table->string('question', 300);          // shown to customer as button label
            $table->text('reply_template')->nullable();// static reply (if null → auto-generated)
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['vendor_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_predefined_replies');
        Schema::dropIfExists('chat_attachments');
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_rooms');
    }
};

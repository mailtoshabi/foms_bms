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
        Schema::create('staff_message_replies', function (Blueprint $table) {
            $table->id();

            $table->foreignId('staff_message_id')
                ->constrained('staff_messages')
                ->cascadeOnDelete();

            $table->enum('sender_type',['admin','staff']);
            $table->unsignedBigInteger('sender_id');

            $table->text('message');

            $table->boolean('is_read')->default(false);

            $table->timestamps();

            $table->index(['sender_type','sender_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_message_replies');
    }
};

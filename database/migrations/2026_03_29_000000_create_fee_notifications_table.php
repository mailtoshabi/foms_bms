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
        Schema::create('fee_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['due', 'overdue']);
            $table->string('recipient_phone')->nullable(); // Contact number
            $table->text('message')->nullable();
            $table->enum('status', ['sent', 'failed'])->default('sent');
            $table->text('response')->nullable(); // API response if any
            $table->timestamps();

            $table->index(['fee_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_notifications');
    }
};

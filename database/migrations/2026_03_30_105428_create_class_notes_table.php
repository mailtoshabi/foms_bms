<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('class_notes', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_room_id')->constrained()->cascadeOnDelete();

            // Note info
            $table->string('title');
            $table->longText('content')->nullable(); // optional text note

            // Visibility
            $table->enum('visibility', ['public', 'private'])->default('public');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_notes');
    }
};

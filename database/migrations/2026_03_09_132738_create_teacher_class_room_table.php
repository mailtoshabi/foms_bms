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
        Schema::create('teacher_class_room', function (Blueprint $table) {
            $table->id();

            $table->foreignId('teacher_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('class_room_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('hourly_wage',10,2)->nullable();

            $table->timestamp('assigned_at')->nullable();

            $table->timestamps();

            $table->unique(['teacher_id', 'class_room_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_class_room');
    }
};

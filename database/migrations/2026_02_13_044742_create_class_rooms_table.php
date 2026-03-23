<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_rooms', function (Blueprint $table) {

            $table->id();

            $table->foreignId('course_id')
                ->constrained('courses')
                ->cascadeOnDelete();

            $table->foreignId('class_type_id')
                ->constrained('class_types')
                ->cascadeOnDelete();

            $table->string('name');
            $table->decimal('admission_fee',10,2)->default(0);
            $table->decimal('monthly_fee',10,2)->default(0);


            $table->unsignedInteger('classes_per_week')->default(0);
            $table->string('time_slot')->nullable(); // Example: "10:00 AM - 11:00 AM"
            $table->unsignedInteger('slot_duration')->nullable();
            $table->date('starting_date')->nullable();
            $table->json('selected_days')->nullable(); // Example: ["Monday","Wednesday","Friday"]
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_rooms');
    }
};

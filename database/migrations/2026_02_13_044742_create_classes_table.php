<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {

            $table->id();

            $table->foreignId('course_id')
                ->constrained('courses')
                ->cascadeOnDelete();

            $table->foreignId('class_type_id')
                ->constrained('class_types')
                ->cascadeOnDelete();

            $table->string('name');
            $table->unsignedInteger('slots_per_week')->default(0);
            $table->string('days')->nullable();
            $table->time('slot_time')->nullable();
            $table->unsignedInteger('slot_duration')->nullable();
            $table->decimal('admission_fee',10,2)->default(0);
            $table->decimal('monthly_fee',10,2)->default(0);
            $table->date('start_date')->nullable();
            $table->boolean('is_completed')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};

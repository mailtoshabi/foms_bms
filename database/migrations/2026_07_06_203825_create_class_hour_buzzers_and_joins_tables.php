<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_hour_student_joins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_hour_id')->constrained('class_hours')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->timestamp('joined_at')->useCurrent();
        });

        Schema::create('class_hour_buzzers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_hour_id')->constrained('class_hours')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_hour_buzzers');
        Schema::dropIfExists('class_hour_student_joins');
    }
};

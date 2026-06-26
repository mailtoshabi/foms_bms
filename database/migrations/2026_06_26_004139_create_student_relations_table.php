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
        Schema::create('student_relations', function (Blueprint $table) {
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('related_student_id')->constrained('students')->cascadeOnDelete();
            $table->primary(['student_id', 'related_student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_relations');
    }
};

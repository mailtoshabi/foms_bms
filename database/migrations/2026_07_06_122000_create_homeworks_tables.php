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
        Schema::create('homeworks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_room_id')->constrained('class_rooms')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->string('title');
            $table->longText('content')->nullable();
            $table->timestamps();
        });

        Schema::create('homework_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homework_id')->constrained('homeworks')->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->timestamps();
        });

        Schema::create('homework_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homework_id')->constrained('homeworks')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->longText('submitted_text')->nullable();
            $table->decimal('total_mark', 5, 2)->nullable();
            $table->decimal('mark_obtained', 5, 2)->nullable();
            $table->text('teacher_comments')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('teachers')->nullOnDelete();
            $table->timestamp('graded_at')->nullable();
            $table->timestamps();

            $table->unique(['homework_id', 'student_id']);
        });

        Schema::create('homework_submission_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homework_submission_id')->constrained('homework_submissions')->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homework_submission_files');
        Schema::dropIfExists('homework_submissions');
        Schema::dropIfExists('homework_files');
        Schema::dropIfExists('homeworks');
    }
};

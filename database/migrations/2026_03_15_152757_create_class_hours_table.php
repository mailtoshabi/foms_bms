<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_hours', function (Blueprint $table) {

            $table->id();

            $table->foreignId('class_room_id')
                ->constrained('class_rooms')
                ->cascadeOnDelete();

            $table->foreignId('teacher_id')
                ->constrained('teachers')
                ->cascadeOnDelete();

            $table->integer('duration')->nullable();

            $table->string('google_meet_link')->nullable();

            $table->dateTime('class_started_at')->nullable();

            $table->enum('status',['pending','completed'])
                ->default('pending');

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_hours');
    }
};

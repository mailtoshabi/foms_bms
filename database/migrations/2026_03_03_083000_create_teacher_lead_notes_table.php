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
        Schema::create('teacher_lead_notes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('teacher_lead_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('staff_id')
                ->constrained('staffs')
                ->cascadeOnDelete();

            $table->text('note');

            $table->enum('status', [
                'pending',
                'follow_up',
                'no_response',
                'not_interested',
                'interested',
                'converted'
            ])->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_lead_notes');
    }
};

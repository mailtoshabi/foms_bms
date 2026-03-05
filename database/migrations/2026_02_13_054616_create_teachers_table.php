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
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_lead_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('contact_number');
            $table->string('email')->nullable();
            $table->date('joining_date')->nullable();
            $table->decimal('wage_per_hour',8,2)->default(0);
            $table->string('gpay_number')->nullable();
            $table->enum('status',['active','suspended','resigned'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};

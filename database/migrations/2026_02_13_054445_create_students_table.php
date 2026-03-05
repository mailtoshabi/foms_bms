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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_lead_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->date('dob')->nullable();
            $table->string('email')->nullable();
            $table->string('contact_number');
            $table->string('parent_name')->nullable();
            $table->text('address')->nullable();
            $table->string('photo')->nullable();
            $table->string('id_proof')->nullable();
            $table->enum('status',['active','passout','dropout'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};

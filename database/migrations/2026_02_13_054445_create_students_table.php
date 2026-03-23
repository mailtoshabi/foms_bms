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
            $table->string('admission_no')->unique();
            $table->string('phone')->unique()->index();
            $table->string('password')->nullable();
            $table->foreignId('student_lead_id')->nullable()->unique()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->date('dob')->nullable();
            $table->string('email')->nullable();
            $table->string('contact_number');
            $table->string('whatsapp_number');
            $table->string('parent_name')->nullable();
            $table->text('address')->nullable();
            $table->string('photo')->nullable();
            $table->string('id_proof')->nullable();

            // New fields
            $table->integer('classes_per_week')->nullable();
            $table->json('selected_days')->nullable(); // Example: ["Monday","Wednesday","Friday"]
            $table->string('time_slot')->nullable(); // Example: "10:00 AM - 11:00 AM"
            $table->date('starting_date')->nullable();

            $table->rememberToken();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();
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

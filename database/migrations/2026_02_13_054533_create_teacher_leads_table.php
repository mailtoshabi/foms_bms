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
        Schema::create('teacher_leads', function (Blueprint $table) {
            $table->id();
            $table->string('form_token')->nullable()->unique();
            $table->timestamp('form_expires_at')->nullable();
            $table->timestamp('form_opened_at')->nullable();
            $table->boolean('form_disabled')->default(false);
            $table->string('name');
            $table->string('contact_number');
            $table->string('email')->nullable();
            $table->foreignId('source_id')
                ->nullable()
                ->constrained('sources')
                ->nullOnDelete();
            $table->enum('status',['pending','approved','not_interested'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_leads');
    }
};

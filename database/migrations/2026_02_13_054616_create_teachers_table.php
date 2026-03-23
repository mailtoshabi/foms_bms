<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {

            $table->id();

            $table->string('admission_no')->unique();

            $table->string('phone')->unique()->index();
            $table->string('password')->nullable();

            $table->foreignId('teacher_lead_id')
                ->nullable()
                ->unique()
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');
            $table->date('dob')->nullable();

            $table->string('email')->nullable();
            $table->string('contact_number');
            $table->string('whatsapp_number')->nullable();

            $table->string('upi_number')->nullable();
            $table->text('address')->nullable();

            $table->string('qualification')->nullable();
            $table->integer('experience')->nullable();

            $table->string('photo')->nullable();
            $table->string('id_proof')->nullable();

            $table->unsignedTinyInteger('salary_cycle_day')
            ->nullable()
            ->comment('Day teacher salary cycle completes');

            $table->rememberToken();

            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();

            $table->enum('status',[
                'active',
                'inactive',
                'suspended'
            ])->default('active');
            $table->index(['name','contact_number']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};

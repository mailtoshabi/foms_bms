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
        Schema::create('teacher_salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->date('salary_date');
            $table->decimal('amount',10,2);
            $table->decimal('deposit_amount',10,2)->default(0);
            $table->date('deposit_return_date')->nullable();
            $table->enum('status',['paid','not_paid','partial','deposit'])->default('not_paid');
            $table->decimal('paid_amount',10,2)->default(0);
            $table->date('paid_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_salaries');
    }
};

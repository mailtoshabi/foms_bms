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
        Schema::create('staff_salary_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_salary_id')->constrained('staff_salaries')->cascadeOnDelete();
            $table->decimal('paid_amount', 10, 2);
            $table->enum('payment_method', ['cash', 'card', 'upi', 'bank_transfer']);
            $table->text('notes')->nullable();
            $table->date('paid_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_salary_payments');
    }
};

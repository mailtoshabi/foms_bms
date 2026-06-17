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
        Schema::table('teacher_salaries', function (Blueprint $table) {
            $table->string('status', 50)->default('unpaid')->change();
        });

        Schema::create('teacher_deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')
                ->constrained('teachers')
                ->cascadeOnDelete();
            $table->foreignId('teacher_salary_id')
                ->nullable()
                ->constrained('teacher_salaries')
                ->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->date('deposited_date');
            $table->date('due_date');
            $table->string('status', 50)->default('not paid'); // 'paid', 'not paid'
            $table->date('payment_date')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_deposits');

        Schema::table('teacher_salaries', function (Blueprint $table) {
            $table->enum('status', ['paid', 'unpaid', 'partial'])->default('unpaid')->change();
        });
    }
};

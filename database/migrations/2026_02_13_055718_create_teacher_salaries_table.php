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

            $table->foreignId('teacher_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('cycle_start');
            $table->date('cycle_end');

            $table->decimal('total_hours', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);

            $table->date('payment_date')->nullable();

            $table->enum('payment_method', ['cash', 'card', 'upi', 'bank_transfer'])->nullable();
            // cash | bank | upi etc

            $table->string('reference_number')->nullable();
            // bank txn id / upi ref

            $table->text('notes')->nullable();
            $table->enum('status',['paid','unpaid','partial'])->default('unpaid');

            $table->timestamps();

            $table->index('payment_date');
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

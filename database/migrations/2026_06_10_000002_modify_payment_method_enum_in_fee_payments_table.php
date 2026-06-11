<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            if (DB::getDriverName() === 'sqlite') {
                $table->string('payment_method')->change();
            } else {
                DB::statement("ALTER TABLE fee_payments MODIFY COLUMN payment_method VARCHAR(255) NOT NULL");
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            if (DB::getDriverName() === 'sqlite') {
                $table->string('payment_method')->change();
            } else {
                DB::statement("ALTER TABLE fee_payments MODIFY COLUMN payment_method ENUM('cash', 'card', 'upi', 'bank_transfer') NOT NULL");
            }
        });
    }
};

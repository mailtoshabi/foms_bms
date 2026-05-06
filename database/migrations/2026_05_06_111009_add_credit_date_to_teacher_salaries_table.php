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
            $table->date('credit_date')->nullable()->after('payment_date');
            $table->index('credit_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher_salaries', function (Blueprint $table) {
            $table->dropIndex(['credit_date']);
            $table->dropColumn('credit_date');
        });
    }
};

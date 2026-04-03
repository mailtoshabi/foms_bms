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
        // Remove from teachers if it exists
        if (Schema::hasColumn('teachers', 'salary_amount')) {
            Schema::table('teachers', function (Blueprint $table) {
                $table->dropColumn('salary_amount');
            });
        }

        // Add to staffs
        Schema::table('staffs', function (Blueprint $table) {
            if (!Schema::hasColumn('staffs', 'salary_amount')) {
                $table->decimal('salary_amount', 10, 2)->nullable()->after('gpay_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staffs', function (Blueprint $table) {
            $table->dropColumn('salary_amount');
        });
    }
};

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
        Schema::table('staff_salaries', function (Blueprint $table) {
            // Change salary_month from date to string (YYYY-MM format)
            $table->string('salary_month', 7)->nullable()->change();
        });

        // Convert existing dates to YYYY-MM format
        DB::table('staff_salaries')
            ->whereNotNull('salary_month')
            ->get()
            ->each(function ($record) {
                DB::table('staff_salaries')
                    ->where('id', $record->id)
                    ->update(['salary_month' => substr($record->salary_month, 0, 7)]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_salaries', function (Blueprint $table) {
            $table->date('salary_month')->nullable()->change();
        });
    }
};

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
        Schema::table('class_hours', function (Blueprint $table) {
            $table->decimal('hourly_wage', 10, 2)->nullable()->after('teacher_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_hours', function (Blueprint $table) {
            $table->dropColumn('hourly_wage');
        });
    }
};

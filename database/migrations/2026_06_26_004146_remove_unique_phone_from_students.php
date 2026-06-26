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
        Schema::table('students', function (Blueprint $table) {
            $table->index('country_id', 'students_country_id_index');
            $table->index(['country_id', 'phone'], 'students_country_phone_index');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique('students_country_id_phone_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->unique(['country_id', 'phone'], 'students_country_id_phone_unique');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('students_country_phone_index');
            $table->dropIndex('students_country_id_index');
        });
    }
};

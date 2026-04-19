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
            $table->foreignId('country_id')->after('admission_no')->nullable()->constrained('countries')->nullOnDelete();
            
            // Re-define uniqueness for phone per country
            $table->dropUnique(['phone']);
            $table->unique(['country_id', 'phone']);
        });

        Schema::table('student_leads', function (Blueprint $table) {
            $table->foreignId('country_id')->after('id')->nullable()->constrained('countries')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique(['country_id', 'phone']);
            $table->unique('phone');
            $table->dropConstrainedForeignId('country_id');
        });

        Schema::table('student_leads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('country_id');
        });
    }
};

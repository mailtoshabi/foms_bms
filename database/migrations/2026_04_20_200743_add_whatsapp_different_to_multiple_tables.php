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
            $table->boolean('is_whatsapp_different')->default(false)->after('whatsapp_number');
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->boolean('is_whatsapp_different')->default(false)->after('whatsapp_number');
        });

        Schema::table('student_leads', function (Blueprint $table) {
            $table->string('whatsapp_number')->nullable()->after('contact_number');
            $table->boolean('is_whatsapp_different')->default(false)->after('whatsapp_number');
        });

        Schema::table('teacher_leads', function (Blueprint $table) {
            $table->string('whatsapp_number')->nullable()->after('contact_number');
            $table->boolean('is_whatsapp_different')->default(false)->after('whatsapp_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('is_whatsapp_different');
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn('is_whatsapp_different');
        });

        Schema::table('student_leads', function (Blueprint $table) {
            $table->dropColumn(['whatsapp_number', 'is_whatsapp_different']);
        });

        Schema::table('teacher_leads', function (Blueprint $table) {
            $table->dropColumn(['whatsapp_number', 'is_whatsapp_different']);
        });
    }
};

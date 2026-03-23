<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_attendance', function (Blueprint $table) {

            $table->string('google_meet_link')
                  ->nullable()
                  ->after('attendance_date');

        });
    }

    public function down(): void
    {
        Schema::table('teacher_attendance', function (Blueprint $table) {

            $table->dropColumn('google_meet_link');

        });
    }
};

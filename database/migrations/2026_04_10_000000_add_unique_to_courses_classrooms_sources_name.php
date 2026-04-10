<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // courses.name — globally unique
        Schema::table('courses', function (Blueprint $table) {
            $table->string('name')->unique()->change();
        });

        // class_rooms — unique per course (same name can exist in different courses)
        Schema::table('class_rooms', function (Blueprint $table) {
            $table->unique(['course_id', 'name'], 'class_rooms_course_id_name_unique');
        });

        // sources.name — globally unique
        Schema::table('sources', function (Blueprint $table) {
            $table->string('name')->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropUnique(['name']);
        });

        Schema::table('class_rooms', function (Blueprint $table) {
            $table->dropUnique('class_rooms_course_id_name_unique');
        });

        Schema::table('sources', function (Blueprint $table) {
            $table->dropUnique(['name']);
        });
    }
};

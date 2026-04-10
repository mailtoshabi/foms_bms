<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staffs', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('students', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('staffs', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};

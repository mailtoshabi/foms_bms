<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('teacher_removal_logs', function (Blueprint $table) {
            $table->string('auth_type')->nullable()->after('removed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher_removal_logs', function (Blueprint $table) {
            $table->dropColumn('auth_type');
        });
    }
};

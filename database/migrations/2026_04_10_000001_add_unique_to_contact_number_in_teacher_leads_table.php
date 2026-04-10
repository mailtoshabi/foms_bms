<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_leads', function (Blueprint $table) {
            $table->string('contact_number')->unique()->index()->change();
        });
    }

    public function down(): void
    {
        Schema::table('teacher_leads', function (Blueprint $table) {
            $table->dropUnique(['contact_number']);
        });
    }
};

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
        Schema::table('teacher_leads', function (Blueprint $table) {
            if (Schema::hasColumn('teacher_leads', 'source')) {
                $table->dropColumn('source');
            }
            if (!Schema::hasColumn('teacher_leads', 'source_id')) {
                $table->foreignId('source_id')
                    ->nullable()
                    ->constrained('sources')
                    ->nullOnDelete()
                    ->after('email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher_leads', function (Blueprint $table) {
            $table->dropForeign(['source_id']);
            $table->dropColumn('source_id');
            $table->string('source')->nullable()->after('email');
        });
    }
};

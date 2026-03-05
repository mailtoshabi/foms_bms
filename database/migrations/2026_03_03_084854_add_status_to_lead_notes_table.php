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
        Schema::table('lead_notes', function (Blueprint $table) {

            $table->enum('status', [
                'pending',
                'follow_up',
                'no_response',
                'not_interested',
                'interested',
                'converted'
            ])->default('pending')->after('note');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_notes', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};

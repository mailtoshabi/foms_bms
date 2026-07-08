<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('utilities')->insertOrIgnore([
            'key' => 'buzzer_interval',
            'value' => '20000', // Default 20 seconds (20000ms)
            'is_visible' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('utilities')->where('key', 'buzzer_interval')->delete();
    }
};

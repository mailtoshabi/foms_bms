<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {

            $table->boolean('is_admission_fee_exempted')
                ->default(false)
                ->after('status');

            $table->boolean('is_monthly_fee_exempted')
                ->default(false)
                ->after('is_admission_fee_exempted');

        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {

            $table->dropColumn([
                'is_admission_fee_exempted',
                'is_monthly_fee_exempted'
            ]);

        });
    }
};

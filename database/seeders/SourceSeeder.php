<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Source;

class SourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void
    {
        $sources = [
            'Website',
            'Google Ads',
            'Facebook',
            'Instagram',
            'Walk-in',
            'Referral',
            'WhatsApp',
            'Phone Inquiry',
            'Other'
        ];

        foreach ($sources as $source) {
            Source::updateOrCreate(
                ['name' => $source],
                ['is_active' => true]
            );
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('expense_categories')->insert([
            [
                'name' => 'room_rent',
                'description' => 'Room rent expenses',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'internet',
                'description' => 'Internet bill expenses',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'electricity',
                'description' => 'Electricity bill expenses',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'stationary',
                'description' => 'Office stationary supplies',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'tea_snacks',
                'description' => 'Tea and snacks for staff',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

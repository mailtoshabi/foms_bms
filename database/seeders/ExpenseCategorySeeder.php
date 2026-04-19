<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use App\Models\ExpenseCategory;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // Original Categories
            ['name' => 'room_rent', 'description' => 'Room rent expenses'],
            ['name' => 'internet', 'description' => 'Internet bill expenses'],
            ['name' => 'electricity', 'description' => 'Electricity bill expenses'],
            ['name' => 'stationary', 'description' => 'Office stationary supplies'],
            ['name' => 'tea_snacks', 'description' => 'Tea and snacks for staff'],

            // New Categories
            ['name' => 'office_rent', 'description' => 'Office rent expenses'],
            ['name' => 'mobile_telephone', 'description' => 'Mobile and Telephone charges'],
            ['name' => 'allowances', 'description' => 'Staff allowances'],
            ['name' => 'TA', 'description' => 'Travel Allowance'],
            ['name' => 'marketing', 'description' => 'Marketing and Promotion expenses'],
            ['name' => 'staff_salary', 'description' => 'Total staff salary expenses'],
            ['name' => 'management_fee', 'description' => 'Management and operational fees'],
            ['name' => 'accommodation', 'description' => 'Accommodation related expenses'],
            ['name' => 'miscellaneous', 'description' => 'Other miscellaneous expenses'],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::updateOrCreate(
                ['name' => $category['name']],
                ['description' => $category['description']]
            );
        }
    }
}

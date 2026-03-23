<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Utility;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::insert([
            [
                'name' => 'enrolment department',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'administrator department',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'finance department',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'hr department',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'operation department',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $utilities = [
            'id_enrolment_dept'     => 1,
            'id_administrator_dept' => 2,
            'id_finance_dept'       => 3,
            'id_hr_dept'            => 4,
            'id_operation_dept'     => 5,
        ];

        foreach ($utilities as $key => $value) {
            Utility::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}

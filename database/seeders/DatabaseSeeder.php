<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $user1=Admin::create(['name' => 'Super Admin','phone'=>'9809373738','password' => bcrypt('123456'),'photo' => '','created_at' => now()]);

        $this->call([
            RoleSeeder::class,
        ]);

        $this->call([
            SourceSeeder::class,
        ]);

        $this->call([
            ExpenseCategorySeeder::class,
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Holiday;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\ClassRoom;
use App\Models\Staff;
use App\Models\Role;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HolidayNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_roles_and_admin_holiday_notification_permissions(): void
    {
        // 1. Create a staff user
        $staff = Staff::create([
            'name' => 'Test Staff',
            'email' => 'teststaff@example.com',
            'phone' => '1234567890',
            'password' => bcrypt('password'),
        ]);

        \App\Models\Utility::create(['key' => 'id_administrator_dept', 'value' => '1', 'is_visible' => true]);
        \App\Models\Utility::create(['key' => 'id_hr_dept', 'value' => '2', 'is_visible' => true]);
        \App\Models\Utility::create(['key' => 'id_operation_dept', 'value' => '3', 'is_visible' => true]);
        \App\Models\Utility::create(['key' => 'id_finance_dept', 'value' => '4', 'is_visible' => true]);

        Role::forceCreate([
            'id' => 1,
            'name' => 'Administrator Department',
        ]);
        Role::forceCreate([
            'id' => 3,
            'name' => 'Operations Department',
        ]);

        // Attach administrator role
        $staff->roles()->attach(1);

        // 2. Create teachers
        $teacher = Teacher::create([
            'name' => 'Teacher One',
            'contact_number' => '1111111111',
            'phone' => '1111111111',
            'status' => 'active',
        ]);

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        // 3. Post request as Administrator (Unauthorized role) -> should abort with 403
        $response = $this->actingAs($staff, 'staff')
            ->post(route('staff.holidays.store'), [
                'title' => 'Admin Test Break',
                'description' => 'Test',
                'date' => now()->addDays(2)->toDateString(),
                'target_type' => 'selected_teachers',
                'teacher_ids' => [$teacher->id],
            ]);

        $response->assertStatus(403);

        // 4. Attach Operations role to staff and request again -> should succeed
        $staff->roles()->detach(1);
        $staff->roles()->attach(3);
        $staff->load('roles');

        $response = $this->actingAs($staff, 'staff')
            ->post(route('staff.holidays.store'), [
                'title' => 'Operations Test Break',
                'description' => 'Test',
                'date' => now()->addDays(2)->toDateString(),
                'target_type' => 'selected_teachers',
                'teacher_ids' => [$teacher->id],
            ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('staff.holidays.index'));
        $this->assertDatabaseHas('holidays', ['title' => 'Operations Test Break']);

        // 5. Create an Admin user and post to admin.holidays.store -> should succeed
        $admin = Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone' => '9999999999',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.holidays.store'), [
                'title' => 'Global Admin Break',
                'description' => 'Announced by Admin',
                'date' => now()->addDays(3)->toDateString(),
                'target_type' => 'all_teachers',
            ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('admin.holidays.index'));
        $this->assertDatabaseHas('holidays', ['title' => 'Global Admin Break']);
    }
}

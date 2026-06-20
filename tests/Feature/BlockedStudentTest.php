<?php

namespace Tests\Feature;

use App\Models\ClassHour;
use App\Models\ClassRoom;
use App\Models\ClassType;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Fee;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Teacher;
use App\Services\FeeService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlockedStudentTest extends TestCase
{
    use RefreshDatabase;

    private function setupDependencies(): array
    {
        $category = CourseCategory::firstOrCreate(['name' => 'school']);
        
        $course = Course::create([
            'category_id' => $category->id,
            'name' => 'Grammar Class'
        ]);

        $groupType = ClassType::firstOrCreate(['name' => 'group']);
        $individualType = ClassType::firstOrCreate(['name' => 'individual']);

        return [$course, $groupType, $individualType];
    }

    public function test_blocked_student_cannot_login(): void
    {
        // 1. Create a country
        $country = new \App\Models\Country();
        $country->name = 'India';
        $country->code = '91';
        $country->save();

        // 2. Create a blocked student
        $student = Student::create([
            'admission_no' => 'S001',
            'country_id' => $country->id,
            'phone' => '1234567890',
            'password' => bcrypt('password'),
            'name' => 'Blocked Student',
            'contact_number' => '1234567890',
            'whatsapp_number' => '911234567890',
            'status' => 'active',
            'is_blocked' => true
        ]);

        // 3. Attempt to login
        $response = $this->post(route('student.login.submit'), [
            'country_id' => $country->id,
            'phone' => '1234567890',
            'password' => 'password'
        ]);

        // Should redirect back with errors
        $response->assertStatus(302);
        $response->assertSessionHasErrors('phone');
        $this->assertFalse(auth('student')->check());
    }

    public function test_blocked_student_is_logged_out_when_accessing_routes(): void
    {
        // 1. Create a country
        $country = new \App\Models\Country();
        $country->name = 'India';
        $country->code = '91';
        $country->save();

        // 2. Create a student who is currently blocked
        $student = Student::create([
            'admission_no' => 'S002',
            'country_id' => $country->id,
            'phone' => '1234567890',
            'password' => bcrypt('password'),
            'name' => 'Blocked Student',
            'contact_number' => '1234567890',
            'whatsapp_number' => '911234567890',
            'status' => 'active',
            'is_blocked' => true
        ]);

        // 3. Access dashboard directly as student
        $response = $this->actingAs($student, 'student')
            ->get(route('student.dashboard'));

        // Should be logged out and redirected to login page with errors
        $response->assertStatus(302);
        $response->assertRedirect(route('student.login'));
        $response->assertSessionHasErrors('phone');
        $this->assertFalse(auth('student')->check());
    }

    public function test_blocked_student_does_not_generate_group_fees(): void
    {
        [$course, $groupType] = $this->setupDependencies();

        $classRoom = ClassRoom::create([
            'course_id' => $course->id,
            'class_type_id' => $groupType->id,
            'name' => 'Group Class',
            'admission_fee' => 100,
            'monthly_fee' => 500,
            'classes_per_week' => 2,
            'is_completed' => false,
            'starting_date' => '2026-04-10',
        ]);

        // Blocked student
        $blockedStudent = Student::create([
            'admission_no' => 'S003',
            'phone' => '1234567890',
            'name' => 'Blocked Student',
            'contact_number' => '1234567890',
            'whatsapp_number' => '1234567890',
            'status' => 'active',
            'is_blocked' => true
        ]);

        // Active student
        $activeStudent = Student::create([
            'admission_no' => 'S004',
            'phone' => '1234567891',
            'name' => 'Active Student',
            'contact_number' => '1234567891',
            'whatsapp_number' => '1234567891',
            'status' => 'active',
            'is_blocked' => false
        ]);

        $classRoom->students()->attach([$blockedStudent->id, $activeStudent->id]);

        // Set date to 1 month later
        Carbon::setTestNow('2026-05-10 12:00:00');

        $feeService = new FeeService();
        $feeService->generateGroupFeesForToday();

        // Active student should have fee
        $this->assertTrue(Fee::where('student_id', $activeStudent->id)->exists());
        // Blocked student should NOT have fee
        $this->assertFalse(Fee::where('student_id', $blockedStudent->id)->exists());

        Carbon::setTestNow();
    }

    public function test_blocked_student_does_not_generate_individual_fees(): void
    {
        [$course, , $individualType] = $this->setupDependencies();

        $classRoom = ClassRoom::create([
            'course_id' => $course->id,
            'class_type_id' => $individualType->id,
            'name' => 'Individual Class',
            'admission_fee' => 100,
            'monthly_fee' => 500,
            'classes_per_week' => 2,
            'is_completed' => false,
            'starting_date' => '2026-04-10',
        ]);

        // Blocked student
        $blockedStudent = Student::create([
            'admission_no' => 'S005',
            'phone' => '1234567890',
            'name' => 'Blocked Student',
            'contact_number' => '1234567890',
            'whatsapp_number' => '1234567890',
            'status' => 'active',
            'is_blocked' => true
        ]);

        $classRoom->students()->attach($blockedStudent->id);

        $teacher = Teacher::create([
            'admission_no' => 'T001',
            'phone' => '0987654321',
            'name' => 'Teacher',
            'contact_number' => '0987654321',
            'status' => 'active'
        ]);

        // Add 8 completed sessions for the individual class (required classes = 8)
        for ($i = 0; $i < 8; $i++) {
            $classHour = ClassHour::create([
                'class_room_id' => $classRoom->id,
                'teacher_id' => $teacher->id,
                'status' => 'pending',
                'google_meet_link' => 'https://meet.google.com/abc-defg-hij',
                'duration' => 60,
                'hourly_wage' => 50,
                'link_updated_at' => now(),
            ]);

            // Mark student present in request input
            $this->actingAs($teacher, 'teacher')
                ->post(route('teacher.class-hours.complete', $classHour->id), [
                    'attendance' => [$blockedStudent->id => 1]
                ]);
        }

        // Verify that no fee was created for the blocked student
        $this->assertFalse(Fee::where('student_id', $blockedStudent->id)->exists());
    }

    public function test_student_filtering_by_blocked_status(): void
    {
        // 1. Seed roles and utilities
        \App\Models\Role::forceCreate([
            'id' => 1,
            'name' => 'enrolment department',
            'is_active' => true,
        ]);
        \App\Models\Utility::create(['key' => 'id_enrolment_dept', 'value' => '1', 'is_visible' => true]);
        
        \App\Models\Role::forceCreate([
            'id' => 5,
            'name' => 'operation department',
            'is_active' => true,
        ]);
        \App\Models\Utility::create(['key' => 'id_operation_dept', 'value' => '5', 'is_visible' => true]);

        // 2. Create a blocked student and an active student
        $blockedStudent = Student::create([
            'admission_no' => 'S006',
            'phone' => '1234567890',
            'name' => 'Blocked Student X',
            'contact_number' => '1234567890',
            'whatsapp_number' => '1234567890',
            'status' => 'active',
            'is_blocked' => true
        ]);

        $activeStudent = Student::create([
            'admission_no' => 'S007',
            'phone' => '1234567891',
            'name' => 'Active Student Y',
            'contact_number' => '1234567891',
            'whatsapp_number' => '1234567891',
            'status' => 'active',
            'is_blocked' => false
        ]);

        // 3. Create a staff user (Enrolment department)
        $staffUser = \App\Models\Staff::create([
            'name' => 'Staff Member',
            'phone' => '1112223334',
            'email' => 'staff@example.com',
            'password' => bcrypt('password'),
        ]);
        $staffUser->roles()->attach(1); // Enrolment department

        // 4. Create an admin user
        $adminUser = \App\Models\Admin::create([
            'name' => 'Admin Member',
            'phone' => '9999999999',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // 5. Test Staff Index Filter: is_blocked = 1
        $response = $this->actingAs($staffUser, 'staff')
            ->get(route('staff.students.index', ['is_blocked' => '1']));
        $response->assertStatus(200);
        $response->assertSee('Blocked Student X');
        $response->assertDontSee('Active Student Y');

        // 6. Test Staff Index Filter: is_blocked = 0
        $response = $this->actingAs($staffUser, 'staff')
            ->get(route('staff.students.index', ['is_blocked' => '0']));
        $response->assertStatus(200);
        $response->assertSee('Active Student Y');
        $response->assertDontSee('Blocked Student X');

        // 7. Test Admin Report Filter: is_blocked = 1
        $response = $this->actingAs($adminUser, 'admin')
            ->get(route('admin.reports.students', ['is_blocked' => '1']));
        $response->assertStatus(200);
        $response->assertSee('Blocked Student X');
        $response->assertDontSee('Active Student Y');

        // 8. Test Admin Report Filter: is_blocked = 0
        $response = $this->actingAs($adminUser, 'admin')
            ->get(route('admin.reports.students', ['is_blocked' => '0']));
        $response->assertStatus(200);
        $response->assertSee('Active Student Y');
        $response->assertDontSee('Blocked Student X');
    }

    public function test_admin_can_toggle_block_from_student_view_page(): void
    {
        $student = Student::create([
            'admission_no' => 'S008',
            'phone' => '1234567890',
            'name' => 'Student To Block',
            'contact_number' => '1234567890',
            'whatsapp_number' => '1234567890',
            'status' => 'active',
            'is_blocked' => false
        ]);

        $adminUser = \App\Models\Admin::create([
            'name' => 'Admin Member',
            'phone' => '9999999999',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // Toggle block via Admin route
        $response = $this->actingAs($adminUser, 'admin')
            ->get(route('admin.reports.students.toggleBlock', encrypt($student->id)));

        $response->assertStatus(302);
        $this->assertTrue($student->refresh()->is_blocked);

        // Toggle back (unblock)
        $response = $this->actingAs($adminUser, 'admin')
            ->get(route('admin.reports.students.toggleBlock', encrypt($student->id)));

        $response->assertStatus(302);
        $this->assertFalse($student->refresh()->is_blocked);
    }

    public function test_staff_cannot_toggle_block_via_admin_route(): void
    {
        $student = Student::create([
            'admission_no' => 'S009',
            'phone' => '1234567890',
            'name' => 'Student To Block',
            'contact_number' => '1234567890',
            'whatsapp_number' => '1234567890',
            'status' => 'active',
            'is_blocked' => false
        ]);

        $staffUser = \App\Models\Staff::create([
            'name' => 'Staff Member',
            'phone' => '1112223334',
            'email' => 'staff@example.com',
            'password' => bcrypt('password'),
        ]);

        // Attempt toggle block via Admin route - should redirect to admin login
        $response = $this->actingAs($staffUser, 'staff')
            ->get(route('admin.reports.students.toggleBlock', encrypt($student->id)));

        $response->assertRedirect(route('admin.login'));
        $this->assertFalse($student->refresh()->is_blocked);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Fee;
use App\Models\Role;
use App\Models\Staff;
use App\Models\Student;
use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\ClassType;
use App\Models\CourseCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManualFeeTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private Staff $staff;
    private Student $student;
    private ClassRoom $classRoom;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user
        $this->admin = Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone' => '9999999999',
            'password' => bcrypt('password'),
        ]);

        // Create staff user
        $this->staff = Staff::create([
            'name' => 'Staff User',
            'email' => 'staff@example.com',
            'phone' => '1234567890',
            'password' => bcrypt('password'),
        ]);

        // Create student
        $this->student = Student::create([
            'admission_no' => 'S1234',
            'name' => 'Jane Student',
            'email' => 'jane@example.com',
            'phone' => '9876543210',
            'contact_number' => '9876543210',
            'whatsapp_number' => '9876543210',
            'status' => 'active',
            'wallet_balance' => 0.00
        ]);

        // Create classroom
        $category = CourseCategory::create(['name' => 'TestCategory']);
        $course = Course::create([
            'category_id' => $category->id,
            'name' => 'Test Course'
        ]);
        $classType = ClassType::where('name', 'group')->first() ?: ClassType::forceCreate(['name' => 'group']);
        $this->classRoom = ClassRoom::create([
            'name' => 'Test Class',
            'course_id' => $course->id,
            'class_type_id' => $classType->id,
            'fees_amount' => 500.00
        ]);
    }

    public function test_admin_can_access_create_manual_fee_page(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.fees.create'));

        $response->assertStatus(200);
        $response->assertSee('Enter Fee Manually');
    }

    public function test_staff_cannot_access_create_manual_fee_page(): void
    {
        $response = $this->actingAs($this->staff, 'staff')
            ->get(route('admin.fees.create'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_search_students_via_ajax(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.fees.students.search', ['q' => 'Jane']));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $this->student->id,
            'text' => 'Jane Student (S1234)'
        ]);
    }

    public function test_admin_can_manually_record_fee(): void
    {
        $feeDate = '2026-06-17';
        $expectedDueDate = '2026-06-24'; // +7 days

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.fees.store'), [
                'student_id' => $this->student->id,
                'class_room_id' => $this->classRoom->id,
                'type' => 'monthly',
                'amount' => 1200.00,
                'date' => $feeDate,
            ]);

        $response->assertRedirect(route('admin.reports.fee'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('fees', [
            'student_id' => $this->student->id,
            'class_room_id' => $this->classRoom->id,
            'type' => 'monthly',
            'amount' => 1200.00,
            'due_date' => $expectedDueDate,
            'status' => 'unpaid'
        ]);
    }

    public function test_manual_fee_creation_prevents_duplicates(): void
    {
        $feeDate = '2026-06-17';
        $expectedDueDate = '2026-06-24'; // +7 days

        // First creation
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.fees.store'), [
                'student_id' => $this->student->id,
                'class_room_id' => $this->classRoom->id,
                'type' => 'monthly',
                'amount' => 1200.00,
                'date' => $feeDate,
            ]);

        // Attempt duplicate creation
        $response = $this->actingAs($this->admin, 'admin')
            ->from(route('admin.fees.create'))
            ->post(route('admin.fees.store'), [
                'student_id' => $this->student->id,
                'class_room_id' => $this->classRoom->id,
                'type' => 'monthly',
                'amount' => 1200.00,
                'date' => $feeDate,
            ]);

        $response->assertRedirect(route('admin.fees.create'));
        $response->assertSessionHas('error');

        // Only 1 fee should exist in database
        $this->assertEquals(1, Fee::where('student_id', $this->student->id)->count());
    }
}

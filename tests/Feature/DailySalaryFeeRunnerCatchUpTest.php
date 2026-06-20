<?php

namespace Tests\Feature;

use App\Models\ClassHour;
use App\Models\ClassRoom;
use App\Models\ClassType;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Fee;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherSalary;
use App\Models\Utility;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailySalaryFeeRunnerCatchUpTest extends TestCase
{
    use RefreshDatabase;

    private function setupDependencies(): array
    {
        $category = CourseCategory::firstOrCreate(['name' => 'school']);
        
        $course = Course::create([
            'category_id' => $category->id,
            'name' => 'CatchUp Grammar Class'
        ]);
        
        $classType = ClassType::firstOrCreate(['name' => 'group']);

        return [$course, $classType];
    }

    public function test_daily_process_catch_up_mechanism(): void
    {
        [$course, $classType] = $this->setupDependencies();

        // 1. Setup a Group Classroom that started on April 10th
        $classRoom = ClassRoom::create([
            'course_id' => $course->id,
            'class_type_id' => $classType->id,
            'name' => 'Group Class 1',
            'admission_fee' => 100,
            'monthly_fee' => 500,
            'classes_per_week' => 2,
            'is_completed' => false,
            'starting_date' => '2026-04-10',
        ]);

        // 2. Setup Student
        $student = Student::create([
            'admission_no' => 'S201',
            'phone' => '1234567891',
            'name' => 'Student One',
            'contact_number' => '1234567891',
            'whatsapp_number' => '1234567891',
            'status' => 'active'
        ]);
        $classRoom->students()->attach($student->id);

        // 3. Setup Teacher with salary cycle day as 10
        $teacher = Teacher::create([
            'admission_no' => 'T201',
            'phone' => '0987654321',
            'name' => 'Teacher One',
            'contact_number' => '0987654321',
            'salary_cycle_day' => 10,
            'status' => 'active'
        ]);
        $classRoom->teachers()->attach($teacher->id, [
            'hourly_wage' => 50,
            'assigned_at' => '2026-04-10'
        ]);

        // 4. Create completed class hours for teacher in the billing window (April 10 to May 9)
        ClassHour::create([
            'class_room_id' => $classRoom->id,
            'teacher_id' => $teacher->id,
            'duration' => 120, // 2 hours
            'hourly_wage' => 50,
            'google_meet_link' => 'https://meet.google.com/abc-defg-hij',
            'status' => 'completed',
            'link_updated_at' => '2026-05-01 10:00:00',
            'completed_at' => '2026-05-01 10:00:00',
            'has_fee_calculated' => false,
            'has_salary_calculated' => false,
        ]);

        // 5. Simulate the daily process last ran on May 9th (Saturday)
        Utility::create([
            'key' => 'daily_process_last_run',
            'value' => '2026-05-09'
        ]);

        // 6. Set "Test Now" to May 11th (Monday) - simulating that Sunday May 10th was skipped!
        Carbon::setTestNow('2026-05-11 12:00:00');

        // Verify pre-conditions
        $this->assertEquals(0, Fee::count());
        $this->assertEquals(0, TeacherSalary::count());

        // 7. Run the daily process
        runDailySalaryFeeProcess();

        // 8. Assertions for catch-up success:
        // A student Fee should have been generated for May 10th (exactly 1 month from April 10th)
        $this->assertEquals(1, Fee::count());
        $fee = Fee::first();
        $this->assertEquals($student->id, $fee->student_id);
        $this->assertEquals(500, $fee->amount);
        $this->assertEquals('monthly', $fee->type);
        // Due date should be relative to the simulated day (May 10 + 7 days = May 17)
        $this->assertEquals('2026-05-17', Carbon::parse($fee->due_date)->toDateString());

        // A teacher salary should have been generated for May 10th cycle day
        $this->assertEquals(1, TeacherSalary::count());
        $salary = TeacherSalary::first();
        $this->assertEquals($teacher->id, $salary->teacher_id);
        $this->assertEquals(2, $salary->total_hours);
        $this->assertEquals(100.0, (float)$salary->total_amount);
        $this->assertEquals('2026-04-10', Carbon::parse($salary->cycle_start)->toDateString());
        $this->assertEquals('2026-05-09', Carbon::parse($salary->cycle_end)->toDateString());

        // Utility last run date should be updated to May 11th (today)
        $this->assertEquals('2026-05-11', Utility::where('key', 'daily_process_last_run')->value('value'));

        Carbon::setTestNow();
    }

    public function test_middleware_checks_daily_process_correctly_over_multiple_days(): void
    {
        // 1. Create a Staff user
        $staff = \App\Models\Staff::create([
            'name' => 'Test Staff',
            'email' => 'staff@example.com',
            'phone' => '1234567890',
            'password' => bcrypt('password'),
            'is_blocked' => false
        ]);

        // 2. Set current date to May 10th
        Carbon::setTestNow('2026-05-10 12:00:00');

        // 3. Request dashboard as staff and verify session is updated
        $response = $this->actingAs($staff, 'staff')
            ->get(route('staff.dashboard'));

        $response->assertStatus(200);
        $this->assertEquals('2026-05-10', session('salary_checked'));

        // 4. Move to May 11th (next day)
        Carbon::setTestNow('2026-05-11 12:00:00');

        // 5. Request dashboard again, verify session updates to the new date
        $response = $this->actingAs($staff, 'staff')
            ->get(route('staff.dashboard'));

        $response->assertStatus(200);
        $this->assertEquals('2026-05-11', session('salary_checked'));

        Carbon::setTestNow();
    }
}

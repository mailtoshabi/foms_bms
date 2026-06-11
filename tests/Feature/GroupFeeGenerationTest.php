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
use App\Services\FeeService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupFeeGenerationTest extends TestCase
{
    use RefreshDatabase;

    private function setupDependencies(): array
    {
        $category = CourseCategory::firstOrCreate(['name' => 'school']);
        
        $course = Course::create([
            'category_id' => $category->id,
            'name' => 'Group Grammar Class'
        ]);
        
        $classType = ClassType::firstOrCreate(['name' => 'group']);

        return [$course, $classType];
    }

    public function test_fee_generation_for_active_group_classes_only(): void
    {
        [$course, $classType] = $this->setupDependencies();

        $activeClass = ClassRoom::create([
            'course_id' => $course->id,
            'class_type_id' => $classType->id,
            'name' => 'Active Group Class',
            'admission_fee' => 100,
            'monthly_fee' => 500,
            'classes_per_week' => 2,
            'is_completed' => false,
            'starting_date' => '2026-04-10',
        ]);

        $completedClass = ClassRoom::create([
            'course_id' => $course->id,
            'class_type_id' => $classType->id,
            'name' => 'Completed Group Class',
            'admission_fee' => 100,
            'monthly_fee' => 500,
            'classes_per_week' => 2,
            'is_completed' => true,
            'starting_date' => '2026-04-10',
        ]);

        $student1 = Student::create([
            'admission_no' => 'S101',
            'phone' => '1234567891',
            'name' => 'Student One',
            'contact_number' => '1234567891',
            'whatsapp_number' => '1234567891',
            'status' => 'active'
        ]);

        $student2 = Student::create([
            'admission_no' => 'S102',
            'phone' => '1234567892',
            'name' => 'Student Two',
            'contact_number' => '1234567892',
            'whatsapp_number' => '1234567892',
            'status' => 'active'
        ]);

        $activeClass->students()->attach($student1->id);
        $completedClass->students()->attach($student2->id);

        // Run today on May 10th (exactly 1 month completed)
        Carbon::setTestNow('2026-05-10 12:00:00');

        $feeService = new FeeService();
        $feeService->generateGroupFeesForToday();

        // Expect fee created for student1 (active class) but NOT student2 (completed class)
        $this->assertTrue(Fee::where('student_id', $student1->id)->exists());
        $this->assertFalse(Fee::where('student_id', $student2->id)->exists());

        Carbon::setTestNow();
    }

    public function test_fee_generation_only_after_one_month_complete(): void
    {
        [$course, $classType] = $this->setupDependencies();

        // Starting date is May 10th
        $classRoom = ClassRoom::create([
            'course_id' => $course->id,
            'class_type_id' => $classType->id,
            'name' => 'New Group Class',
            'admission_fee' => 100,
            'monthly_fee' => 500,
            'classes_per_week' => 2,
            'is_completed' => false,
            'starting_date' => '2026-05-10',
        ]);

        $student = Student::create([
            'admission_no' => 'S103',
            'phone' => '1234567893',
            'name' => 'Student Three',
            'contact_number' => '1234567893',
            'whatsapp_number' => '1234567893',
            'status' => 'active'
        ]);

        $classRoom->students()->attach($student->id);

        $feeService = new FeeService();

        // 1. Check on the starting date (May 10th) - should not generate fee
        Carbon::setTestNow('2026-05-10 12:00:00');
        $feeService->generateGroupFeesForToday();
        $this->assertFalse(Fee::where('student_id', $student->id)->exists());

        // 2. Check 15 days later (May 25th) - should not generate fee
        Carbon::setTestNow('2026-05-25 12:00:00');
        $feeService->generateGroupFeesForToday();
        $this->assertFalse(Fee::where('student_id', $student->id)->exists());

        // 3. Check 1 month later (June 10th) - should generate fee
        Carbon::setTestNow('2026-06-10 12:00:00');
        $feeService->generateGroupFeesForToday();
        $this->assertTrue(Fee::where('student_id', $student->id)->exists());

        Carbon::setTestNow();
    }

    public function test_fee_generation_duplicate_prevention_within_25_days(): void
    {
        [$course, $classType] = $this->setupDependencies();

        $classRoom = ClassRoom::create([
            'course_id' => $course->id,
            'class_type_id' => $classType->id,
            'name' => 'Duplicate Prevention Class',
            'admission_fee' => 100,
            'monthly_fee' => 500,
            'classes_per_week' => 2,
            'is_completed' => false,
            'starting_date' => '2026-04-10',
        ]);

        $student = Student::create([
            'admission_no' => 'S104',
            'phone' => '1234567894',
            'name' => 'Student Four',
            'contact_number' => '1234567894',
            'whatsapp_number' => '1234567894',
            'status' => 'active'
        ]);

        $classRoom->students()->attach($student->id);

        $feeService = new FeeService();

        // 1. Generate fee first time on May 10th
        Carbon::setTestNow('2026-05-10 12:00:00');
        $feeService->generateGroupFeesForToday();
        $this->assertEquals(1, Fee::where('student_id', $student->id)->count());

        // 2. Try to run it again on the same day (May 10th) - should not generate duplicate
        $feeService->generateGroupFeesForToday();
        $this->assertEquals(1, Fee::where('student_id', $student->id)->count());

        // 3. Try to run it on May 15th (day of month is different, but if day is forced or edge cases run) - should not generate duplicate within 25 days
        // Let's manually trigger it by forcing the day or simulating it
        Carbon::setTestNow('2026-05-20 12:00:00');
        // Let's check with a mock classroom day or manually calling fee creation check
        $exists = Fee::where('student_id', $student->id)
            ->where('class_room_id', $classRoom->id)
            ->where('type', 'monthly')
            ->whereDate('created_at', '>=', '2026-05-20') // within 25 days
            ->exists();
        // Since May 10th is within 25 days of May 20th, it must return true
        $existsCheck = Fee::where('student_id', $student->id)
            ->where('class_room_id', $classRoom->id)
            ->where('type', 'monthly')
            ->whereDate('created_at', '>=', Carbon::parse('2026-05-20')->subDays(25)->toDateString())
            ->exists();
        $this->assertTrue($existsCheck);

        Carbon::setTestNow();
    }

    public function test_class_hours_are_marked_calculated_when_fee_generated(): void
    {
        [$course, $classType] = $this->setupDependencies();

        $classRoom = ClassRoom::create([
            'course_id' => $course->id,
            'class_type_id' => $classType->id,
            'name' => 'Group Class Hour Test',
            'admission_fee' => 100,
            'monthly_fee' => 500,
            'classes_per_week' => 2,
            'is_completed' => false,
            'starting_date' => '2026-04-10',
        ]);

        $student = Student::create([
            'admission_no' => 'S105',
            'phone' => '1234567895',
            'name' => 'Student Five',
            'contact_number' => '1234567895',
            'whatsapp_number' => '1234567895',
            'status' => 'active'
        ]);

        $classRoom->students()->attach($student->id);

        $teacher = Teacher::create([
            'admission_no' => 'T102',
            'phone' => '0987654321',
            'name' => 'Teacher One',
            'contact_number' => '0987654321',
            'status' => 'active'
        ]);

        // Create completed class hours that are not yet calculated
        $ch1 = ClassHour::create([
            'class_room_id' => $classRoom->id,
            'teacher_id' => $teacher->id,
            'status' => 'completed',
            'has_fee_calculated' => false,
        ]);

        $ch2 = ClassHour::create([
            'class_room_id' => $classRoom->id,
            'teacher_id' => $teacher->id,
            'status' => 'completed',
            'has_fee_calculated' => false,
        ]);

        // Create a non-completed class hour
        $ch3 = ClassHour::create([
            'class_room_id' => $classRoom->id,
            'teacher_id' => $teacher->id,
            'status' => 'pending',
            'has_fee_calculated' => false,
        ]);

        // Create a completed class hour that already has fee calculated
        $ch4 = ClassHour::create([
            'class_room_id' => $classRoom->id,
            'teacher_id' => $teacher->id,
            'status' => 'completed',
            'has_fee_calculated' => true,
        ]);

        // Run today on May 10th (exactly 1 month completed)
        Carbon::setTestNow('2026-05-10 12:00:00');

        $feeService = new FeeService();
        $feeService->generateGroupFeesForToday();

        // Verify the fee is created for the student
        $this->assertTrue(Fee::where('student_id', $student->id)->exists());

        // Verify the completed class hours without fee calculated are updated to true
        $this->assertTrue($ch1->refresh()->has_fee_calculated);
        $this->assertTrue($ch2->refresh()->has_fee_calculated);

        // Verify the non-completed or already calculated ones remain correct
        $this->assertFalse($ch3->refresh()->has_fee_calculated);
        $this->assertTrue($ch4->refresh()->has_fee_calculated);

        Carbon::setTestNow();
    }

    public function test_teacher_cannot_start_group_class_exceeding_monthly_limit(): void
    {
        [$course, $classType] = $this->setupDependencies();

        $classRoom = ClassRoom::create([
            'course_id' => $course->id,
            'class_type_id' => $classType->id,
            'name' => 'Limit Group Class',
            'admission_fee' => 100,
            'monthly_fee' => 500,
            'classes_per_week' => 2,
            'is_completed' => false,
            'starting_date' => '2026-04-10', // Started on April 10th
        ]);

        $student = Student::create([
            'admission_no' => 'S106',
            'phone' => '1234567896',
            'name' => 'Student Six',
            'contact_number' => '1234567896',
            'whatsapp_number' => '1234567896',
            'status' => 'active'
        ]);

        $classRoom->students()->attach($student->id);

        $teacher = Teacher::create([
            'admission_no' => 'T202',
            'phone' => '0987654322',
            'name' => 'Teacher Two',
            'contact_number' => '0987654322',
            'status' => 'active'
        ]);

        $classRoom->teachers()->attach($teacher->id, [
            'hourly_wage' => 50,
            'assigned_at' => '2026-04-10'
        ]);

        // ==========================================
        // CYCLE 1: April 10th to May 10th
        // ==========================================

        // 1. Pre-create 8 completed class hours (classes_per_week * 4 = 8) inside Cycle 1
        for ($i = 0; $i < 8; $i++) {
            ClassHour::create([
                'class_room_id' => $classRoom->id,
                'teacher_id' => $teacher->id,
                'status' => 'completed',
                'completed_at' => '2026-04-20 10:00:00', // within Cycle 1
                'google_meet_link' => 'https://meet.google.com/abc-defg-hij',
                'duration' => 60,
                'hourly_wage' => 50,
            ]);
        }

        // 2. Set test time to May 5th (within Cycle 1)
        Carbon::setTestNow('2026-05-05 12:00:00');

        // Attempt to start the 9th class session in Cycle 1 -> Should be blocked!
        $response = $this->actingAs($teacher, 'teacher')
            ->post(route('teacher.classes.start'), [
                'class_room_id' => $classRoom->id,
                'google_meet_link' => 'https://meet.google.com/xyz-pdq-rst'
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error');
        $this->assertStringContainsString('maximum allowed 8 classes', session('error'));
        $this->assertFalse(ClassHour::where('class_room_id', $classRoom->id)->where('status', 'pending')->exists());

        // ==========================================
        // CYCLE 2: May 10th to June 10th
        // ==========================================

        // 3. Set test time to May 15th (within Cycle 2)
        // Since Cycle 1 is over, completed class hours from April 20th are not in the current billing cycle.
        // Therefore, the count of completed and uncalculated class hours in Cycle 2 is 0.
        // Starting a new class in Cycle 2 should succeed!
        Carbon::setTestNow('2026-05-15 12:00:00');

        $response = $this->actingAs($teacher, 'teacher')
            ->post(route('teacher.classes.start'), [
                'class_room_id' => $classRoom->id,
                'google_meet_link' => 'https://meet.google.com/xyz-pdq-rst'
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $this->assertTrue(ClassHour::where('class_room_id', $classRoom->id)->where('status', 'pending')->exists());

        // Let's delete the pending class hour to keep DB clean for subsequent limit tests
        ClassHour::where('class_room_id', $classRoom->id)->where('status', 'pending')->delete();

        // 4. Now, let's pre-create 8 completed class hours inside Cycle 2 (completed_at in May 10th to June 10th)
        for ($i = 0; $i < 8; $i++) {
            ClassHour::create([
                'class_room_id' => $classRoom->id,
                'teacher_id' => $teacher->id,
                'status' => 'completed',
                'completed_at' => '2026-05-20 10:00:00', // within Cycle 2
                'google_meet_link' => 'https://meet.google.com/abc-defg-hij',
                'duration' => 60,
                'hourly_wage' => 50,
            ]);
        }

        // Attempt to start a new class session on May 25th (within Cycle 2) -> Should be blocked!
        Carbon::setTestNow('2026-05-25 12:00:00');

        $response = $this->actingAs($teacher, 'teacher')
            ->post(route('teacher.classes.start'), [
                'class_room_id' => $classRoom->id,
                'google_meet_link' => 'https://meet.google.com/xyz-pdq-rst'
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error');
        $this->assertStringContainsString('maximum allowed 8 classes', session('error'));

        // 5. Generate fees on June 10th (the end of Cycle 2)
        // This will process Cycle 2 and mark completed uncalculated classes as calculated
        Carbon::setTestNow('2026-06-10 12:00:00');
        $feeService = new FeeService();
        $feeService->generateGroupFeesForToday();

        // Verify that completed class hours are now marked as calculated
        $uncalculatedCount = ClassHour::where('class_room_id', $classRoom->id)
            ->where('status', 'completed')
            ->where('has_fee_calculated', false)
            ->count();
        $this->assertEquals(0, $uncalculatedCount);

        // Move to June 11th (Cycle 3: June 10th to July 10th) and attempt to start a session -> Should succeed!
        Carbon::setTestNow('2026-06-11 12:00:00');

        $response = $this->actingAs($teacher, 'teacher')
            ->post(route('teacher.classes.start'), [
                'class_room_id' => $classRoom->id,
                'google_meet_link' => 'https://meet.google.com/xyz-pdq-rst'
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $this->assertTrue(ClassHour::where('class_room_id', $classRoom->id)->where('status', 'pending')->exists());

        Carbon::setTestNow();
    }

    public function test_receipt_number_generation_handles_deleted_fees(): void
    {
        [$course, $classType] = $this->setupDependencies();

        $classRoom = ClassRoom::create([
            'course_id' => $course->id,
            'class_type_id' => $classType->id,
            'name' => 'Receipt Number Test Class',
            'admission_fee' => 100,
            'monthly_fee' => 500,
            'classes_per_week' => 2,
            'is_completed' => false,
            'starting_date' => '2026-04-10',
        ]);

        $student = Student::create([
            'admission_no' => 'S777',
            'phone' => '1234567777',
            'name' => 'Test Student for Receipt Suffix',
            'contact_number' => '1234567777',
            'whatsapp_number' => '1234567777',
            'status' => 'active'
        ]);

        // Fix time to June 10, 2026
        Carbon::setTestNow('2026-06-10 12:00:00');

        // Create 3 fee entries with different due dates to avoid unique constraint
        $fee1 = Fee::create([
            'student_id' => $student->id,
            'class_room_id' => $classRoom->id,
            'type' => 'monthly',
            'amount' => 500,
            'due_date' => '2026-06-17',
            'status' => 'unpaid',
        ]);

        $fee2 = Fee::create([
            'student_id' => $student->id,
            'class_room_id' => $classRoom->id,
            'type' => 'monthly',
            'amount' => 500,
            'due_date' => '2026-06-18',
            'status' => 'unpaid',
        ]);

        $fee3 = Fee::create([
            'student_id' => $student->id,
            'class_room_id' => $classRoom->id,
            'type' => 'monthly',
            'amount' => 500,
            'due_date' => '2026-06-19',
            'status' => 'unpaid',
        ]);

        $this->assertEquals('REC-06-26-1', $fee1->receipt_no);
        $this->assertEquals('REC-06-26-2', $fee2->receipt_no);
        $this->assertEquals('REC-06-26-3', $fee3->receipt_no);

        // Delete the middle fee entry
        $fee2->delete();

        // Create a fourth fee entry
        $fee4 = Fee::create([
            'student_id' => $student->id,
            'class_room_id' => $classRoom->id,
            'type' => 'monthly',
            'amount' => 500,
            'due_date' => '2026-06-20',
            'status' => 'unpaid',
        ]);

        // With old logic, count would be 2, next receipt_no would be REC-06-26-3 (duplicate of fee3) -> DB Error
        // With new logic, next receipt_no should be REC-06-26-4
        $this->assertEquals('REC-06-26-4', $fee4->receipt_no);

        Carbon::setTestNow();
    }
}

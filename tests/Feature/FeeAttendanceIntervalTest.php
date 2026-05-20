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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class FeeAttendanceIntervalTest extends TestCase
{
    use RefreshDatabase;

    public function test_fee_attendance_interval_calculation(): void
    {
        // 1. Create dependencies
        $category = CourseCategory::where('name', 'school')->first();
        if (!$category) {
            $category = new CourseCategory();
            $category->name = 'school';
            $category->save();
        }
        
        $course = Course::create([
            'category_id' => $category->id,
            'name' => 'English Grammar'
        ]);
        
        $classType = ClassType::where('name', 'individual')->first();
        if (!$classType) {
            $classType = new ClassType();
            $classType->name = 'individual';
            $classType->save();
        }
        
        $classRoom = ClassRoom::create([
            'course_id' => $course->id,
            'class_type_id' => $classType->id,
            'name' => 'English Class 101',
            'admission_fee' => 100,
            'monthly_fee' => 500,
            'classes_per_week' => 2,
        ]);

        $teacher = Teacher::create([
            'admission_no' => 'T101',
            'phone' => '1234567890',
            'name' => 'Mr. Smith',
            'contact_number' => '1234567890',
            'status' => 'active'
        ]);

        $student = Student::create([
            'admission_no' => 'S101',
            'phone' => '0987654321',
            'name' => 'John Doe',
            'contact_number' => '0987654321',
            'whatsapp_number' => '0987654321',
            'status' => 'active'
        ]);

        // 2. Create class hours completed at different times with student attendance
        
        // Cycle 1 class hours (completed before Fee A generation)
        $ch1 = ClassHour::create([
            'class_room_id' => $classRoom->id,
            'teacher_id' => $teacher->id,
            'status' => 'completed',
            'completed_at' => '2026-04-10 10:00:00'
        ]);
        StudentAttendance::create([
            'class_hour_id' => $ch1->id,
            'student_id' => $student->id,
            'is_present' => true
        ]);

        $ch2 = ClassHour::create([
            'class_room_id' => $classRoom->id,
            'teacher_id' => $teacher->id,
            'status' => 'completed',
            'completed_at' => '2026-04-15 10:00:00'
        ]);
        StudentAttendance::create([
            'class_hour_id' => $ch2->id,
            'student_id' => $student->id,
            'is_present' => true
        ]);

        $ch3 = ClassHour::create([
            'class_room_id' => $classRoom->id,
            'teacher_id' => $teacher->id,
            'status' => 'completed',
            'completed_at' => '2026-04-20 10:00:00'
        ]);
        StudentAttendance::create([
            'class_hour_id' => $ch3->id,
            'student_id' => $student->id,
            'is_present' => false
        ]);

        // Cycle 2 class hours (completed after Fee A but before Fee B generation)
        $ch4 = ClassHour::create([
            'class_room_id' => $classRoom->id,
            'teacher_id' => $teacher->id,
            'status' => 'completed',
            'completed_at' => '2026-05-10 10:00:00'
        ]);
        StudentAttendance::create([
            'class_hour_id' => $ch4->id,
            'student_id' => $student->id,
            'is_present' => true
        ]);

        $ch5 = ClassHour::create([
            'class_room_id' => $classRoom->id,
            'teacher_id' => $teacher->id,
            'status' => 'completed',
            'completed_at' => '2026-05-15 10:00:00'
        ]);
        StudentAttendance::create([
            'class_hour_id' => $ch5->id,
            'student_id' => $student->id,
            'is_present' => true
        ]);

        // 3. Create monthly fees with custom created_at values
        // Fee A (first monthly fee, generated on 2026-04-25)
        $feeA = new Fee();
        $feeA->student_id = $student->id;
        $feeA->class_room_id = $classRoom->id;
        $feeA->type = 'monthly';
        $feeA->amount = 450;
        $feeA->due_date = '2026-05-02';
        $feeA->status = 'unpaid';
        $feeA->timestamps = false; // Disable timestamps for custom created_at
        $feeA->created_at = '2026-04-25 10:00:00';
        $feeA->updated_at = '2026-04-25 10:00:00';
        $feeA->save();

        // Fee B (second monthly fee, generated on 2026-05-20)
        $feeB = new Fee();
        $feeB->student_id = $student->id;
        $feeB->class_room_id = $classRoom->id;
        $feeB->type = 'monthly';
        $feeB->amount = 450;
        $feeB->due_date = '2026-05-27';
        $feeB->status = 'unpaid';
        $feeB->timestamps = false; // Disable timestamps for custom created_at
        $feeB->created_at = '2026-05-20 10:00:00';
        $feeB->updated_at = '2026-05-20 10:00:00';
        $feeB->save();

        // 4. Render the Blade component and verify calculations
        $fees = Fee::with(['student', 'classRoom'])->paginate(10);

        $html = Blade::render('
            <x-fees.index
                :fees="$fees"
                tab="unpaid"
                classRoomSearchUrl="/search"
                selectedClassName=""
                isExport="false"
                isAction="true"
                filterRoute="/filter"
                routeTemplateUnPaid="/unpaid"
                routeTemplateOverdue="/overdue"
                routeTemplatePaid="/paid"
                totalAmount="900"
                isFiltered="false"
            />
            @yield("content")
        ', ['fees' => $fees]);

        // Clean up the html whitespaces
        $normalizedHtml = preg_replace('/\s+/', ' ', $html);

        // Check outputs
        // Fee A: expect present = 2, total = 3, percent = 67%
        $this->assertTrue(str_contains($normalizedHtml, '2/3 (67%)'), "HTML did not contain '2/3 (67%)' for first fee");

        // Fee B: expect present = 2, total = 2, percent = 100%
        $this->assertTrue(str_contains($normalizedHtml, '2/2 (100%)'), "HTML did not contain '2/2 (100%)' for second fee");
    }
}

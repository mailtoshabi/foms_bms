<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\ClassRoom;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\Country;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\ClassType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeworkSubmissionFormatTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_and_teacher_can_view_homework_submission_without_errors(): void
    {
        $country = Country::forceCreate([
            'name' => 'United States',
            'code' => '1',
        ]);

        $student = Student::forceCreate([
            'admission_no' => 'STU001',
            'country_id' => $country->id,
            'name' => 'John Student',
            'email' => 'student@example.com',
            'contact_number' => '1234567890',
            'whatsapp_number' => '11234567890',
            'phone' => '1234567890',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);

        $teacher = Teacher::forceCreate([
            'name' => 'Teacher Jane',
            'email' => 'teacher@example.com',
            'contact_number' => '9876543210',
            'whatsapp_number' => '19876543210',
            'phone' => '9876543210',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);

        $category = CourseCategory::firstOrCreate(['name' => 'school']);
        
        $course = Course::forceCreate([
            'category_id' => $category->id,
            'name' => 'English Class'
        ]);
        
        $groupType = ClassType::firstOrCreate(['name' => 'group']);

        $classRoom = ClassRoom::forceCreate([
            'course_id' => $course->id,
            'class_type_id' => $groupType->id,
            'name' => 'Class A',
            'is_completed' => false,
        ]);

        // Enroll student in class room
        $student->class_rooms()->attach($classRoom->id, ['assigned_date' => now()]);

        // Attach teacher to class room
        $teacher->classRooms()->attach($classRoom->id, ['hourly_wage' => 50, 'assigned_at' => now()]);

        $homework = Homework::forceCreate([
            'class_room_id' => $classRoom->id,
            'teacher_id' => $teacher->id,
            'title' => 'Grammar Homework',
            'content' => 'Complete chapter 1.'
        ]);

        $submission = HomeworkSubmission::forceCreate([
            'homework_id' => $homework->id,
            'student_id' => $student->id,
            'submitted_text' => 'My homework answers.',
        ]);

        // Disable CSRF token validation just in case
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        // 1. Verify Student View
        $response = $this->actingAs($student, 'student')
            ->get(route('student.homeworks.show', encrypt($homework->id)));

        $response->assertStatus(200);
        $response->assertViewHas('homework');
        $response->assertViewHas('submission');
        $response->assertSee('Pending Evaluation');
        $response->assertSee('My homework answers.');

        // 2. Verify Teacher View
        $response = $this->actingAs($teacher, 'teacher')
            ->get(route('teacher.homeworks.show', encrypt($homework->id)));

        $response->assertStatus(200);
        $response->assertViewHas('homework');
        $response->assertViewHas('submissions');
        $response->assertSee('John Student');
        $response->assertSee('Pending Evaluation');
    }
}

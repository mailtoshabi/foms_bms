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
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndividualFeeGenerationTest extends TestCase
{
    use RefreshDatabase;

    private function setupDependencies(): array
    {
        $category = CourseCategory::firstOrCreate(['name' => 'school']);
        
        $course = Course::create([
            'category_id' => $category->id,
            'name' => 'Individual English Class'
        ]);
        
        $classType = ClassType::firstOrCreate(['name' => 'individual']);

        return [$course, $classType];
    }

    public function test_individual_class_fee_generation_on_completing_cycle(): void
    {
        [$course, $classType] = $this->setupDependencies();

        $classRoom = ClassRoom::create([
            'course_id' => $course->id,
            'class_type_id' => $classType->id,
            'name' => 'Individual Class A',
            'admission_fee' => 100,
            'monthly_fee' => 600,
            'classes_per_week' => 1,
            'is_completed' => false,
        ]);

        $activeStudent = Student::create([
            'admission_no' => 'S201',
            'phone' => '1234567891',
            'name' => 'Active Student',
            'contact_number' => '1234567891',
            'whatsapp_number' => '1234567891',
            'status' => 'active'
        ]);

        $inactiveStudent = Student::create([
            'admission_no' => 'S202',
            'phone' => '1234567892',
            'name' => 'Inactive Student',
            'contact_number' => '1234567892',
            'whatsapp_number' => '1234567892',
            'status' => 'inactive'
        ]);

        $exemptedStudent = Student::create([
            'admission_no' => 'S203',
            'phone' => '1234567893',
            'name' => 'Exempted Student',
            'contact_number' => '1234567893',
            'whatsapp_number' => '1234567893',
            'status' => 'active',
            'is_monthly_fee_exempted' => true
        ]);

        $classRoom->students()->attach([
            $activeStudent->id,
            $inactiveStudent->id,
            $exemptedStudent->id
        ]);

        $teacher = Teacher::create([
            'admission_no' => 'T201',
            'phone' => '0987654321',
            'name' => 'Teacher One',
            'contact_number' => '0987654321',
            'status' => 'active'
        ]);

        $classRoom->teachers()->attach($teacher->id, [
            'hourly_wage' => 50,
            'assigned_at' => now()
        ]);

        // Required classes to trigger billing = classes_per_week (1) * 4 = 4 classes.
        // Let's pre-create 3 completed and calculated-free class hours
        for ($i = 0; $i < 3; $i++) {
            ClassHour::create([
                'class_room_id' => $classRoom->id,
                'teacher_id' => $teacher->id,
                'status' => 'completed',
                'has_fee_calculated' => false,
                'completed_at' => now()->subDays(3 - $i)
            ]);
        }

        // Now create the 4th class hour which will trigger the completion
        $triggeringClassHour = ClassHour::create([
            'class_room_id' => $classRoom->id,
            'teacher_id' => $teacher->id,
            'status' => 'pending',
            'has_fee_calculated' => false,
            'join_student_at' => now(), // Required to complete
        ]);

        // Perform HTTP POST to complete the 4th class hour
        $response = $this->actingAs($teacher, 'teacher')
            ->post(route('teacher.class-hours.complete', $triggeringClassHour->id), [
                'attendance' => [
                    $activeStudent->id => true,
                    $inactiveStudent->id => false,
                    $exemptedStudent->id => true
                ]
            ]);

        $response->assertStatus(302); // Redirect back on success

        // Assert fee generation
        // Active student must have a monthly fee created
        $activeFee = Fee::where('student_id', $activeStudent->id)
            ->where('class_room_id', $classRoom->id)
            ->where('type', 'monthly')
            ->first();
        $this->assertNotNull($activeFee);
        $this->assertEquals(600, $activeFee->amount);

        // Inactive student must NOT have a monthly fee
        $inactiveFeeExists = Fee::where('student_id', $inactiveStudent->id)
            ->where('class_room_id', $classRoom->id)
            ->where('type', 'monthly')
            ->exists();
        $this->assertFalse($inactiveFeeExists);

        // Exempted student must NOT have a monthly fee
        $exemptedFeeExists = Fee::where('student_id', $exemptedStudent->id)
            ->where('class_room_id', $classRoom->id)
            ->where('type', 'monthly')
            ->exists();
        $this->assertFalse($exemptedFeeExists);

        // Assert all 4 class hours are updated to has_fee_calculated = true
        $this->assertEquals(4, ClassHour::where('class_room_id', $classRoom->id)
            ->where('status', 'completed')
            ->where('has_fee_calculated', true)
            ->count());
    }
}

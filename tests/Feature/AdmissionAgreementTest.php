<?php

namespace Tests\Feature;

use App\Models\TeacherLead;
use App\Models\Teacher;
use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdmissionAgreementTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_admission_requires_agreed_rules_checkbox(): void
    {
        Storage::fake('public');

        // Create country
        $country = Country::forceCreate([
            'name' => 'India',
            'code' => '91',
        ]);

        // Create lead
        $lead = TeacherLead::create([
            'name' => 'John Doe',
            'contact_number' => '9876543210',
            'email' => 'john@example.com',
            'country_id' => $country->id,
            'form_token' => 'test-token',
        ]);

        // Submit form without agreed_rules
        $response = $this->post(route('admission.submit', ['teacher', 'test-token']), [
            'name' => 'John Doe',
            'contact_number' => '9876543210',
            'photo' => UploadedFile::fake()->image('profile.jpg'),
            'id_proof' => UploadedFile::fake()->create('id.pdf', 100),
        ]);

        // Assert validation error for agreed_rules
        $response->assertSessionHasErrors('agreed_rules');
        $this->assertEquals(0, Teacher::count());
    }

    public function test_teacher_admission_saves_agreed_rules_when_checked(): void
    {
        Storage::fake('public');

        // Create country
        $country = Country::forceCreate([
            'name' => 'India',
            'code' => '91',
        ]);

        // Create lead
        $lead = TeacherLead::create([
            'name' => 'John Doe',
            'contact_number' => '9876543210',
            'email' => 'john@example.com',
            'country_id' => $country->id,
            'form_token' => 'test-token',
        ]);

        // Submit form with agreed_rules
        $response = $this->post(route('admission.submit', ['teacher', 'test-token']), [
            'name' => 'John Doe',
            'contact_number' => '9876543210',
            'photo' => UploadedFile::fake()->image('profile.jpg'),
            'id_proof' => UploadedFile::fake()->create('id.pdf', 100),
            'agreed_rules' => '1',
        ]);

        // Assert success and database record
        $response->assertStatus(200); // Renders admission.success view
        $this->assertEquals(1, Teacher::count());
        $teacher = Teacher::first();
        $this->assertEquals(1, $teacher->agreed_rules);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Student;
use App\Models\Staff;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentSiblingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create standard roles & configurations if needed
        \App\Models\Role::forceCreate([
            'id' => 1,
            'name' => 'enrolment department',
            'is_active' => true,
        ]);
        \App\Models\Utility::firstOrCreate(['key' => 'id_enrolment_dept', 'value' => '1', 'is_visible' => true]);

        \App\Models\Role::forceCreate([
            'id' => 5,
            'name' => 'operation department',
            'is_active' => true,
        ]);
        \App\Models\Utility::firstOrCreate(['key' => 'id_operation_dept', 'value' => '5', 'is_visible' => true]);
    }

    public function test_can_register_sibling_with_same_phone_and_different_password(): void
    {
        $country = new Country();
        $country->name = 'United States';
        $country->code = '1';
        $country->save();

        // Create original student
        $student1 = Student::create([
            'admission_no' => 'S001',
            'country_id' => $country->id,
            'phone' => '1234567890',
            'password' => bcrypt('password123'),
            'name' => 'John Doe',
            'contact_number' => '1234567890',
            'whatsapp_number' => '11234567890',
            'status' => 'active',
            'is_blocked' => false
        ]);

        // Create staff user to submit sibling registration
        $staff = Staff::create([
            'name' => 'Staff Member',
            'phone' => '9876543210',
            'email' => 'staff@example.com',
            'password' => bcrypt('password'),
        ]);
        $staff->roles()->attach(1); // Enrolment department

        // 1. First test password validation: sibling cannot have the same password
        $response = $this->actingAs($staff, 'staff')
            ->post(route('staff.students.store'), [
                'name' => 'Jane Doe',
                'admission_no' => 'S002',
                'country_id' => $country->id,
                'phone' => '1234567890',
                'contact_number' => '1234567890',
                'whatsapp_number' => '11234567890',
                'password' => 'password123', // same password!
                'password_confirmation' => 'password123',
                'status' => 'active',
                'relative_of' => encrypt($student1->id),
                'selected_days' => ['Monday']
            ]);

        $response->assertSessionHasErrors('password');
        $this->assertEquals(1, Student::count());

        // 2. Register sibling with a different password
        $response = $this->actingAs($staff, 'staff')
            ->post(route('staff.students.store'), [
                'name' => 'Jane Doe',
                'admission_no' => 'S002',
                'country_id' => $country->id,
                'phone' => '1234567890',
                'contact_number' => '1234567890',
                'whatsapp_number' => '11234567890',
                'password' => 'newpassword456', // different password
                'password_confirmation' => 'newpassword456',
                'status' => 'active',
                'relative_of' => encrypt($student1->id),
                'selected_days' => ['Monday']
            ]);

        $response->assertRedirect();
        $this->assertEquals(2, Student::count());

        $student2 = Student::orderBy('id', 'desc')->first();
        $this->assertNotNull($student2);

        // Verify the bidirectional relation was established
        $this->assertTrue($student1->relatedStudents->contains($student2->id));
        $this->assertTrue($student2->relatedStudents->contains($student1->id));
    }

    public function test_transitive_relations_clique(): void
    {
        $country = new Country();
        $country->name = 'United States';
        $country->code = '1';
        $country->save();

        $student1 = Student::create([
            'admission_no' => 'S001',
            'country_id' => $country->id,
            'phone' => '1234567890',
            'password' => bcrypt('password123'),
            'name' => 'John Doe',
            'contact_number' => '1234567890',
            'whatsapp_number' => '11234567890',
            'status' => 'active'
        ]);

        $student2 = Student::create([
            'admission_no' => 'S002',
            'country_id' => $country->id,
            'phone' => '1234567890',
            'password' => bcrypt('password456'),
            'name' => 'Jane Doe',
            'contact_number' => '1234567890',
            'whatsapp_number' => '11234567890',
            'status' => 'active'
        ]);

        // Manually link 1 and 2
        $student1->relatedStudents()->attach($student2->id);
        $student2->relatedStudents()->attach($student1->id);

        $staff = Staff::create([
            'name' => 'Staff Member',
            'phone' => '9876543210',
            'email' => 'staff@example.com',
            'password' => bcrypt('password'),
        ]);
        $staff->roles()->attach(1);

        // Add a third sibling (student3) relative of student2
        $response = $this->actingAs($staff, 'staff')
            ->post(route('staff.students.store'), [
                'name' => 'Jack Doe',
                'admission_no' => 'S003',
                'country_id' => $country->id,
                'phone' => '1234567890',
                'contact_number' => '1234567890',
                'whatsapp_number' => '11234567890',
                'password' => 'password789',
                'password_confirmation' => 'password789',
                'status' => 'active',
                'relative_of' => encrypt($student2->id),
                'selected_days' => ['Monday']
            ]);

        $response->assertRedirect();
        
        $student3 = Student::orderBy('id', 'desc')->first();
        $this->assertNotNull($student3);

        // Verify clique: 1, 2, 3 should all be linked to each other
        $this->assertTrue($student1->relatedStudents->contains($student3->id));
        $this->assertTrue($student2->relatedStudents->contains($student3->id));
        $this->assertTrue($student3->relatedStudents->contains($student1->id));
        $this->assertTrue($student3->relatedStudents->contains($student2->id));
    }

    public function test_login_authenticates_correct_account_by_password(): void
    {
        $country = new Country();
        $country->name = 'United States';
        $country->code = '1';
        $country->save();

        $student1 = Student::create([
            'admission_no' => 'S001',
            'country_id' => $country->id,
            'phone' => '1234567890',
            'password' => bcrypt('password123'),
            'name' => 'John Doe',
            'contact_number' => '1234567890',
            'whatsapp_number' => '11234567890',
            'status' => 'active'
        ]);

        $student2 = Student::create([
            'admission_no' => 'S002',
            'country_id' => $country->id,
            'phone' => '1234567890',
            'password' => bcrypt('password456'),
            'name' => 'Jane Doe',
            'contact_number' => '1234567890',
            'whatsapp_number' => '11234567890',
            'status' => 'active'
        ]);

        // Attempt login for Jane Doe (password456)
        $response = $this->post(route('student.login.submit'), [
            'country_id' => $country->id,
            'phone' => '1234567890',
            'password' => 'password456'
        ]);

        $response->assertRedirect(route('student.dashboard'));
        $this->assertTrue(auth('student')->check());
        $this->assertEquals($student2->id, auth('student')->id());
    }

    public function test_student_can_switch_account_without_password(): void
    {
        $country = new Country();
        $country->name = 'United States';
        $country->code = '1';
        $country->save();

        $student1 = Student::create([
            'admission_no' => 'S001',
            'country_id' => $country->id,
            'phone' => '1234567890',
            'password' => bcrypt('password123'),
            'name' => 'John Doe',
            'contact_number' => '1234567890',
            'whatsapp_number' => '11234567890',
            'status' => 'active'
        ]);

        $student2 = Student::create([
            'admission_no' => 'S002',
            'country_id' => $country->id,
            'phone' => '1234567890',
            'password' => bcrypt('password456'),
            'name' => 'Jane Doe',
            'contact_number' => '1234567890',
            'whatsapp_number' => '11234567890',
            'status' => 'active'
        ]);

        $student1->relatedStudents()->attach($student2->id);
        $student2->relatedStudents()->attach($student1->id);

        // Authenticate as John Doe
        $this->actingAs($student1, 'student');

        // Post switch request to switch to Jane Doe (student2)
        $response = $this->post(route('student.switch', encrypt($student2->id)));

        $response->assertRedirect(route('student.dashboard'));
        $this->assertEquals($student2->id, auth('student')->id());
    }

    public function test_cannot_switch_to_non_related_student(): void
    {
        $country = new Country();
        $country->name = 'United States';
        $country->code = '1';
        $country->save();

        $student1 = Student::create([
            'admission_no' => 'S001',
            'country_id' => $country->id,
            'phone' => '1234567890',
            'password' => bcrypt('password123'),
            'name' => 'John Doe',
            'contact_number' => '1234567890',
            'whatsapp_number' => '11234567890',
            'status' => 'active'
        ]);

        $unrelatedStudent = Student::create([
            'admission_no' => 'S999',
            'country_id' => $country->id,
            'phone' => '9999999999',
            'password' => bcrypt('password999'),
            'name' => 'Unrelated',
            'contact_number' => '9999999999',
            'whatsapp_number' => '19999999999',
            'status' => 'active'
        ]);

        $this->actingAs($student1, 'student');

        $response = $this->post(route('student.switch', encrypt($unrelatedStudent->id)));

        // Should return 403 Forbidden or similar abort code
        $response->assertStatus(403);
        $this->assertEquals($student1->id, auth('student')->id());
    }

    public function test_staff_and_admin_can_unlink_relationship(): void
    {
        $country = new Country();
        $country->name = 'United States';
        $country->code = '1';
        $country->save();

        $student1 = Student::create([
            'admission_no' => 'S001',
            'country_id' => $country->id,
            'phone' => '1234567890',
            'password' => bcrypt('password123'),
            'name' => 'John Doe',
            'contact_number' => '1234567890',
            'whatsapp_number' => '11234567890',
            'status' => 'active'
        ]);

        $student2 = Student::create([
            'admission_no' => 'S002',
            'country_id' => $country->id,
            'phone' => '1234567890',
            'password' => bcrypt('password456'),
            'name' => 'Jane Doe',
            'contact_number' => '1234567890',
            'whatsapp_number' => '11234567890',
            'status' => 'active'
        ]);

        $student1->relatedStudents()->attach($student2->id);
        $student2->relatedStudents()->attach($student1->id);

        $staff = Staff::create([
            'name' => 'Staff Member',
            'phone' => '9876543210',
            'email' => 'staff@example.com',
            'password' => bcrypt('password'),
        ]);
        $staff->roles()->attach(1);

        $admin = Admin::create([
            'name' => 'Admin Member',
            'phone' => '9999999999',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // 1. Staff destroys relationship
        $response = $this->actingAs($staff, 'staff')
            ->delete(route('staff.students.relations.destroy', [
                'id' => encrypt($student1->id),
                'related_id' => encrypt($student2->id)
            ]), [
                'new_contact_number' => '9999999999'
            ]);

        $response->assertRedirect();
        
        // Relationship should be bidirectionally removed
        $this->assertFalse($student1->relatedStudents()->exists());
        $this->assertFalse($student2->relatedStudents()->exists());

        // Student2 details should be updated to new phone number and password reset to it
        $student2 = $student2->refresh();
        $this->assertEquals('9999999999', $student2->phone);
        $this->assertEquals('9999999999', $student2->contact_number);
        $this->assertTrue(Hash::check('9999999999', $student2->password));

        // Relink for Admin check
        $student1->relatedStudents()->attach($student2->id);
        $student2->relatedStudents()->attach($student1->id);

        $response = $this->actingAs($admin, 'admin')
            ->delete(route('admin.reports.students.relations.destroy', [
                'id' => encrypt($student1->id),
                'related_id' => encrypt($student2->id)
            ]), [
                'new_contact_number' => '8888888888'
            ]);

        $response->assertRedirect();
        $this->assertFalse($student1->relatedStudents()->exists());
        $this->assertFalse($student2->relatedStudents()->exists());

        // Student2 details should be updated to admin's provided new number
        $student2 = $student2->refresh();
        $this->assertEquals('8888888888', $student2->phone);
        $this->assertEquals('8888888888', $student2->contact_number);
        $this->assertTrue(Hash::check('8888888888', $student2->password));
    }

    public function test_edit_sibling_allows_shared_phone_number(): void
    {
        $country = new Country();
        $country->name = 'United States';
        $country->code = '1';
        $country->save();

        $student1 = Student::create([
            'admission_no' => 'S001',
            'country_id' => $country->id,
            'phone' => '1234567890',
            'password' => bcrypt('password123'),
            'name' => 'John Doe',
            'contact_number' => '1234567890',
            'whatsapp_number' => '11234567890',
            'status' => 'active'
        ]);

        $student2 = Student::create([
            'admission_no' => 'S002',
            'country_id' => $country->id,
            'phone' => '1234567890',
            'password' => bcrypt('password456'),
            'name' => 'Jane Doe',
            'contact_number' => '1234567890',
            'whatsapp_number' => '11234567890',
            'status' => 'active'
        ]);

        $student1->relatedStudents()->attach($student2->id);
        $student2->relatedStudents()->attach($student1->id);

        $staff = Staff::create([
            'name' => 'Staff Member',
            'phone' => '9876543210',
            'email' => 'staff@example.com',
            'password' => bcrypt('password'),
        ]);
        $staff->roles()->attach(1);

        // Edit Jane Doe, change shared phone to 9999999999, update name.
        // This should pass and also sync the new phone number to student1.
        $response = $this->actingAs($staff, 'staff')
            ->put(route('staff.students.update', encrypt($student2->id)), [
                'name' => 'Jane Doe Updated',
                'country_id' => $country->id,
                'contact_number' => '9999999999',
                'phone' => '9999999999',
                'email' => 'jane@example.com',
                'selected_days' => ['Monday']
            ]);

        $response->assertRedirect(route('staff.students.index'));
        $this->assertEquals('Jane Doe Updated', $student2->refresh()->name);
        $this->assertEquals('9999999999', $student2->phone);
        $this->assertEquals('9999999999', $student1->refresh()->phone);
    }

    public function test_edit_sibling_blocks_duplicate_password_with_another_sibling(): void
    {
        $country = new Country();
        $country->name = 'United States';
        $country->code = '1';
        $country->save();

        $student1 = Student::create([
            'admission_no' => 'S001',
            'country_id' => $country->id,
            'phone' => '1234567890',
            'password' => bcrypt('password123'),
            'name' => 'John Doe',
            'contact_number' => '1234567890',
            'whatsapp_number' => '11234567890',
            'status' => 'active'
        ]);

        $student2 = Student::create([
            'admission_no' => 'S002',
            'country_id' => $country->id,
            'phone' => '1234567890',
            'password' => bcrypt('password456'),
            'name' => 'Jane Doe',
            'contact_number' => '1234567890',
            'whatsapp_number' => '11234567890',
            'status' => 'active'
        ]);

        $student1->relatedStudents()->attach($student2->id);
        $student2->relatedStudents()->attach($student1->id);

        $staff = Staff::create([
            'name' => 'Staff Member',
            'phone' => '9876543210',
            'email' => 'staff@example.com',
            'password' => bcrypt('password'),
        ]);
        $staff->roles()->attach(1);

        // Edit Jane Doe, try to change password to John Doe's password (password123)
        $response = $this->actingAs($staff, 'staff')
            ->put(route('staff.students.update', encrypt($student2->id)), [
                'name' => 'Jane Doe',
                'country_id' => $country->id,
                'contact_number' => '1234567890',
                'phone' => '1234567890',
                'password' => 'password123', // same as student1!
                'password_confirmation' => 'password123',
                'email' => 'jane@example.com',
                'selected_days' => ['Monday']
            ]);

        $response->assertSessionHasErrors('password');
        $this->assertTrue(Hash::check('password456', $student2->refresh()->password));
    }

    public function test_can_link_existing_student_as_sibling(): void
    {
        $country = new Country();
        $country->name = 'United States';
        $country->code = '1';
        $country->save();

        $student1 = Student::create([
            'admission_no' => 'S001',
            'country_id' => $country->id,
            'phone' => '1234567890',
            'password' => bcrypt('password123'),
            'name' => 'John Doe',
            'contact_number' => '1234567890',
            'whatsapp_number' => '11234567890',
            'status' => 'active'
        ]);

        $student2 = Student::create([
            'admission_no' => 'S002',
            'country_id' => $country->id,
            'phone' => '9999999999',
            'password' => bcrypt('password456'),
            'name' => 'Jane Doe',
            'contact_number' => '9999999999',
            'whatsapp_number' => '19999999999',
            'status' => 'active'
        ]);

        $staff = Staff::create([
            'name' => 'Staff Member',
            'phone' => '9876543210',
            'email' => 'staff@example.com',
            'password' => bcrypt('password'),
        ]);
        $staff->roles()->attach(1);

        $response = $this->actingAs($staff, 'staff')
            ->post(route('staff.students.relations.store', encrypt($student1->id)), [
                'related_student_id' => $student2->id
            ]);

        $response->assertRedirect();
        
        // Verify relationship is linked
        $this->assertTrue($student1->relatedStudents->contains($student2->id));
        $this->assertTrue($student2->relatedStudents->contains($student1->id));

        // Verify contact details of student2 are synced to student1
        $student2 = $student2->refresh();
        $this->assertEquals('1234567890', $student2->phone);
        $this->assertEquals('1234567890', $student2->contact_number);
        $this->assertEquals('11234567890', $student2->whatsapp_number);
        $this->assertEquals($student1->country_id, $student2->country_id);
    }
}

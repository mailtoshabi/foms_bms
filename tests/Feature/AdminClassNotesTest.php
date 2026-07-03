<?php

namespace Tests\Feature;

use App\Models\ClassNote;
use App\Models\ClassNoteFile;
use App\Models\ClassRoom;
use App\Models\Teacher;
use App\Models\Admin;
use App\Models\Country;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\ClassType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminClassNotesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_and_filter_class_notes(): void
    {
        // 1. Setup Admin, Teacher, ClassRoom, Notes
        $admin = Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone' => '9999999999',
            'password' => bcrypt('password'),
        ]);

        $country = Country::create([
            'name' => 'India',
            'code' => '91',
        ]);

        $teacher1 = Teacher::create([
            'name' => 'John Teacher',
            'phone' => '1234567890',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'status' => 'active',
            'country_id' => $country->id,
            'contact_number' => '1234567890',
            'whatsapp_number' => '911234567890',
        ]);

        $teacher2 = Teacher::create([
            'name' => 'Jane Teacher',
            'phone' => '1234567891',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
            'status' => 'active',
            'country_id' => $country->id,
            'contact_number' => '1234567891',
            'whatsapp_number' => '911234567891',
        ]);

        $category = CourseCategory::firstOrCreate(['name' => 'school']);
        $course = Course::create([
            'category_id' => $category->id,
            'name' => 'Grammar Class'
        ]);
        $groupType = ClassType::firstOrCreate(['name' => 'group']);

        $classRoom1 = ClassRoom::create([
            'course_id' => $course->id,
            'class_type_id' => $groupType->id,
            'name' => 'Class A',
            'status' => 'active',
        ]);

        $classRoom2 = ClassRoom::create([
            'course_id' => $course->id,
            'class_type_id' => $groupType->id,
            'name' => 'Class B',
            'status' => 'active',
        ]);

        // Note 1: Teacher 1, Class 1, Title 'Algebra'
        $note1 = ClassNote::create([
            'teacher_id' => $teacher1->id,
            'class_room_id' => $classRoom1->id,
            'title' => 'Algebra Rules',
            'content' => 'Algebra rules here.',
        ]);

        // Note 2: Teacher 2, Class 2, Title 'Geometry'
        $note2 = ClassNote::create([
            'teacher_id' => $teacher2->id,
            'class_room_id' => $classRoom2->id,
            'title' => 'Geometry Rules',
            'content' => 'Geometry rules here.',
        ]);

        // 2. Index listing without filters
        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.class-notes.index'));

        $response->assertStatus(200);
        $response->assertViewHas('notes');
        
        // 3. Filter by title
        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.class-notes.index', ['title' => 'Algebra']));
        $response->assertStatus(200);
        $notes = $response->viewData('notes');
        $this->assertCount(1, $notes);
        $this->assertEquals('Algebra Rules', $notes->first()->title);

        // 4. Filter by class_room_id
        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.class-notes.index', ['class_room_id' => $classRoom2->id]));
        $response->assertStatus(200);
        $notes = $response->viewData('notes');
        $this->assertCount(1, $notes);
        $this->assertEquals('Geometry Rules', $notes->first()->title);

        // 5. Filter by teacher_id
        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.class-notes.index', ['teacher_id' => $teacher1->id]));
        $response->assertStatus(200);
        $notes = $response->viewData('notes');
        $this->assertCount(1, $notes);
        $this->assertEquals('Algebra Rules', $notes->first()->title);
    }

    public function test_admin_can_view_note_and_delete_with_files(): void
    {
        Storage::fake('public');

        $admin = Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone' => '9999999999',
            'password' => bcrypt('password'),
        ]);

        $country = Country::create([
            'name' => 'India',
            'code' => '91',
        ]);

        $teacher = Teacher::create([
            'name' => 'John Teacher',
            'phone' => '1234567890',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'status' => 'active',
            'country_id' => $country->id,
            'contact_number' => '1234567890',
            'whatsapp_number' => '911234567890',
        ]);

        $category = CourseCategory::firstOrCreate(['name' => 'school']);
        $course = Course::create([
            'category_id' => $category->id,
            'name' => 'Grammar Class'
        ]);
        $groupType = ClassType::firstOrCreate(['name' => 'group']);

        $classRoom = ClassRoom::create([
            'course_id' => $course->id,
            'class_type_id' => $groupType->id,
            'name' => 'Class A',
            'status' => 'active',
        ]);

        $note = ClassNote::create([
            'teacher_id' => $teacher->id,
            'class_room_id' => $classRoom->id,
            'title' => 'Math Note',
            'content' => 'Some content.',
        ]);

        // Upload fake file
        $file = UploadedFile::fake()->create('document.pdf', 500);
        $filePath = $file->store('class_notes', 'public');

        $noteFile = ClassNoteFile::create([
            'class_note_id' => $note->id,
            'file_name' => 'document.pdf',
            'file_path' => $filePath,
            'file_type' => 'pdf',
            'file_size' => 500 * 1024,
        ]);

        // 1. Show details
        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.class-notes.show', encrypt($note->id)));
        $response->assertStatus(200);

        // 2. Download file
        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.class-notes.file.download', encrypt($noteFile->id)));
        $response->assertStatus(200);

        // 3. Delete note
        $response = $this->actingAs($admin, 'admin')
            ->delete(route('admin.class-notes.destroy', encrypt($note->id)));

        $response->assertRedirect(route('admin.class-notes.index'));
        $this->assertDatabaseMissing('class_notes', ['id' => $note->id]);
        $this->assertDatabaseMissing('class_note_files', ['id' => $noteFile->id]);

        // Verify storage file is deleted
        Storage::disk('public')->assertMissing($filePath);
    }
}

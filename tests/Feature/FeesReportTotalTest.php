<?php

namespace Tests\Feature;

use App\Models\Fee;
use App\Models\FeePayment;
use App\Models\FeeRefund;
use App\Models\Student;
use App\Models\Admin;
use App\Models\Country;
use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\ClassType;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeesReportTotalTest extends TestCase
{
    use RefreshDatabase;

    public function test_fees_report_grand_total_calculates_balance_on_partial_status(): void
    {
        $now = Carbon::now();

        // Create country
        $country = new Country();
        $country->name = 'India';
        $country->code = '91';
        $country->save();

        // Create class room dependencies
        $category = CourseCategory::firstOrCreate(['name' => 'school']);
        $course = Course::create([
            'category_id' => $category->id,
            'name' => 'Grammar Class'
        ]);
        $groupType = ClassType::firstOrCreate(['name' => 'group']);

        $classRoom = ClassRoom::create([
            'course_id' => $course->id,
            'class_type_id' => $groupType->id,
            'name' => 'Grammar Group A',
            'status' => 'active',
        ]);

        // Create students
        $student1 = Student::create([
            'admission_no' => 'S123',
            'name' => 'John Doe',
            'phone' => '1234567890',
            'password' => bcrypt('password'),
            'status' => 'active',
            'country_id' => $country->id,
            'contact_number' => '1234567890',
            'whatsapp_number' => '911234567890',
        ]);

        $student2 = Student::create([
            'admission_no' => 'S124',
            'name' => 'Jane Doe',
            'phone' => '1234567891',
            'password' => bcrypt('password'),
            'status' => 'active',
            'country_id' => $country->id,
            'contact_number' => '1234567891',
            'whatsapp_number' => '911234567891',
        ]);

        $student3 = Student::create([
            'admission_no' => 'S125',
            'name' => 'Jim Doe',
            'phone' => '1234567892',
            'password' => bcrypt('password'),
            'status' => 'active',
            'country_id' => $country->id,
            'contact_number' => '1234567892',
            'whatsapp_number' => '911234567892',
        ]);

        // Fee 1: Partial fee of 1500 (Paid 800, refunded 200, remaining balance 900)
        $feePartial = Fee::create([
            'student_id' => $student1->id,
            'class_room_id' => $classRoom->id,
            'amount' => 1500,
            'due_date' => $now->toDateString(),
            'type' => 'monthly',
            'status' => 'partial',
        ]);

        FeePayment::create([
            'fee_id' => $feePartial->id,
            'paid_amount' => 800,
            'paid_date' => $now->toDateString(),
            'payment_method' => 'gpay',
        ]);

        FeeRefund::create([
            'fee_id' => $feePartial->id,
            'amount' => 200,
            'refund_date' => $now->toDateString(),
            'payment_method' => 'gpay',
        ]);

        // Fee 2: Unpaid fee of 1000
        $feeUnpaid = Fee::create([
            'student_id' => $student2->id,
            'class_room_id' => $classRoom->id,
            'amount' => 1000,
            'due_date' => $now->toDateString(),
            'type' => 'monthly',
            'status' => 'unpaid',
        ]);

        // Fee 3: Paid fee of 600
        $feePaid = Fee::create([
            'student_id' => $student3->id,
            'class_room_id' => $classRoom->id,
            'amount' => 600,
            'due_date' => $now->toDateString(),
            'type' => 'monthly',
            'status' => 'paid',
        ]);

        FeePayment::create([
            'fee_id' => $feePaid->id,
            'paid_amount' => 600,
            'paid_date' => $now->toDateString(),
            'payment_method' => 'gpay',
        ]);

        // Authenticate as Admin
        $admin = Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone' => '9999999999',
            'password' => bcrypt('password'),
        ]);

        // 1. Fetch fees report filtered by status => partial on paid tab
        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.reports.fee', ['tab' => 'paid', 'status' => 'partial']));

        $response->assertStatus(200);
        // Expect totalAmount to be 900 (remaining balance of partial fee: 1500 - (800 - 200))
        $response->assertViewHas('totalAmount', 900.0);

        // 2. Fetch fees report filtered by status => unpaid on unpaid tab
        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.reports.fee', ['tab' => 'unpaid', 'status' => 'unpaid']));

        $response->assertStatus(200);
        // Expect totalAmount to be 1000 (sum of unpaid fees)
        $response->assertViewHas('totalAmount', 1000.0);

        // 3. Fetch fees report filtered by status => paid on paid tab
        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.reports.fee', ['tab' => 'paid', 'status' => 'paid']));

        $response->assertStatus(200);
        // Expect totalAmount to be 600 (sum of paid fees)
        $response->assertViewHas('totalAmount', 600.0);
    }
}

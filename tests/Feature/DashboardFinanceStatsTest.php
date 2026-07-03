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
use App\Models\Staff;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardFinanceStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_correctly_calculates_paid_and_pending_amounts_with_refunds(): void
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

        // 1. Create students
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

        $student4 = Student::create([
            'admission_no' => 'S126',
            'name' => 'Jack Doe',
            'phone' => '1234567893',
            'password' => bcrypt('password'),
            'status' => 'active',
            'country_id' => $country->id,
            'contact_number' => '1234567893',
            'whatsapp_number' => '911234567893',
        ]);

        // 2. Create Fees due this month
        // Fee 1: Unpaid fee of 1000
        $feeUnpaid = Fee::create([
            'student_id' => $student1->id,
            'class_room_id' => $classRoom->id,
            'amount' => 1000,
            'due_date' => $now->toDateString(),
            'type' => 'monthly',
            'status' => 'unpaid',
        ]);

        // Fee 2: Partial fee of 1500 (Paid 800, refunded 200, net paid 600, pending 900)
        $feePartial = Fee::create([
            'student_id' => $student2->id,
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

        // Fee 3: Paid fee of 500 (Paid 500, pending 0)
        $feePaid = Fee::create([
            'student_id' => $student3->id,
            'class_room_id' => $classRoom->id,
            'amount' => 500,
            'due_date' => $now->toDateString(),
            'type' => 'monthly',
            'status' => 'paid',
        ]);

        FeePayment::create([
            'fee_id' => $feePaid->id,
            'paid_amount' => 500,
            'paid_date' => $now->toDateString(),
            'payment_method' => 'gpay',
        ]);

        // Fee 4: Overdue fee (unpaid, due 10 days ago) of 800
        $feeOverdue = Fee::create([
            'student_id' => $student4->id,
            'class_room_id' => $classRoom->id,
            'amount' => 800,
            'due_date' => $now->copy()->subDays(10)->toDateString(),
            'type' => 'monthly',
            'status' => 'unpaid',
        ]);

        // 3. Authenticate as Admin and request Dashboard
        $admin = Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone' => '9999999999',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);

        // Expected monthly paidAmount: 800 (from feePartial) + 500 (from feePaid) - 200 (refund) = 1100
        // Expected monthly pendingAmount (fees due this month): 1000 (from feeUnpaid) + 900 (from feePartial) = 1900
        $response->assertViewHas('paidAmount', 1100.0);
        $response->assertViewHas('pendingAmount', 1900.0);

        // Expected unpaidFeesCount (all unpaid/partial fees): 3 (feeUnpaid, feePartial, feeOverdue)
        // Expected unpaidFeesAmount (net unpaid balance of all unpaid/partial fees): 1000 + 900 + 800 = 2700
        $response->assertViewHas('unpaidFeesCount', 3);
        $response->assertViewHas('unpaidFeesAmount', 2700.0);

        // Expected overdueFeesCount: 1 (feeOverdue)
        // Expected overdueFeesAmount: 800
        $response->assertViewHas('overdueFeesCount', 1);
        $response->assertViewHas('overdueFeesAmount', 800.0);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Fee;
use App\Models\FeePayment;
use App\Models\FeeRefund;
use App\Models\Role;
use App\Models\Staff;
use App\Models\Student;
use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\ClassType;
use App\Models\CourseCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFeeRefundTest extends TestCase
{
    use RefreshDatabase;

    private Staff $staffFinance;
    private Admin $admin;
    private Role $roleFinance;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        $this->roleFinance = Role::forceCreate([
            'id' => 3,
            'name' => 'finance department',
            'is_active' => true,
        ]);

        // Seed utilities
        \App\Models\Utility::create(['key' => 'id_finance_dept', 'value' => '3', 'is_visible' => true]);

        // Create staff users
        $this->staffFinance = Staff::create([
            'name' => 'Finance Staff',
            'email' => 'finance@example.com',
            'phone' => '1234567890',
            'password' => bcrypt('password'),
        ]);
        $this->staffFinance->roles()->attach($this->roleFinance->id);

        // Create admin user
        $this->admin = Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone' => '9999999999',
            'password' => bcrypt('password'),
        ]);
    }

    private function createFee(array $attributes = []): Fee
    {
        $student = Student::create([
            'admission_no' => 'S' . rand(1000, 9999),
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '9876543210',
            'contact_number' => '9876543210',
            'whatsapp_number' => '9876543210',
            'status' => 'active',
            'wallet_balance' => 0.00
        ]);

        $category = CourseCategory::firstOrCreate(['name' => 'school']);
        $course = Course::create([
            'category_id' => $category->id,
            'name' => 'Test Course'
        ]);
        $classType = ClassType::where('name', 'group')->first() ?: ClassType::forceCreate(['name' => 'group']);
        $classRoom = ClassRoom::create([
            'name' => 'Test Class',
            'course_id' => $course->id,
            'class_type_id' => $classType->id,
            'fees_amount' => 1000.00
        ]);

        return Fee::create(array_merge([
            'student_id' => $student->id,
            'class_room_id' => $classRoom->id,
            'amount' => 1000.00,
            'due_date' => now()->addDays(7)->toDateString(),
            'status' => 'unpaid',
            'type' => 'monthly'
        ], $attributes));
    }

    public function test_admin_can_refund_fully_paid_fee_partially(): void
    {
        $fee = $this->createFee(['status' => 'paid']);

        // Record a payment of 1000
        FeePayment::create([
            'fee_id' => $fee->id,
            'paid_amount' => 1000.00,
            'payment_method' => 'cash',
            'paid_date' => now()->toDateString()
        ]);

        // Post a partial refund of 300
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.fees.refund'), [
                'fee_id' => $fee->id,
                'amount' => 300.00,
                'payment_method' => 'cash',
                'refund_date' => now()->toDateString(),
                'notes' => 'Partial refund'
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        // Assert refund was recorded
        $this->assertDatabaseHas('fee_refunds', [
            'fee_id' => $fee->id,
            'amount' => 300.00,
            'payment_method' => 'cash'
        ]);

        // Assert fee status is updated to partial
        $fee->refresh();
        $this->assertEquals('partial', $fee->status);
    }

    public function test_admin_can_refund_fully_paid_fee_fully(): void
    {
        $fee = $this->createFee(['status' => 'paid']);

        // Record payment of 1000
        FeePayment::create([
            'fee_id' => $fee->id,
            'paid_amount' => 1000.00,
            'payment_method' => 'cash',
            'paid_date' => now()->toDateString()
        ]);

        // Post full refund of 1000
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.fees.refund'), [
                'fee_id' => $fee->id,
                'amount' => 1000.00,
                'payment_method' => 'upi',
                'refund_date' => now()->toDateString(),
                'notes' => 'Full refund'
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        // Assert fee status is updated to unpaid
        $fee->refresh();
        $this->assertEquals('unpaid', $fee->status);
    }

    public function test_admin_cannot_refund_more_than_paid_amount(): void
    {
        $fee = $this->createFee(['status' => 'paid']);

        // Record payment of 1000
        FeePayment::create([
            'fee_id' => $fee->id,
            'paid_amount' => 1000.00,
            'payment_method' => 'cash',
            'paid_date' => now()->toDateString()
        ]);

        // Record a prior refund of 400 (Net paid is 600)
        FeeRefund::create([
            'fee_id' => $fee->id,
            'amount' => 400.00,
            'payment_method' => 'cash',
            'refund_date' => now()->toDateString()
        ]);

        // Attempt to refund 700 (which exceeds max refundable of 600)
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.fees.refund'), [
                'fee_id' => $fee->id,
                'amount' => 700.00,
                'payment_method' => 'cash',
                'refund_date' => now()->toDateString(),
                'notes' => 'Should fail'
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error');

        // Verify no new refund record was created
        $this->assertDatabaseMissing('fee_refunds', [
            'amount' => 700.00
        ]);
    }

    public function test_staff_cannot_issue_fee_refunds(): void
    {
        $fee = $this->createFee(['status' => 'paid']);

        // Record payment of 1000
        FeePayment::create([
            'fee_id' => $fee->id,
            'paid_amount' => 1000.00,
            'payment_method' => 'cash',
            'paid_date' => now()->toDateString()
        ]);

        // Attempt refund via staff
        $response = $this->actingAs($this->staffFinance, 'staff')
            ->post(route('admin.fees.refund'), [
                'fee_id' => $fee->id,
                'amount' => 100.00,
                'payment_method' => 'cash',
                'refund_date' => now()->toDateString()
            ]);

        // Should redirect to admin login because it uses admin guard routes
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_cannot_refund_if_last_payment_date_exceeds_two_months(): void
    {
        $fee = $this->createFee(['status' => 'paid']);

        // Record payment that was paid 2 months and 5 days ago
        FeePayment::create([
            'fee_id' => $fee->id,
            'paid_amount' => 1000.00,
            'payment_method' => 'cash',
            'paid_date' => now()->subMonths(2)->subDays(5)->toDateString()
        ]);

        // Attempt to refund
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.fees.refund'), [
                'fee_id' => $fee->id,
                'amount' => 500.00,
                'payment_method' => 'cash',
                'refund_date' => now()->toDateString(),
                'notes' => 'Old payment refund'
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error');

        // Verify no refund record was created
        $this->assertDatabaseMissing('fee_refunds', [
            'amount' => 500.00
        ]);
    }

    public function test_admin_and_staff_can_view_fee_refunds(): void
    {
        $fee = $this->createFee();
        $refund = FeeRefund::create([
            'fee_id' => $fee->id,
            'amount' => 100.00,
            'payment_method' => 'cash',
            'refund_date' => now()->toDateString()
        ]);

        // Get as Admin
        $responseAdmin = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.fees.refunds', $fee->id));
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertJsonFragment(['amount' => 100]);

        // Get as Staff
        $responseStaff = $this->actingAs($this->staffFinance, 'staff')
            ->get(route('staff.fees.refunds', $fee->id));
        $responseStaff->assertStatus(200);
        $responseStaff->assertJsonFragment(['amount' => 100]);
    }
}

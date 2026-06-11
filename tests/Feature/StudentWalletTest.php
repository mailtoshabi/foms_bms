<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use App\Models\ClassType;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Fee;
use App\Models\FeePayment;
use App\Models\Role;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Utility;
use App\Models\WalletTransaction;
use App\Services\FeeService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentWalletTest extends TestCase
{
    use RefreshDatabase;

    private Staff $staffOperations;
    private Staff $staffFinance;
    private Staff $staffEnrolment;
    private Role $roleOperations;
    private Role $roleFinance;
    private Role $roleEnrolment;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        $this->roleEnrolment = Role::forceCreate([
            'id' => 1,
            'name' => 'enrolment department',
            'is_active' => true,
        ]);
        $this->roleFinance = Role::forceCreate([
            'id' => 3,
            'name' => 'finance department',
            'is_active' => true,
        ]);
        $this->roleOperations = Role::forceCreate([
            'id' => 5,
            'name' => 'operation department',
            'is_active' => true,
        ]);

        // Seed utilities
        Utility::create(['key' => 'id_enrolment_dept', 'value' => '1', 'is_visible' => true]);
        Utility::create(['key' => 'id_finance_dept', 'value' => '3', 'is_visible' => true]);
        Utility::create(['key' => 'id_operation_dept', 'value' => '5', 'is_visible' => true]);

        // Create staff users
        $this->staffEnrolment = Staff::create([
            'name' => 'Enrolment Agent',
            'email' => 'enrolment@example.com',
            'phone' => '1111111111',
            'password' => bcrypt('password'),
        ]);
        $this->staffEnrolment->roles()->attach($this->roleEnrolment->id);

        $this->staffFinance = Staff::create([
            'name' => 'Finance Agent',
            'email' => 'finance@example.com',
            'phone' => '2222222222',
            'password' => bcrypt('password'),
        ]);
        $this->staffFinance->roles()->attach($this->roleFinance->id);

        $this->staffOperations = Staff::create([
            'name' => 'Operations Agent',
            'email' => 'operations@example.com',
            'phone' => '3333333333',
            'password' => bcrypt('password'),
        ]);
        $this->staffOperations->roles()->attach($this->roleOperations->id);
    }

    private function createStudent(array $attributes = []): Student
    {
        return Student::create(array_merge([
            'admission_no' => 'S' . rand(1000, 9999),
            'phone' => '1234567890',
            'name' => 'Test Student',
            'contact_number' => '1234567890',
            'whatsapp_number' => '1234567890',
            'status' => 'active',
            'wallet_balance' => 0.00,
            'is_wallet_autopay_enabled' => true,
        ], $attributes));
    }

    private function setupClassDependencies(): array
    {
        $category = CourseCategory::firstOrCreate(['name' => 'school']);
        
        $course = Course::create([
            'category_id' => $category->id,
            'name' => 'Test Group Course'
        ]);
        
        $classType = ClassType::firstOrCreate(['name' => 'group']);

        return [$course, $classType];
    }

    public function test_staff_can_toggle_wallet_autopay(): void
    {
        $student = $this->createStudent(['is_wallet_autopay_enabled' => true]);

        // Access via Enrolment staff (should be authorized)
        $response = $this->actingAs($this->staffEnrolment, 'staff')
            ->post(route('staff.students.wallet.toggle-autopay', ['id' => encrypt($student->id)]));

        $response->assertStatus(302);
        $this->assertFalse($student->refresh()->is_wallet_autopay_enabled);

        // Toggle back via Operations staff (should be authorized)
        $response = $this->actingAs($this->staffOperations, 'staff')
            ->post(route('staff.students.wallet.toggle-autopay', ['id' => encrypt($student->id)]));

        $response->assertStatus(302);
        $this->assertTrue($student->refresh()->is_wallet_autopay_enabled);

        // Toggle attempt via unauthorized staff (e.g. finance department only, not enrolment or operations)
        // Wait, Finance is not in checkManagementRole's allowed list
        $response = $this->actingAs($this->staffFinance, 'staff')
            ->post(route('staff.students.wallet.toggle-autopay', ['id' => encrypt($student->id)]));

        $response->assertStatus(403);
    }

    public function test_staff_can_deposit_money_into_wallet(): void
    {
        $student = $this->createStudent(['wallet_balance' => 50.00]);

        $response = $this->actingAs($this->staffFinance, 'staff')
            ->post(route('staff.fees.wallet.deposit'), [
                'student_id' => $student->id,
                'amount' => 150.00,
                'payment_method' => 'cash',
                'notes' => 'Advance fee payment deposit',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $student->refresh();
        $this->assertEquals(200.00, $student->wallet_balance);

        $this->assertDatabaseHas('wallet_transactions', [
            'student_id' => $student->id,
            'amount' => 150.00,
            'type' => 'deposit',
            'payment_method' => 'cash',
            'notes' => 'Advance fee payment deposit',
        ]);
    }

    public function test_staff_can_refund_money_from_wallet(): void
    {
        $student = $this->createStudent(['wallet_balance' => 200.00]);

        // Attempt refund of 80
        $response = $this->actingAs($this->staffFinance, 'staff')
            ->post(route('staff.fees.wallet.refund'), [
                'student_id' => $student->id,
                'amount' => 80.00,
                'payment_method' => 'bank_transfer',
                'notes' => 'Refunding leftover advance balance',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $student->refresh();
        $this->assertEquals(120.00, $student->wallet_balance);

        $this->assertDatabaseHas('wallet_transactions', [
            'student_id' => $student->id,
            'amount' => -80.00,
            'type' => 'refund',
            'payment_method' => 'bank_transfer',
            'notes' => 'Refunding leftover advance balance',
        ]);
    }

    public function test_refund_cannot_exceed_available_wallet_balance(): void
    {
        $student = $this->createStudent(['wallet_balance' => 50.00]);

        $response = $this->actingAs($this->staffFinance, 'staff')
            ->post(route('staff.fees.wallet.refund'), [
                'student_id' => $student->id,
                'amount' => 100.00,
                'payment_method' => 'cash',
                'notes' => 'Should fail',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error');
        $this->assertEquals(50.00, $student->refresh()->wallet_balance);
    }

    public function test_wallet_autopay_allocation_on_fee_generation(): void
    {
        [$course, $classType] = $this->setupClassDependencies();

        $classRoom = ClassRoom::create([
            'course_id' => $course->id,
            'class_type_id' => $classType->id,
            'name' => 'Autopay Class',
            'admission_fee' => 100,
            'monthly_fee' => 500,
            'classes_per_week' => 2,
            'is_completed' => false,
            'starting_date' => '2026-04-10',
        ]);

        $student = $this->createStudent([
            'wallet_balance' => 300.00,
            'is_wallet_autopay_enabled' => true,
        ]);

        $classRoom->students()->attach($student->id);

        // Run today on May 10th (exactly 1 month completed)
        Carbon::setTestNow('2026-05-10 12:00:00');

        $feeService = new FeeService();
        $feeService->generateGroupFeesForToday();

        // There should be a generated monthly fee of 500
        $fee = Fee::where('student_id', $student->id)->firstOrFail();
        $this->assertEquals(500.00, $fee->amount);

        // Since wallet balance was 300 and autopay is ON, 300 should be auto-allocated
        $fee->refresh();
        $student->refresh();

        $this->assertEquals('partial', $fee->status);
        $this->assertEquals(0.00, $student->wallet_balance);

        $this->assertDatabaseHas('fee_payments', [
            'fee_id' => $fee->id,
            'paid_amount' => 300.00,
            'payment_method' => 'wallet',
        ]);

        $this->assertDatabaseHas('wallet_transactions', [
            'student_id' => $student->id,
            'fee_id' => $fee->id,
            'amount' => -300.00,
            'type' => 'fee_payment',
        ]);

        Carbon::setTestNow();
    }

    public function test_wallet_autopay_does_not_allocate_if_autopay_disabled(): void
    {
        [$course, $classType] = $this->setupClassDependencies();

        $classRoom = ClassRoom::create([
            'course_id' => $course->id,
            'class_type_id' => $classType->id,
            'name' => 'No Autopay Class',
            'admission_fee' => 100,
            'monthly_fee' => 500,
            'classes_per_week' => 2,
            'is_completed' => false,
            'starting_date' => '2026-04-10',
        ]);

        $student = $this->createStudent([
            'wallet_balance' => 300.00,
            'is_wallet_autopay_enabled' => false,
        ]);

        $classRoom->students()->attach($student->id);

        Carbon::setTestNow('2026-05-10 12:00:00');

        $feeService = new FeeService();
        $feeService->generateGroupFeesForToday();

        $fee = Fee::where('student_id', $student->id)->firstOrFail();
        $this->assertEquals(500.00, $fee->amount);

        // Autopay is off, so wallet balance should remain untouched and fee unpaid
        $fee->refresh();
        $student->refresh();

        $this->assertEquals('unpaid', $fee->status);
        $this->assertEquals(300.00, $student->wallet_balance);

        $this->assertDatabaseMissing('fee_payments', [
            'fee_id' => $fee->id,
            'payment_method' => 'wallet',
        ]);

        Carbon::setTestNow();
    }

    public function test_manual_wallet_payment_recorded_correctly(): void
    {
        [$course, $classType] = $this->setupClassDependencies();

        $classRoom = ClassRoom::create([
            'course_id' => $course->id,
            'class_type_id' => $classType->id,
            'name' => 'Manual Pay Class',
            'admission_fee' => 100,
            'monthly_fee' => 500,
            'classes_per_week' => 2,
            'is_completed' => false,
            'starting_date' => '2026-04-10',
        ]);

        $student = $this->createStudent(['wallet_balance' => 350.00]);

        $fee = Fee::create([
            'student_id' => $student->id,
            'class_room_id' => $classRoom->id,
            'amount' => 500.00,
            'due_date' => now()->addDays(7)->toDateString(),
            'type' => 'monthly',
            'status' => 'unpaid',
            'receipt_no' => 'REC' . rand(1000, 9999),
        ]);

        // Finance department makes a manual wallet payment of 250
        $response = $this->actingAs($this->staffFinance, 'staff')
            ->post(route('staff.fees.pay'), [
                'fee_id' => $fee->id,
                'paid_amount' => 250.00,
                'payment_method' => 'wallet',
                'paid_date' => now()->toDateString(),
                'notes' => 'Manual payment from wallet',
            ]);

        $response->assertStatus(302);
        
        $fee->refresh();
        $student->refresh();

        $this->assertEquals('partial', $fee->status);
        $this->assertEquals(100.00, $student->wallet_balance); // 350 - 250

        $this->assertDatabaseHas('fee_payments', [
            'fee_id' => $fee->id,
            'paid_amount' => 250.00,
            'payment_method' => 'wallet',
            'notes' => 'Manual payment from wallet',
        ]);

        $this->assertDatabaseHas('wallet_transactions', [
            'student_id' => $student->id,
            'fee_id' => $fee->id,
            'amount' => -250.00,
            'type' => 'fee_payment',
        ]);
    }
}

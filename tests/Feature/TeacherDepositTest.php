<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Role;
use App\Models\Staff;
use App\Models\Teacher;
use App\Models\TeacherSalary;
use App\Models\TeacherDeposit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherDepositTest extends TestCase
{
    use RefreshDatabase;

    private Staff $staffFinance;
    private Admin $admin;
    private Role $roleFinance;
    private Teacher $teacher;

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
        \App\Models\Utility::create(['key' => 'id_administrator_dept', 'value' => '1', 'is_visible' => true]);
        \App\Models\Utility::create(['key' => 'id_hr_dept', 'value' => '2', 'is_visible' => true]);
        \App\Models\Utility::create(['key' => 'id_operation_dept', 'value' => '4', 'is_visible' => true]);

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

        // Create teacher
        $this->teacher = Teacher::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '9876543211',
            'contact_number' => '9876543211',
            'whatsapp_number' => '9876543211',
            'status' => 'active',
            'salary_cycle_day' => 15,
        ]);
    }

    private function createSalary(array $attributes = []): TeacherSalary
    {
        return TeacherSalary::create(array_merge([
            'teacher_id' => $this->teacher->id,
            'cycle_start' => now()->subMonth()->toDateString(),
            'cycle_end' => now()->toDateString(),
            'total_hours' => 20.00,
            'total_amount' => 5000.00,
            'status' => 'unpaid'
        ], $attributes));
    }

    public function test_admin_can_move_first_month_salary_to_deposit(): void
    {
        $salary = $this->createSalary();

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.salaries.deposit', $salary->id));

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $salary->refresh();
        $this->assertEquals('deposit', $salary->status);

        $this->assertDatabaseHas('teacher_deposits', [
            'teacher_id' => $this->teacher->id,
            'teacher_salary_id' => $salary->id,
            'amount' => 5000.00,
            'status' => 'not paid'
        ]);
    }

    public function test_admin_cannot_move_non_first_month_salary_to_deposit(): void
    {
        // First salary
        $salary1 = $this->createSalary([
            'cycle_start' => now()->subMonths(2)->toDateString(),
            'cycle_end' => now()->subMonth()->toDateString(),
        ]);

        // Second salary
        $salary2 = $this->createSalary();

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.salaries.deposit', $salary2->id));

        $response->assertStatus(302);
        $response->assertSessionHas('error');

        $salary2->refresh();
        $this->assertEquals('unpaid', $salary2->status);
    }

    public function test_admin_can_release_deposit_back_to_salary(): void
    {
        $salary = $this->createSalary(['status' => 'deposit']);
        
        TeacherDeposit::create([
            'teacher_id' => $this->teacher->id,
            'teacher_salary_id' => $salary->id,
            'amount' => 5000.00,
            'deposited_date' => now()->toDateString(),
            'due_date' => now()->addMonths(6)->toDateString(),
            'status' => 'not paid'
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.salaries.release', $salary->id));

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $salary->refresh();
        $this->assertEquals('unpaid', $salary->status);
        $this->assertDatabaseMissing('teacher_deposits', [
            'teacher_salary_id' => $salary->id
        ]);
    }

    public function test_admin_cannot_release_deposit_with_payments(): void
    {
        $salary = $this->createSalary(['status' => 'deposit']);
        
        TeacherDeposit::create([
            'teacher_id' => $this->teacher->id,
            'teacher_salary_id' => $salary->id,
            'amount' => 5000.00,
            'paid_amount' => 1000.00,
            'deposited_date' => now()->toDateString(),
            'due_date' => now()->addMonths(6)->toDateString(),
            'status' => 'not paid'
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.salaries.release', $salary->id));

        $response->assertStatus(302);
        $response->assertSessionHas('error');

        $salary->refresh();
        $this->assertEquals('deposit', $salary->status);
    }

    public function test_admin_can_pay_deposit_partially(): void
    {
        $salary = $this->createSalary(['status' => 'deposit']);
        
        $deposit = TeacherDeposit::create([
            'teacher_id' => $this->teacher->id,
            'teacher_salary_id' => $salary->id,
            'amount' => 5000.00,
            'deposited_date' => now()->toDateString(),
            'due_date' => now()->addMonths(6)->toDateString(),
            'status' => 'not paid'
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.deposits.pay'), [
                'deposit_id' => $deposit->id,
                'amount_to_pay' => 2000.00,
                'payment_date' => now()->toDateString(),
                'payment_method' => 'cash',
                'reference_number' => 'REF123',
                'notes' => 'Partial payment'
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $deposit->refresh();
        $this->assertEquals(2000.00, $deposit->paid_amount);
        $this->assertEquals('not paid', $deposit->status);

        $salary->refresh();
        $this->assertEquals('deposit', $salary->status); // stays as deposit
    }

    public function test_admin_can_pay_deposit_fully(): void
    {
        $salary = $this->createSalary(['status' => 'deposit']);
        
        $deposit = TeacherDeposit::create([
            'teacher_id' => $this->teacher->id,
            'teacher_salary_id' => $salary->id,
            'amount' => 5000.00,
            'deposited_date' => now()->toDateString(),
            'due_date' => now()->addMonths(6)->toDateString(),
            'status' => 'not paid'
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.deposits.pay'), [
                'deposit_id' => $deposit->id,
                'amount_to_pay' => 5000.00,
                'payment_date' => now()->toDateString(),
                'payment_method' => 'upi',
                'reference_number' => 'REF999',
                'notes' => 'Full payment'
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $deposit->refresh();
        $this->assertEquals(5000.00, $deposit->paid_amount);
        $this->assertEquals('paid', $deposit->status);

        $salary->refresh();
        $this->assertEquals('paid', $salary->status); // salary is updated to paid!
    }

    public function test_staff_cannot_pay_deposit(): void
    {
        $salary = $this->createSalary(['status' => 'deposit']);
        
        $deposit = TeacherDeposit::create([
            'teacher_id' => $this->teacher->id,
            'teacher_salary_id' => $salary->id,
            'amount' => 5000.00,
            'deposited_date' => now()->toDateString(),
            'due_date' => now()->addMonths(6)->toDateString(),
            'status' => 'not paid'
        ]);

        $response = $this->actingAs($this->staffFinance, 'staff')
            ->post(route('admin.deposits.pay'), [
                'deposit_id' => $deposit->id,
                'amount_to_pay' => 5000.00,
                'payment_date' => now()->toDateString(),
                'payment_method' => 'cash'
            ]);

        // Guard redirect as it's an admin route
        $response->assertRedirect(route('admin.login'));
    }
}

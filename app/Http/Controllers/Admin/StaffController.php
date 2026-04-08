<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\StaffSalary;
use App\Models\StaffSalaryPayment;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $query = Staff::with('roles');

        if ($request->filled('name')) {
            $query->where('name','like','%'.$request->name.'%');
        }

        if ($request->filled('phone')) {
            $query->where('phone','like','%'.$request->phone.'%');
        }

        if ($request->filled('status')) {
            if ($request->status == 'active') {
                $query->where('is_blocked',0);
            } else {
                $query->where('is_blocked',1);
            }
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', function($q) use ($request){
                $q->where('roles.id',$request->role);
            });
        }

        $staffs = $query->latest()->paginate(10)->withQueryString();

        $roles = Role::all();

        return view('admin.staffs.index', compact('staffs','roles'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('admin.staffs.create',compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'phone'         => 'required|string|max:20|unique:staffs,phone',
            'email'         => 'nullable|email|max:255',
            'password'      => 'required|min:4',
            'photo'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'id_proof'      => 'nullable|file|max:4096',
            'roles'         => 'nullable|array',
            'address'       => 'nullable|string|max:500',
            'gpay_number'   => 'nullable|string|max:20',
        ]);

        DB::beginTransaction();

        try {

            $data = $request->only([
                'name',
                'phone',
                'email',
                'address',
                'gpay_number'
            ]);

            // Password hash
            $data['password'] = Hash::make($request->password);

            // ================= Upload Photo =================
            if ($request->hasFile('photo')) {
                $data['photo'] = $request->file('photo')->store('staffs/photos','public');
            }

            // ================= Upload ID Proof =================
            if ($request->hasFile('id_proof')) {
                $data['id_proof'] = $request->file('id_proof')->store('staffs/id_proofs','public');
            }

            // ================= Create Staff =================
            $staff = Staff::create($data);

            // ================= Attach Roles =================
            if ($request->filled('roles')) {
                $staff->roles()->sync($request->roles);
            }

            DB::commit();

            return redirect()
                ->route('admin.staffs.index')
                ->with('success','Staff created successfully');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->with('error','Something went wrong');
        }
    }

    public function edit($id)
    {
        $staff = Staff::with('roles')->findOrFail(decrypt($id));
        $roles = Role::all();
        return view('admin.staffs.create',compact('staff','roles'));
    }

    public function update(Request $request)
    {
        $staffId = decrypt($request->staff_id);

        $staff = Staff::findOrFail($staffId);

        $request->validate([
            'name'      => 'required|string|max:255',
            'phone'     => 'required|string|max:20|unique:staffs,phone,' . $staff->id,
            'email'     => 'nullable|email|max:255',
            'password'  => 'nullable|min:4',
            'photo'     => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'id_proof'  => 'nullable|file|max:4096',
            'roles'     => 'nullable|array',
            'address'   => 'nullable|string|max:500',
            'gpay_number' => 'nullable|string|max:20',
        ]);

        DB::beginTransaction();

        try {

            $data = $request->only([
                'name',
                'phone',
                'email',
                'address',
                'gpay_number'
            ]);

            // ================= Password Update =================
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            // ================= Photo Upload =================
            if ($request->hasFile('photo')) {

                if ($staff->photo && Storage::disk('public')->exists($staff->photo)) {
                    Storage::disk('public')->delete($staff->photo);
                }

                $data['photo'] = $request->file('photo')->store('staffs/photos','public');
            }

            // ================= ID Proof Upload =================
            if ($request->hasFile('id_proof')) {

                if ($staff->id_proof && Storage::disk('public')->exists($staff->id_proof)) {
                    Storage::disk('public')->delete($staff->id_proof);
                }

                $data['id_proof'] = $request->file('id_proof')->store('staffs/id_proofs','public');
            }

            // ================= Update Staff =================
            $staff->update($data);

            // ================= Sync Roles =================
            if ($request->has('roles')) {
                $staff->roles()->sync($request->roles);
            }

            DB::commit();

            return redirect()
                ->route('admin.staffs.index')
                ->with('success','Staff updated successfully');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->with('error','Something went wrong');
        }
    }

    public function show($id)
    {
        $staff = Staff::with(['roles', 'salaries'])->findOrFail(decrypt($id));
        return view('admin.staffs.show', compact('staff'));
    }

    public function storeSalary(Request $request)
    {
        $request->validate([
            'staff_id'       => 'required|exists:staffs,id',
            'salary_month'   => 'required|date_format:Y-m',
            'payment_method' => 'nullable|string|in:cash,card,upi,bank_transfer',
            'payment_date'   => 'nullable|date',
            'paid_amount'    => 'nullable|numeric|min:0',
            'remarks'        => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // Get salary_amount from staff record
            $staff = Staff::findOrFail($request->staff_id);
            $salaryAmount = $staff->salary_amount;
            $paidAmount = $request->paid_amount ?? 0;

            // Validate that paid_amount doesn't exceed salary_amount
            if ($paidAmount > $salaryAmount) {
                DB::rollBack();
                return back()->with('error', 'Paid amount cannot exceed salary amount (₹' . $salaryAmount . ')')
                    ->withInput();
            }

            // Calculate status based on paid_amount
            if ($paidAmount == 0) {
                $status = 'not_paid';
            } elseif ($paidAmount >= $salaryAmount) {
                $status = 'paid';
            } else {
                $status = 'partial';
            }

            $staffSalary = StaffSalary::create([
                'staff_id'      => $request->staff_id,
                'salary_month'  => $request->salary_month,
                'salary_amount' => $salaryAmount,
                'status'        => $status,
                'paid_date'     => $request->payment_date,
                'remarks'       => $request->remarks
            ]);

            // Create payment record if paid_amount is greater than 0
            if ($paidAmount > 0 && $request->payment_method) {
                StaffSalaryPayment::create([
                    'staff_salary_id' => $staffSalary->id,
                    'paid_amount'     => $paidAmount,
                    'payment_method'  => $request->payment_method,
                    'paid_date'       => $request->payment_date,
                    'notes'           => $request->remarks
                ]);
            }

            DB::commit();
            return back()->with('success', 'Salary recorded successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error recording salary: ' . $e->getMessage());
        }
    }

    public function updateSalary(Request $request)
    {
        $request->validate([
            'staff_salary_id' => 'required|exists:staff_salaries,id',
            'staff_id'        => 'required|exists:staffs,id',
            'salary_month'    => 'required|date_format:Y-m',
            'payment_method'  => 'nullable|string|in:cash,card,upi,bank_transfer',
            'payment_date'    => 'nullable|date',
            'paid_amount'     => 'nullable|numeric|min:0',
            'remarks'         => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $staffSalary = StaffSalary::with('payments')->findOrFail($request->staff_salary_id);

            // Get salary_amount from staff record
            $staff = Staff::findOrFail($request->staff_id);
            $salaryAmount = $staff->salary_amount;
            $paidAmount = $request->paid_amount ?? 0;

            $editablePayment = $staffSalary->payments->sortBy('id')->first();
            $otherPaymentsTotal = $staffSalary->payments
                ->when($editablePayment, fn ($payments) => $payments->where('id', '!=', $editablePayment->id))
                ->sum('paid_amount');

            // Validate that paid_amount doesn't exceed salary_amount
            if ($paidAmount > $salaryAmount) {
                DB::rollBack();
                return back()->with('error', 'Paid amount cannot exceed salary amount (₹' . $salaryAmount . ')')
                    ->withInput();
            }

            if ($paidAmount < $otherPaymentsTotal) {
                DB::rollBack();
                return back()->with('error', 'Paid amount cannot be less than already recorded payments (₹' . number_format($otherPaymentsTotal, 2) . ')')
                    ->withInput();
            }

            $currentPaymentAmount = max($paidAmount - $otherPaymentsTotal, 0);

            // Calculate status based on paid_amount
            if ($paidAmount == 0) {
                $status = 'not_paid';
            } elseif ($paidAmount >= $salaryAmount) {
                $status = 'paid';
            } else {
                $status = 'partial';
            }

            $staffSalary->update([
                'salary_month'  => $request->salary_month,
                'salary_amount' => $salaryAmount,
                'status'        => $status,
                'paid_date'     => $request->payment_date,
                'remarks'       => $request->remarks
            ]);

            // Keep the entered amount aligned with payment history totals.
            if ($paidAmount > 0 && $request->payment_method) {
                $payment = $editablePayment;

                if ($payment) {
                    $payment->update([
                        'paid_amount'     => $currentPaymentAmount,
                        'payment_method'  => $request->payment_method,
                        'paid_date'       => $request->payment_date,
                        'notes'           => $request->remarks
                    ]);
                } elseif ($currentPaymentAmount > 0) {
                    StaffSalaryPayment::create([
                        'staff_salary_id' => $staffSalary->id,
                        'paid_amount'     => $currentPaymentAmount,
                        'payment_method'  => $request->payment_method,
                        'paid_date'       => $request->payment_date,
                        'notes'           => $request->remarks
                    ]);
                }
            }

            DB::commit();
            return back()->with('success', 'Salary updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error updating salary: ' . $e->getMessage());
        }
    }

    public function updateSalaryAmount(Request $request)
    {
        $request->validate([
            'staff_id'      => 'required|exists:staffs,id',
            'salary_amount' => 'required|numeric|min:0'
        ]);

        try {
            $staff = Staff::findOrFail($request->staff_id);
            $staff->update(['salary_amount' => $request->salary_amount]);

            return back()->with('success', 'Salary amount updated successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Error updating salary amount: ' . $e->getMessage());
        }
    }

    public function payBalance(Request $request)
    {
        $request->validate([
            'staff_salary_id' => 'required|exists:staff_salaries,id',
            'payment_amount'  => 'required|numeric|min:0',
            'payment_method'  => 'nullable|string|in:cash,card,upi,bank_transfer',
            'payment_date'    => 'nullable|date',
            'remarks'         => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $staffSalary = StaffSalary::findOrFail($request->staff_salary_id);
            $balanceDue = $staffSalary->balance_due;

            // Validate that payment doesn't exceed balance
            if ($request->payment_amount > $balanceDue) {
                DB::rollBack();
                return back()->with('error', 'Payment amount cannot exceed balance due (₹' . number_format($balanceDue, 2) . ')');
            }

            // Calculate new paid amount and status
            $newPaidAmount = $staffSalary->paid_amount + $request->payment_amount;
            $newStatus = $newPaidAmount >= $staffSalary->salary_amount ? 'paid' : 'partial';

            // Update salary record with new status
            $staffSalary->update([
                'status'      => $newStatus,
                'paid_date'   => $request->payment_date,
                'remarks'     => $request->remarks
            ]);

            // Create payment record
            if ($request->payment_method) {
                StaffSalaryPayment::create([
                    'staff_salary_id' => $staffSalary->id,
                    'paid_amount'     => $request->payment_amount,
                    'payment_method'  => $request->payment_method,
                    'paid_date'       => $request->payment_date,
                    'notes'           => $request->remarks
                ]);
            }

            DB::commit();
            return back()->with('success', 'Balance payment recorded successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error processing balance payment: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        Staff::findOrFail(decrypt($id))->delete();
        return back()->with('success','Deleted');
    }

    public function toggleBlock($id)
    {
        $staff = Staff::findOrFail(decrypt($id));
        $staff->is_blocked = !$staff->is_blocked;
        $staff->save();

        return back()->with('success','Status changed');
    }
}

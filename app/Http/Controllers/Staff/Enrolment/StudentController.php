<?php

namespace App\Http\Controllers\Staff\Enrolment;

use App\Http\Controllers\Controller;
use App\Models\ClassNote;
use App\Models\ClassRoom;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Fee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    private function checkOperationRole()
    {
        $staff = auth('staff')->user();
        $operationRoleId = utility('id_operation_dept');

        if (!$staff->hasRoleId($operationRoleId)) {
            abort(403, 'Unauthorized Department Access: Operations only.');
        }
    }

    private function checkManagementRole()
    {
        $staff = auth('staff')->user();
        $enrolmentRoleId = utility('id_enrolment_dept');
        $operationRoleId = utility('id_operation_dept');

        if (!$staff->hasRoleId($enrolmentRoleId) && !$staff->hasRoleId($operationRoleId)) {
            abort(403, 'Unauthorized Department Access: Enrolment or Operations only.');
        }
    }

    public function index(Request $request)
    {
        $students = Student::query()->with('lead');

        if ($request->filled('status')) {
            $students->where('status', $request->status);
        }

        if ($request->filled('is_blocked')) {
            $students->where('is_blocked', $request->is_blocked);
        }

        if ($request->filled('search')) {
            $students->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('contact_number', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $students = $students->latest('id')->paginate(utility('pagination', 50));

        return view('staff.students.index', compact('students'));
    }


    public function create(Request $request)
    {
        $this->checkManagementRole();
        $countries = \App\Models\Country::orderBy('name', 'asc')->get();
        
        $relativeOfStudent = null;
        if ($request->filled('relative_of')) {
            try {
                $relativeOfStudent = Student::findOrFail(decrypt($request->relative_of));
            } catch (\Exception $e) {
                // Ignore decrypt failure
            }
        }

        return view('staff.students.create', compact('countries', 'relativeOfStudent'));
    }




    public function store(Request $request)
    {
        $this->checkManagementRole();

        $request->merge([
            'contact_number' => preg_replace('/[^0-9]/', '', $request->contact_number),
            'whatsapp_number' => preg_replace('/[^0-9]/', '', $request->whatsapp_number),
        ]);

        $request->merge(['phone' => $request->contact_number]);

        $relativeOfStudent = null;
        if ($request->filled('relative_of')) {
            try {
                $relativeOfStudent = Student::with('relatedStudents')->findOrFail(decrypt($request->relative_of));
            } catch (\Exception $e) {
                return back()->with('error', 'Invalid sibling reference.')->withInput();
            }
        }

        if ($relativeOfStudent) {
            // Force same contact details as main student
            $request->merge([
                'country_id' => $relativeOfStudent->country_id,
                'contact_number' => $relativeOfStudent->contact_number,
                'phone' => $relativeOfStudent->phone,
            ]);

            $request->validate([
                'name' => 'required',
                'password' => 'required|min:6',
                'selected_days' => 'required|array|min:1'
            ]);

            // Enforce password is not the same as any family member
            $familyIds = \DB::table('student_relations')
                ->where('student_id', $relativeOfStudent->id)
                ->orWhere('related_student_id', $relativeOfStudent->id)
                ->get()
                ->flatMap(function ($row) {
                    return [$row->student_id, $row->related_student_id];
                })
                ->unique()
                ->toArray();
            $allFamilyIds = array_unique(array_merge($familyIds, [$relativeOfStudent->id]));

            $familyHashedPasswords = Student::whereIn('id', $allFamilyIds)->pluck('password')->filter();

            foreach ($familyHashedPasswords as $hashedPassword) {
                if (\Illuminate\Support\Facades\Hash::check($request->password, $hashedPassword)) {
                    return back()->withErrors([
                        'password' => 'The password cannot be the same as another related family member. Please choose a different password.'
                    ])->withInput();
                }
            }
        } else {
            // Standard validation (must be unique phone)
            $request->validate([
                'name' => 'required',
                'country_id' => 'required|exists:countries,id',
                'contact_number' => 'required|string|digits_between:7,15',
                'phone' => 'required|unique:students,phone,NULL,id,country_id,' . $request->country_id,
                'email' => 'nullable|email',
                'password' => 'required|min:6',
                'selected_days' => 'required|array|min:1'
            ]);
        }

        try {
            // Enforce consistency
            $classesPerWeek = count($request->selected_days ?? []);

            $photo = null;
            $idProof = null;

            if ($request->hasFile('photo')) {
                $photo = $request->file('photo')
                    ->store('students/photos', 'public');
            }

            if ($request->hasFile('id_proof')) {
                $idProof = $request->file('id_proof')
                    ->store('students/id_proofs', 'public');
            }

            $country = \App\Models\Country::find($request->country_id);
            $isWhatsappDifferent = $request->has('is_whatsapp_different');
            if ($isWhatsappDifferent) {
                $whatsapp_number = $request->whatsapp_number;
            } else {
                $countryCode = $country ? preg_replace('/[^0-9]/', '', $country->code) : '91';
                $whatsapp_number = $countryCode . $request->contact_number;
            }

            $admissionNo = generateAdmissionNo();

            $newStudent = Student::create([
                'admission_no' => $admissionNo,
                'student_lead_id' => null,
                'country_id' => $request->country_id,
                'is_whatsapp_different' => $isWhatsappDifferent,
                'name' => $request->name,
                'dob' => $request->dob,
                'email' => $request->email,
                'contact_number' => $request->contact_number,
                'whatsapp_number' => $whatsapp_number,
                'parent_name' => $request->parent_name,
                'address' => $request->address,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'photo' => $photo,
                'id_proof' => $idProof,
                'classes_per_week' => $classesPerWeek,
                'selected_days' => $request->selected_days ?? [],
                'time_slot' => $request->time_slot,
                'starting_date' => $request->starting_date,
                'status' => $request->status ?? 'active'
            ]);

            if ($relativeOfStudent) {
                // Form clique of all family members
                $familyIds = $relativeOfStudent->relatedStudents()->pluck('students.id')->toArray();
                $allFamilyIds = array_unique(array_merge($familyIds, [$relativeOfStudent->id, $newStudent->id]));

                foreach ($allFamilyIds as $id) {
                    $member = Student::find($id);
                    if ($member) {
                        $otherIds = array_diff($allFamilyIds, [$id]);
                        $member->relatedStudents()->sync($otherIds);
                    }
                }

                return redirect()
                    ->route('staff.students.show', encrypt($relativeOfStudent->id))
                    ->with('success', 'Sibling account registered and linked successfully.');
            }

            return redirect()
                ->route('staff.students.index')
                ->with('success', 'Student created successfully.');

        } catch (\Exception $e) {
            Log::error("Student Creation Failed: " . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->with('error', 'Error creating student: ' . $e->getMessage());
        }
    }




    public function edit($id)
    {
        $this->checkManagementRole();
        $student = Student::findOrFail(decrypt($id));
        $countries = \App\Models\Country::orderBy('name', 'asc')->get();

        return view('staff.students.create', compact('student', 'countries'));
    }


    public function update(Request $request, $id)
    {
        $this->checkManagementRole();
        $student = Student::findOrFail(decrypt($id));

        $request->merge([
            'contact_number' => preg_replace('/[^0-9]/', '', $request->contact_number),
            'whatsapp_number' => preg_replace('/[^0-9]/', '', $request->whatsapp_number),
        ]);

        $request->merge(['phone' => $request->contact_number]);

        $relatedIds = \DB::table('student_relations')
            ->where('student_id', $student->id)
            ->orWhere('related_student_id', $student->id)
            ->get()
            ->flatMap(function ($row) {
                return [$row->student_id, $row->related_student_id];
            })
            ->unique()
            ->filter(fn($id) => $id != $student->id)
            ->toArray();

        $excludedIds = array_merge([$student->id], $relatedIds);

        $phoneRule = \Illuminate\Validation\Rule::unique('students', 'phone')
            ->where('country_id', $request->country_id)
            ->whereNotIn('id', $excludedIds);

        $request->validate([
            'name' => 'required',
            'country_id' => 'required|exists:countries,id',
            'contact_number' => 'required|string|digits_between:7,15',
            'phone' => ['required', $phoneRule],
            'email' => 'nullable|email',
            'selected_days' => 'required|array|min:1'
        ]);

        if ($request->password && count($relatedIds) > 0) {
            $familyHashedPasswords = Student::whereIn('id', $relatedIds)->pluck('password')->filter();
            foreach ($familyHashedPasswords as $hashedPassword) {
                if (\Illuminate\Support\Facades\Hash::check($request->password, $hashedPassword)) {
                    return back()->withErrors([
                        'password' => 'The password cannot be the same as another related family member. Please choose a different password.'
                    ])->withInput();
                }
            }
        }

        // Enforce consistency
        $classesPerWeek = count($request->selected_days ?? []);

        $photo = $student->photo;
        $idProof = $student->id_proof;

        if ($request->hasFile('photo')) {

            $photo = $request->file('photo')
                ->store('students/photos', 'public');

        }

        if ($request->hasFile('id_proof')) {

            $idProof = $request->file('id_proof')
                ->store('students/id_proofs', 'public');

        }

        $country = \App\Models\Country::find($request->country_id);
        $isWhatsappDifferent = $request->has('is_whatsapp_different');
        if ($isWhatsappDifferent) {
            $whatsapp_number = $request->whatsapp_number;
        } else {
            $countryCode = $country ? preg_replace('/[^0-9]/', '', $country->code) : '91';
            $whatsapp_number = $countryCode . $request->contact_number;
        }

        $student->update([

            'country_id' => $request->country_id,
            'is_whatsapp_different' => $isWhatsappDifferent,
            'name' => $request->name,
            'dob' => $request->dob,
            'email' => $request->email,
            'contact_number' => $request->contact_number,
            'whatsapp_number' => $whatsapp_number,
            'parent_name' => $request->parent_name,
            'address' => $request->address,

            'phone' => $request->phone,

            // Update password only if provided
            'password' => $request->password
                ? Hash::make($request->password)
                : $student->password,

            'photo' => $photo,
            'id_proof' => $idProof,

            // Class schedule
            'classes_per_week' => $classesPerWeek,
            'selected_days' => $request->selected_days ?? [],
            'time_slot' => $request->time_slot,
            'starting_date' => $request->starting_date,

            'status' => $request->status ?? 'active'

        ]);

        if (count($relatedIds) > 0) {
            $countryCode = $country ? preg_replace('/[^0-9]/', '', $country->code) : '91';

            foreach ($relatedIds as $rId) {
                $relatedStudent = Student::find($rId);
                if ($relatedStudent) {
                    $relatedWhatsapp = $relatedStudent->is_whatsapp_different 
                        ? $relatedStudent->whatsapp_number 
                        : ($countryCode . $request->contact_number);

                    $relatedStudent->update([
                        'country_id' => $request->country_id,
                        'contact_number' => $request->contact_number,
                        'phone' => $request->phone,
                        'whatsapp_number' => $relatedWhatsapp,
                    ]);
                }
            }
        }

        return redirect()
            ->route('staff.students.index')
            ->with('success', 'Student updated successfully.');
    }

    public function show($id)
    {
        $student = Student::with([
            'class_rooms.course',
            'class_rooms.classType',
            'fees',
            'attendances',
            'walletTransactions.fee'
        ])->findOrFail(decrypt($id));

        $teachers = Teacher::whereHas('classRooms', function ($q) use ($student) {
            $q->whereIn('class_rooms.id', $student->class_rooms->pluck('id'));
        })->get();

        $attendance = [
            'total' => $student->attendances()->count(),
            'present' => $student->attendances()->where('is_present', 1)->count(),
            'absent' => $student->attendances()->where('is_present', 0)->count(),
        ];

        $notes = ClassNote::whereIn(
            'class_room_id',
            $student->class_rooms->pluck('id')
        )->latest()->get();

        return view('staff.students.show', compact(
            'student',
            'teachers',
            'attendance',
            'notes'
        ));
    }


    public function checkRelated($id)
    {
        $this->checkOperationRole();
        $student = Student::withCount([
            'fees',
            'attendances',
            'class_rooms',
        ])->findOrFail(decrypt($id));

        return response()->json([
            'name' => $student->name,
            'fees' => $student->fees_count,
            'attendances' => $student->attendances_count,
            'class_rooms' => $student->class_rooms_count,
        ]);
    }

    public function destroy($id)
    {
        $this->checkOperationRole();
        $student = Student::findOrFail(decrypt($id));
        $student->delete(); // soft delete

        return back()->with('success', "Student \"{$student->name}\" deleted successfully.");
    }

    public function assignClass(Request $request)
    {
        $this->checkManagementRole();
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'class_room_id' => 'required|exists:class_rooms,id'
        ]);

        $student = Student::findOrFail($request->student_id);
        $class = ClassRoom::with('classType', 'students')->findOrFail($request->class_room_id);

        // 🔴 INDIVIDUAL CLASS LOGIC
        if ($class->classType?->name === 'individual') {
            if ($class->students()->count() > 0) {
                return back()->with('error', 'Only one student allowed for individual class.');
            }
        }

        // duplicate
        if ($student->class_rooms()->where('class_room_id', $class->id)->exists()) {
            return back()->with('error', 'Student already assigned to this class.');
        }

        // Assign class
        $student->class_rooms()->attach($class->id, [
            'assigned_date' => now()
        ]);

        // =========================
        // Fee Logic Starts Here
        // =========================

        // Case: No admission fee if exempted or student is blocked
        if ($student->is_admission_fee_exempted || $student->is_blocked) {
            return back()->with('success', 'Class assigned ' . ($student->is_blocked ? '(Student is blocked)' : '(Admission fee exempted)'));
        }

        $type = 'admission';
        $amount = max(0, $class->admission_fee - $student->admission_fee_discount);

        if (
            Fee::where('student_id', $student->id)
                ->where('class_room_id', $class->id)
                ->where('type', $type)
                ->exists()
        ) {

            return back()->with('warning', 'Class assigned, fee already exists.');
        }

        // Only create fee if amount > 0
        if ($amount > 0) {

            $fee = Fee::create([
                'student_id' => $student->id,
                'class_room_id' => $class->id,
                'type' => $type,
                'amount' => $amount,
                'due_date' => now()->addDays(7),
                'status' => 'unpaid'
            ]);

            app(\App\Services\FeeService::class)->applyWalletBalance($fee);

        }

        return back()->with('success', 'Class assigned and fee generated successfully.');
    }

    public function saveFeeExemption(Request $request)
    {
        $this->checkManagementRole();
        $request->validate([
            'student_id' => 'required|exists:students,id'
        ]);

        $student = Student::findOrFail($request->student_id);

        $student->update([
            'is_admission_fee_exempted' => $request->has('is_admission_fee_exempted'),
            'is_monthly_fee_exempted' => $request->has('is_monthly_fee_exempted')
        ]);

        return back()->with('success', 'Fee exemption updated successfully');
    }

    public function saveDiscount(Request $request)
    {
        $this->checkManagementRole();
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'admission_fee_discount' => 'nullable|numeric|min:0',
            'monthly_fee_discount' => 'nullable|numeric|min:0',
        ]);

        $student = Student::findOrFail($request->student_id);

        $student->update([
            'admission_fee_discount' => $request->admission_fee_discount ?? 0,
            'monthly_fee_discount' => $request->monthly_fee_discount ?? 0,
        ]);

        return back()->with('success', 'Discount updated successfully');
    }

    public function changeClass(Request $request)
    {
        $this->checkManagementRole();
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'from_class_id' => 'required|exists:class_rooms,id',
            'to_class_id' => 'required|exists:class_rooms,id|different:from_class_id'
        ]);

        $student = Student::findOrFail($request->student_id);

        // Check if student is actually in the from class
        if (!$student->class_rooms()->where('class_room_id', $request->from_class_id)->exists()) {
            return back()->with('error', 'Student is not assigned to the selected "From Class".');
        }

        // Check if student is already in the to class
        if ($student->class_rooms()->where('class_room_id', $request->to_class_id)->exists()) {
            return back()->with('error', 'Student is already assigned to the selected "To Class".');
        }

        DB::transaction(function () use ($student, $request) {
            // 1. Log the change
            DB::table('class_change_logs')->insert([
                'student_id' => $student->id,
                'class_room_id_from' => $request->from_class_id,
                'class_room_id_to' => $request->to_class_id,
                'type' => 'change',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Transfer unpaid fees
            Fee::where('student_id', $student->id)
                ->where('class_room_id', $request->from_class_id)
                ->where('status', 'unpaid')
                ->update(['class_room_id' => $request->to_class_id]);

            // 3. Update class association
            $student->class_rooms()->detach($request->from_class_id);
            $student->class_rooms()->attach($request->to_class_id, [
                'assigned_date' => now()
            ]);
        });

        return back()->with('success', 'Class changed successfully. Unpaid fees have been transferred.');
    }

    public function promoteClass(Request $request)
    {
        $this->checkManagementRole();
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'from_class_id' => 'required|exists:class_rooms,id',
            'to_class_id' => 'required|exists:class_rooms,id|different:from_class_id'
        ]);

        $student = Student::findOrFail($request->student_id);

        // Check if student is actually in the from class
        if (!$student->class_rooms()->where('class_room_id', $request->from_class_id)->exists()) {
            return back()->with('error', 'Student is not assigned to the selected "From Class".');
        }

        // Check if student is already in the to class
        if ($student->class_rooms()->where('class_room_id', $request->to_class_id)->exists()) {
            return back()->with('error', 'Student is already assigned to the selected "To Class".');
        }

        // Check for unpaid fees
        $hasUnpaidFees = Fee::where('student_id', $student->id)
            ->where('class_room_id', $request->from_class_id)
            ->where('status', '!=', 'paid')
            ->exists();

        if ($hasUnpaidFees) {
            return back()->with('error', 'Cannot promote student. There are unpaid fees in the current class.');
        }

        DB::transaction(function () use ($student, $request) {
            // 1. Log the promotion
            DB::table('class_change_logs')->insert([
                'student_id' => $student->id,
                'class_room_id_from' => $request->from_class_id,
                'class_room_id_to' => $request->to_class_id,
                'type' => 'promote',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Add new class association
            $student->class_rooms()->attach($request->to_class_id, [
                'assigned_date' => now()
            ]);

            // 3. Mark the from class as completed
            ClassRoom::where('id', $request->from_class_id)->update(['is_completed' => true]);
        });

        return back()->with('success', 'Student promoted to new class successfully.');
    }

    public function searchActiveClasses(Request $request)
    {
        $term = $request->input('q', '');
        $query = ClassRoom::with('course')
            ->where('is_completed', false);

        // Filter by type name (e.g., 'group,individual') if provided
        if ($request->filled('type')) {
            $types = explode(',', $request->type);
            $query->whereHas('classType', function ($q) use ($types) {
                $q->whereIn('name', $types);
            });
        }

        // Exclude specific student's enrolled classes
        if ($request->filled('exclude_student_id')) {
            $query->whereDoesntHave('students', function ($q) use ($request) {
                $q->where('students.id', $request->exclude_student_id);
            });
        }

        $results = $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhereHas('course', fn($c) => $c->where('name', 'like', "%{$term}%"));
        })
            ->limit(30)
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'text' => $c->name . ($c->course ? ' (' . $c->course->name . ')' : ''),
            ]);

        return response()->json(['results' => $results]);
    }

    public function searchStudents(Request $request)
    {
        $term = $request->input('q', '');
        $excludeClassId = $request->input('exclude_class_id');

        $query = Student::query();

        if ($excludeClassId) {
            $query->whereDoesntHave('class_rooms', function ($q) use ($excludeClassId) {
                $q->where('class_rooms.id', $excludeClassId);
            });
        }

        $results = $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('contact_number', 'like', "%{$term}%")
                ->orWhere('admission_no', 'like', "%{$term}%");
        })
            ->limit(20)
            ->get();

        return response()->json(['results' => $results]);
    }

    public function toggleWalletAutopay(Request $request, $id)
    {
        $this->checkManagementRole();
        $student = Student::findOrFail(decrypt($id));
        $student->update([
            'is_wallet_autopay_enabled' => !$student->is_wallet_autopay_enabled
        ]);
        return back()->with('success', 'Wallet autopay setting updated successfully.');
    }

    public function toggleBlock($id)
    {
        $this->checkManagementRole();
        $student = Student::findOrFail(decrypt($id));
        $student->update([
            'is_blocked' => !$student->is_blocked
        ]);

        $statusStr = $student->is_blocked ? 'blocked' : 'unblocked';
        return back()->with('success', "Student \"{$student->name}\" {$statusStr} successfully.");
    }

    public function removeRelation(Request $request, $id, $related_id)
    {
        $this->checkManagementRole();
        
        $student = Student::findOrFail(decrypt($id));
        $relatedStudent = Student::findOrFail(decrypt($related_id));

        $request->validate([
            'new_contact_number' => 'required|string|digits_between:7,15',
        ]);

        $newNumber = preg_replace('/[^0-9]/', '', $request->new_contact_number);

        // Check unique constraint for the new number
        $exists = Student::where('phone', $newNumber)
            ->where('country_id', $relatedStudent->country_id)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'new_contact_number' => 'The contact number is already registered under this country.'
            ])->withInput();
        }

        // Update details and password
        $country = $relatedStudent->country;
        $countryCode = $country ? preg_replace('/[^0-9]/', '', $country->code) : '91';
        $whatsapp_number = $relatedStudent->is_whatsapp_different 
            ? $relatedStudent->whatsapp_number 
            : ($countryCode . $newNumber);

        $relatedStudent->update([
            'contact_number' => $newNumber,
            'phone' => $newNumber,
            'whatsapp_number' => $whatsapp_number,
            'password' => Hash::make($newNumber),
        ]);

        $relatedStudent->relatedStudents()->detach();
        
        \DB::table('student_relations')
            ->where('related_student_id', $relatedStudent->id)
            ->delete();

        return redirect()->route('staff.students.show', encrypt($student->id))
            ->with('success', 'Sibling account unlinked, contact details updated, and password reset successfully.');
    }

    public function searchStudentsForRelations(Request $request, $id)
    {
        $this->checkManagementRole();
        $student = Student::findOrFail(decrypt($id));
        $term = $request->input('q', '');

        $relatedIds = \DB::table('student_relations')
            ->where('student_id', $student->id)
            ->orWhere('related_student_id', $student->id)
            ->get()
            ->flatMap(function ($row) {
                return [$row->student_id, $row->related_student_id];
            })
            ->unique()
            ->toArray();

        $excludeIds = array_merge([$student->id], $relatedIds);

        $results = Student::whereNotIn('id', $excludeIds)
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('contact_number', 'like', "%{$term}%")
                    ->orWhere('admission_no', 'like', "%{$term}%");
            })
            ->limit(20)
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'text' => $s->name . ' (' . $s->admission_no . ') - ' . $s->phone,
            ]);

        return response()->json(['results' => $results]);
    }

    public function addRelation(Request $request, $id)
    {
        $this->checkManagementRole();
        
        $studentA = Student::findOrFail(decrypt($id));
        $relatedStudentId = $request->input('related_student_id');
        
        if (!$relatedStudentId) {
            return back()->with('error', 'Please select a student to link.');
        }

        $studentB = Student::findOrFail($relatedStudentId);

        if ($studentA->id == $studentB->id) {
            return back()->with('error', 'Cannot link a student to themselves.');
        }

        // Get A's family clique
        $familyIdsA = \DB::table('student_relations')
            ->where('student_id', $studentA->id)
            ->orWhere('related_student_id', $studentA->id)
            ->get()
            ->flatMap(fn($row) => [$row->student_id, $row->related_student_id])
            ->unique()
            ->toArray();
        $allFamilyIdsA = array_unique(array_merge($familyIdsA, [$studentA->id]));

        // Get B's family clique
        $familyIdsB = \DB::table('student_relations')
            ->where('student_id', $studentB->id)
            ->orWhere('related_student_id', $studentB->id)
            ->get()
            ->flatMap(fn($row) => [$row->student_id, $row->related_student_id])
            ->unique()
            ->toArray();
        $allFamilyIdsB = array_unique(array_merge($familyIdsB, [$studentB->id]));

        // Check if there are any intersecting IDs
        if (count(array_intersect($allFamilyIdsA, $allFamilyIdsB)) > 0) {
            return back()->with('error', 'These students are already linked.');
        }

        // Update all members of B's clique to use A's contact details
        foreach ($allFamilyIdsB as $mId) {
            $member = Student::find($mId);
            if ($member) {
                $member->update([
                    'country_id' => $studentA->country_id,
                    'contact_number' => $studentA->contact_number,
                    'phone' => $studentA->phone,
                    'whatsapp_number' => $studentA->whatsapp_number,
                    'is_whatsapp_different' => $studentA->is_whatsapp_different,
                ]);
            }
        }

        // Merge cliques
        $combinedFamilyIds = array_unique(array_merge($allFamilyIdsA, $allFamilyIdsB));

        // Sync relationships bidirectionally for all members of the combined clique
        foreach ($combinedFamilyIds as $mId) {
            $member = Student::find($mId);
            if ($member) {
                $otherIds = array_diff($combinedFamilyIds, [$mId]);
                $member->relatedStudents()->sync($otherIds);
            }
        }

        return back()->with('success', 'Student linked as family member successfully.');
    }
}

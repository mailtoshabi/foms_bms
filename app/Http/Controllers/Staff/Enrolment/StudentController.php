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

        if ($request->filled('search')) {
            $students->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('contact_number', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $students = $students->latest()->paginate(10);

        return view('staff.students.index', compact('students'));
    }


    public function create()
    {
        $this->checkManagementRole();
        $countries = \App\Models\Country::orderBy('name', 'asc')->get();
        return view('staff.students.create', compact('countries'));
    }




    public function store(Request $request)
    {
        $this->checkManagementRole();

        $request->merge([
            'contact_number' => preg_replace('/[^0-9]/', '', $request->contact_number),
            'whatsapp_number' => preg_replace('/[^0-9]/', '', $request->whatsapp_number),
        ]);

        $request->merge(['phone' => $request->contact_number]);

        $request->validate([
            'name' => 'required',
            'country_id' => 'required|exists:countries,id',
            'contact_number' => 'required|string|digits_between:7,15',
            'phone' => 'required|unique:students,phone,NULL,id,country_id,' . $request->country_id,
            'email' => 'nullable|email',
            'password' => 'required|min:6',
            'selected_days' => 'required|array|min:1'
        ]);

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

            Student::create([
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

        $request->validate([
            'name' => 'required',
            'country_id' => 'required|exists:countries,id',
            'contact_number' => 'required|string|digits_between:7,15',
            'phone' => 'required|unique:students,phone,' . $student->id . ',id,country_id,' . $request->country_id,
            'email' => 'nullable|email',
            'selected_days' => 'required|array|min:1'
        ]);

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
            'attendances'
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

        $isAdmissionExempted = $student->is_admission_fee_exempted;


        // Case: No admission fee if exempted
        if ($isAdmissionExempted) {
            return back()->with('success', 'Class assigned (Admission fee exempted)');
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

            Fee::create([
                'student_id' => $student->id,
                'class_room_id' => $class->id,
                'type' => $type,
                'amount' => $amount,
                'due_date' => Carbon::parse($class->starting_date)->addDays(7),
                'status' => 'unpaid'
            ]);

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
}

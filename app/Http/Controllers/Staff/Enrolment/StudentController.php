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

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $students = Student::query()->with('lead');

        if ($request->filled('status')) {
            $students->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $students->where(function ($q) use ($request) {
                $q->where('name','like','%'.$request->search.'%')
                  ->orWhere('contact_number','like','%'.$request->search.'%')
                  ->orWhere('email','like','%'.$request->search.'%');
            });
        }

        $students = $students->latest()->paginate(10);

        return view('staff.students.index', compact('students'));
    }


    public function create()
    {
        return view('staff.students.create');
    }




public function store(Request $request)
{
    $request->validate([
        'name'           => 'required',
        'contact_number' => 'required|string|digits_between:7,15',
        'phone'          => 'required|unique:students,phone',
        'email'          => 'nullable|email',
        'password'       => 'required|min:6',
        'selected_days'  => 'required|array|min:1'
    ]);

    // Enforce consistency
    $classesPerWeek = count($request->selected_days ?? []);

    $photo = null;
    $idProof = null;

    if ($request->hasFile('photo')) {
        $photo = $request->file('photo')
            ->store('students/photos','public');
    }

    if ($request->hasFile('id_proof')) {
        $idProof = $request->file('id_proof')
            ->store('students/id_proofs','public');
    }

    Student::create([

        'student_lead_id' => null,

        'name' => $request->name,
        'dob' => $request->dob,
        'email' => $request->email,
        'contact_number' => $request->contact_number,
        'whatsapp_number' => $request->whatsapp_number ?? $request->contact_number,
        'parent_name' => $request->parent_name,
        'address' => $request->address,

        'phone' => $request->phone,
        'password' => Hash::make($request->password),

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
        ->with('success','Student created successfully.');
}


    public function edit($id)
    {
        $student = Student::findOrFail(decrypt($id));

        return view('staff.students.create', compact('student'));
    }


public function update(Request $request, $id)
{
    $student = Student::findOrFail(decrypt($id));

    $request->validate([
        'name'           => 'required',
        'contact_number' => 'required|string|digits_between:7,15',
        'phone'          => 'required|unique:students,phone,' . $student->id,
        'email'          => 'nullable|email',
        'selected_days'  => 'required|array|min:1'
    ]);

    // Enforce consistency
    $classesPerWeek = count($request->selected_days ?? []);

    $photo = $student->photo;
    $idProof = $student->id_proof;

    if ($request->hasFile('photo')) {

        $photo = $request->file('photo')
            ->store('students/photos','public');

    }

    if ($request->hasFile('id_proof')) {

        $idProof = $request->file('id_proof')
            ->store('students/id_proofs','public');

    }

    $student->update([

        'name' => $request->name,
        'dob' => $request->dob,
        'email' => $request->email,
        'contact_number' => $request->contact_number,
        'whatsapp_number' => $request->whatsapp_number ?? $request->contact_number,
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
        ->with('success','Student updated successfully.');
}

    public function show($id)
    {
        $student = Student::with([
            'class_rooms.course',
            'class_rooms.classType',
            'fees',
            'attendances'
        ])->findOrFail(decrypt($id));

        $teachers = Teacher::whereHas('classRooms', function($q) use ($student){
            $q->whereIn('class_rooms.id',$student->class_rooms->pluck('id'));
        })->get();

        $attendance = [
            'total' => $student->attendances()->count(),
            'present' => $student->attendances()->where('is_present',1)->count(),
            'absent' => $student->attendances()->where('is_present',0)->count(),
        ];

        $notes = ClassNote::whereIn(
            'class_room_id',
            $student->class_rooms->pluck('id')
        )->latest()->get();

        $classRooms = ClassRoom::with('course')->get();

        return view('staff.students.show', compact(
            'student','teachers','attendance','notes','classRooms'
        ));
    }


    public function destroy($id)
    {
        Student::findOrFail(decrypt($id))->delete();

        return back()->with('success','Student deleted.');
    }

    public function assignClass(Request $request)
{
    $request->validate([
        'student_id' => 'required|exists:students,id',
        'class_room_id' => 'required|exists:class_rooms,id'
    ]);

    $student = Student::findOrFail($request->student_id);
    $class = ClassRoom::findOrFail($request->class_room_id);

    // duplicate
    if ($student->class_rooms()->where('class_room_id', $class->id)->exists()) {
        return back()->with('error','Student already assigned to this class.');
    }

    // Assign class
    $student->class_rooms()->attach($class->id, [
        'assigned_date' => now()
    ]);

    // =========================
    // Fee Logic Starts Here
    // =========================

    $isAdmissionExempted = $student->is_admission_fee_exempted;
    $isMonthlyExempted = $student->is_monthly_fee_exempted;


    // Case 1: Both exempted → do nothing
    if ($isAdmissionExempted && $isMonthlyExempted) {
        return back()->with('success','Class assigned (No fee - fully exempted)');
    }

    $type = null;
    $amount = 0;

    // Case 2: Admission exempted → monthly fee
    if ($isAdmissionExempted && !$isMonthlyExempted) {

        $type = 'monthly';
        $amount = max(0, $class->monthly_fee - $student->monthly_fee_discount);

    }
    // Case 3: Monthly exempted → admission fee
    elseif (!$isAdmissionExempted && $isMonthlyExempted) {

        $type = 'admission';
        $amount = max(0, $class->admission_fee - $student->admission_fee_discount);

    }
    // Case 4: No exemption
    else {

        if ($class->admission_fee > 0) {
            $type = 'admission';
            $amount = max(0, $class->admission_fee - $student->admission_fee_discount);
        } else {
            $type = 'monthly';
            $amount = max(0, $class->monthly_fee - $student->monthly_fee_discount);
        }

    }

    if (Fee::where('student_id',$student->id)
            ->where('class_room_id',$class->id)
            ->where('type',$type)
            ->exists()) {

        return back()->with('warning','Class assigned, fee already exists.');
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

    return back()->with('success','Class assigned and fee generated successfully.');
}

    public function saveFeeExemption(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id'
        ]);

        $student = Student::findOrFail($request->student_id);

        $student->update([
            'is_admission_fee_exempted' => $request->has('is_admission_fee_exempted'),
            'is_monthly_fee_exempted' => $request->has('is_monthly_fee_exempted')
        ]);

        return back()->with('success','Fee exemption updated successfully');
    }

    public function saveDiscount(Request $request)
    {
        $request->validate([
            'student_id'             => 'required|exists:students,id',
            'admission_fee_discount' => 'nullable|numeric|min:0',
            'monthly_fee_discount'   => 'nullable|numeric|min:0',
        ]);

        $student = Student::findOrFail($request->student_id);

        $student->update([
            'admission_fee_discount' => $request->admission_fee_discount ?? 0,
            'monthly_fee_discount'   => $request->monthly_fee_discount ?? 0,
        ]);

        return back()->with('success', 'Discount updated successfully');
    }
}

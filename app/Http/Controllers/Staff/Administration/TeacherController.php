<?php

namespace App\Http\Controllers\Staff\Administration;

use App\Http\Controllers\Controller;
use App\Models\ClassNote;
use App\Models\ClassRoom;
use Illuminate\Http\Request;
use App\Models\Teacher;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class TeacherController extends Controller
{

public function index(Request $request)
{
    $teachers = Teacher::query()->with('lead');

    if ($request->filled('search')) {
        $teachers->where(function ($q) use ($request) {
            $q->where('name','like','%'.$request->search.'%')
              ->orWhere('contact_number','like','%'.$request->search.'%');
        });
    }

    $teachers = $teachers->latest()->paginate(10);

    return view('staff.teachers.index',compact('teachers'));
}


public function create()
{
    return view('staff.teachers.create');
}


public function store(Request $request)
{
    $request->validate([
        'name'=>'required',
        'contact_number'=>'required',
        'phone'=>'required|unique:teachers,phone',
        'password'=>'required|min:6'
    ]);

    $photo=null;
    $idProof=null;

    if($request->hasFile('photo')){
        $photo=$request->file('photo')->store('teachers/photos','public');
    }

    if($request->hasFile('id_proof')){
        $idProof=$request->file('id_proof')->store('teachers/id_proofs','public');
    }

    Teacher::create([

        'teacher_lead_id'=>null,

        'name'=>$request->name,
        'dob'=>$request->dob,
        'email'=>$request->email,
        'contact_number'=>$request->contact_number,
        'whatsapp_number'=>$request->whatsapp_number ?? $request->contact_number,
        'upi_number'=>$request->upi_number,
        'address'=>$request->address,

        'qualification'=>$request->qualification,
        'experience'=>$request->experience,

        'phone'=>$request->phone,
        'password'=>Hash::make($request->password),

        'photo'=>$photo,
        'id_proof'=>$idProof,

        'status'=>'active'
    ]);

    return redirect()
        ->route('staff.teachers.index')
        ->with('success','Teacher created.');
}


public function edit($id)
{
    $teacher = Teacher::findOrFail(decrypt($id));

    return view('staff.teachers.create',compact('teacher'));
}


public function update(Request $request,$id)
{
    $teacher = Teacher::findOrFail(decrypt($id));

    $teacher->update($request->except('password'));

    if($request->filled('password')){
        $teacher->update([
            'password'=>Hash::make($request->password)
        ]);
    }

    return redirect()
        ->route('staff.teachers.index')
        ->with('success','Teacher updated.');
}

public function show($id)
{
    $teacher = Teacher::with([
    'classRooms.course',
    'classRooms.classType',
    'salaries'
    ])->findOrFail(decrypt($id));

    $classRooms = ClassRoom::with('course')->get();

    $assignedClasses = $teacher->classRooms->pluck('id')->toArray();

    $notes = ClassNote::where('teacher_id',$teacher->id)->latest()->get();

    return view('staff.teachers.show',[
        'teacher'=>$teacher,
        'classRooms'=>$classRooms,
        'assignedClasses'=>$assignedClasses,
        'notes'=>$notes
    ]);
}


public function destroy($id)
{
    Teacher::findOrFail(decrypt($id))->delete();

    return back()->with('success','Teacher deleted.');
}

}

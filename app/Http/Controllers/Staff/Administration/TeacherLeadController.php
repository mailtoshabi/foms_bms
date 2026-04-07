<?php

namespace App\Http\Controllers\Staff\Administration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TeacherLead;
use App\Models\Teacher;
use App\Models\TeacherLeadNote;
use App\Models\Source;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TeacherLeadController extends Controller
{

public function index(Request $request)
{
    $leads = TeacherLead::query();

    if ($request->filled('status')) {
        $leads->where('status',$request->status);
    }

    if ($request->filled('date')) {
        $leads->whereDate('created_at',$request->date);
    }

    $leads = $leads->latest()->paginate(10);

    return view('staff.teacher_leads.index',compact('leads'));
}


public function create()
{
    $sources = Source::where('is_active', true)->get();
    return view('staff.teacher_leads.create', compact('sources'));
}


public function store(Request $request)
{
    $request->validate([
        'name'           => 'required',
        'contact_number' => 'required',
        'email'          => 'nullable|email',
        'source_id'      => 'nullable|exists:sources,id',
    ]);

    TeacherLead::create([
        'name'           => $request->name,
        'contact_number' => $request->contact_number,
        'email'          => $request->email,
        'source_id'      => $request->source_id,
        'status'         => 'pending',
    ]);

    return redirect()
        ->route('staff.teacher-leads.index')
        ->with('success','Teacher lead created.');
}


public function edit($id)
{
    $lead = TeacherLead::findOrFail(decrypt($id));
    $sources = Source::where('is_active', true)->get();

    return view('staff.teacher_leads.create', compact('lead', 'sources'));
}


public function update(Request $request,$id)
{
    $lead = TeacherLead::findOrFail(decrypt($id));

    $request->validate([
        'name'=>'required',
        'contact_number'=>'required',
        'email'=>'nullable|email',
        'status'=>'required|in:pending,approved,not_interested'
    ]);

    $request->validate([
        'source_id' => 'nullable|exists:sources,id',
    ]);

    $lead->update($request->only(
        'name', 'contact_number', 'email', 'source_id', 'status'
    ));

    return redirect()
        ->route('staff.teacher-leads.index')
        ->with('success','Teacher lead updated.');
}


public function destroy($id)
{
    TeacherLead::findOrFail(decrypt($id))->delete();

    return back()->with('success','Lead deleted.');
}


public function storeNote(Request $request, $leadId)
{
    $request->validate([
        'note' => 'required|string',
        'status' => 'required|in:pending,follow_up,no_response,not_interested,interested,converted'
    ]);

    TeacherLeadNote::create([
        'teacher_lead_id' => $leadId,
        'staff_id' => auth('staff')->id(),
        'note' => $request->note,
        'status' => $request->status,
    ]);

    return back()->with('success', 'Note added successfully.');
}

public function convertToTeacher(Request $request,$id)
{
    $lead = TeacherLead::with('teacher')->findOrFail(decrypt($id));

    if($lead->teacher){
        return back()->with('error','Already converted.');
    }

    $request->validate([
        'name'=>'required',
        'contact_number'=>'required',
        'email'=>'nullable|email'
    ]);

    DB::transaction(function() use ($request,$lead){

        $photo=null;
        $idProof=null;

        if($request->hasFile('photo')){
            $photo=$request->file('photo')->store('teachers/photos','public');
        }

        if($request->hasFile('id_proof')){
            $idProof=$request->file('id_proof')->store('teachers/id_proofs','public');
        }

        $phone = $request->contact_number;

        Teacher::create([

            'teacher_lead_id'=>$lead->id,

            'name'=>$request->name,
            'dob'=>$request->dob,
            'email'=>$request->email,
            'contact_number'=>$request->contact_number,
            'whatsapp_number'=>$request->whatsapp_number ?? $request->contact_number,
            'upi_number'=>$request->upi_number,
            'address'=>$request->address,

            'qualification'=>$request->qualification,
            'experience'=>$request->experience,

            'phone'=>$phone,
            'password'=>Hash::make($phone),

            'photo'=>$photo,
            'id_proof'=>$idProof,

            'status'=>'active'
        ]);

        $lead->update([
            'status'=>'approved'
        ]);

    });

    return redirect()
        ->route('staff.teacher-leads.index')
        ->with('success','Teacher created successfully.');
}

}

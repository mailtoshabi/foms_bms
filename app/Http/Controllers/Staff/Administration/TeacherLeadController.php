<?php

namespace App\Http\Controllers\Staff\Administration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TeacherLead;
use App\Models\Teacher;
use App\Models\TeacherLeadNote;
use App\Models\Source;
use App\Models\Country;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class TeacherLeadController extends Controller
{

public function index(Request $request)
{
    $leads = TeacherLead::query()->with('country');

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
    $countries = Country::orderBy('name', 'asc')->get();
    return view('staff.teacher_leads.create', compact('sources', 'countries'));
}


public function store(Request $request)
{
    $request->merge([
        'contact_number'  => preg_replace('/[^0-9]/', '', $request->contact_number),
        'whatsapp_number' => preg_replace('/[^0-9]/', '', $request->whatsapp_number),
    ]);

    $request->validate([
        'name'           => 'required',
        'country_id'     => 'required|exists:countries,id',
        'contact_number' => 'required|string|digits_between:7,15|unique:teacher_leads,contact_number',
        'email'          => 'nullable|email',
        'source_id'      => 'nullable|exists:sources,id',
    ]);

    $country = Country::find($request->country_id);
    $isWhatsappDifferent = $request->has('is_whatsapp_different');
    if ($isWhatsappDifferent) {
        $whatsapp_number = $request->whatsapp_number;
    } else {
        $countryCode = $country ? preg_replace('/[^0-9]/', '', $country->code) : '91';
        $whatsapp_number = $countryCode . $request->contact_number;
    }

    TeacherLead::create([
        'name'           => $request->name,
        'country_id'     => $request->country_id,
        'contact_number' => $request->contact_number,
        'whatsapp_number' => $whatsapp_number,
        'is_whatsapp_different' => $isWhatsappDifferent,
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
    $countries = Country::orderBy('name', 'asc')->get();

    return view('staff.teacher_leads.create', compact('lead', 'sources', 'countries'));
}


public function update(Request $request,$id)
{
    $request->merge([
        'contact_number'  => preg_replace('/[^0-9]/', '', $request->contact_number),
        'whatsapp_number' => preg_replace('/[^0-9]/', '', $request->whatsapp_number),
    ]);

    $lead = TeacherLead::findOrFail(decrypt($id));

    $request->validate([
        'name'           => 'required',
        'country_id'     => 'required|exists:countries,id',
        'contact_number' => 'required|string|digits_between:7,15|unique:teacher_leads,contact_number,' . $lead->id,
        'email'          => 'nullable|email',
        'source_id'      => 'nullable|exists:sources,id',
    ]);

    $country = Country::find($request->country_id);
    $isWhatsappDifferent = $request->has('is_whatsapp_different');
    if ($isWhatsappDifferent) {
        $whatsapp_number = $request->whatsapp_number;
    } else {
        $countryCode = $country ? preg_replace('/[^0-9]/', '', $country->code) : '91';
        $whatsapp_number = $countryCode . $request->contact_number;
    }

    $lead->update([
        'name'           => $request->name,
        'country_id'     => $request->country_id,
        'contact_number' => $request->contact_number,
        'whatsapp_number' => $whatsapp_number,
        'is_whatsapp_different' => $isWhatsappDifferent,
        'email'          => $request->email,
        'source_id'      => $request->source_id,
    ]);

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
    $request->merge([
        'contact_number' => preg_replace('/[^0-9]/', '', $request->contact_number),
        'whatsapp_number' => preg_replace('/[^0-9]/', '', $request->whatsapp_number),
    ]);

    $lead = TeacherLead::with('teacher')->findOrFail(decrypt($id));

    if($lead->teacher){
        return back()->with('error','Already converted.');
    }

    $request->merge(['phone' => $request->contact_number]);

    $request->validate([
        'name'           => 'required',
        'contact_number' => 'required|string|digits_between:7,15',
        'email'          => 'nullable|email'
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

        $isWhatsappDifferent = $request->has('is_whatsapp_different');
        if ($isWhatsappDifferent) {
            $whatsapp_number = $request->whatsapp_number;
        } else {
            $countryCode = $lead->country ? preg_replace('/[^0-9]/', '', $lead->country->code) : '91';
            $whatsapp_number = $countryCode . $request->contact_number;
        }

        Teacher::create([

            'teacher_lead_id'=>$lead->id,
            'country_id'=>$lead->country_id,
            'is_whatsapp_different' => $isWhatsappDifferent,

            'name'=>$request->name,
            'dob'=>$request->dob,
            'email'=>$request->email,
            'contact_number'=>$request->contact_number,
            'whatsapp_number'=>$whatsapp_number,
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

    public function regenerateLink($id)
    {
        $lead = TeacherLead::findOrFail(decrypt($id));
        $lead->regenerateFormToken();

        return back()->with('success', 'Admission form link regenerated successfully.');
    }
}

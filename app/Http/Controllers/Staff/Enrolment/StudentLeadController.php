<?php

namespace App\Http\Controllers\Staff\Enrolment;

use App\Http\Controllers\Controller;
use App\Models\LeadNote;
use Illuminate\Http\Request;
use App\Models\StudentLead;
use App\Models\Source;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentLeadController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $leads = StudentLead::with('source');

        // Filter by status
        if ($request->filled('status')) {
            $leads->where('status', $request->status);
        }

        // Filter by source
        if ($request->filled('source_id')) {
            $leads->where('source_id', $request->source_id);
        }

        // Filter by date
        if ($request->filled('date')) {
            $leads->whereDate('created_at', $request->date);
        }

        $leads = $leads->latest()->paginate(10);

        // Needed for filter dropdown
        $sources = Source::where('is_active', true)->get();

        return view('staff.student_leads.index', compact('leads', 'sources'));
    }

    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $sources = Source::where('is_active', true)->get();

        return view('staff.student_leads.create', compact('sources'));
    }

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'digits_between:7,15', 'unique:student_leads,contact_number'],
            'email'          => ['nullable', 'email'],
            'source_id'      => ['nullable', 'exists:sources,id'],
        ]);

        StudentLead::create([
            'name'           => $request->name,
            'contact_number' => $request->contact_number,
            'email'          => $request->email,
            'source_id'      => $request->source_id,
            'status'         => 'pending',
        ]);

        return redirect()
            ->route('staff.student-leads.index')
            ->with('success', 'Lead created successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */
    public function edit($id)
{
    $lead = StudentLead::with('notes.staff')->findOrFail(decrypt($id));
    $sources = Source::where('is_active', true)->get();

    return view('staff.student_leads.create', compact('lead', 'sources'));
}

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        $lead = StudentLead::findOrFail(decrypt($id));

        $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'digits_between:7,15', 'unique:student_leads,contact_number,' . $lead->id],
            'email'          => ['nullable', 'email'],
            'source_id'      => ['nullable', 'exists:sources,id'],
            'status'         => ['required', 'in:pending,admitted'],
        ]);

        $lead->update([
            'name'           => $request->name,
            'contact_number' => $request->contact_number,
            'email'          => $request->email,
            'source_id'      => $request->source_id,
            'status'         => $request->status,
        ]);

        return redirect()
            ->route('staff.student-leads.index')
            ->with('success', 'Lead updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        $lead = StudentLead::findOrFail(decrypt($id));
        $lead->delete();

        return back()->with('success', 'Lead deleted successfully.');
    }


public function storeNote(Request $request, $leadId)
{
    $request->validate([
        'note' => 'required|string',
        'status' => 'required|in:pending,follow_up,no_response,not_interested,interested,converted'
    ]);

    LeadNote::create([
        'student_lead_id' => $leadId,
        'staff_id' => auth('staff')->id(),
        'note' => $request->note,
        'status' => $request->status,
    ]);

    return back()->with('success', 'Note added successfully.');
}


public function convertToStudent(Request $request, $id)
{
    $lead = StudentLead::with('student')->findOrFail(decrypt($id));

    // Prevent duplicate conversion
    if ($lead->student) {
        return redirect()
            ->route('staff.student-leads.edit', $lead->id)
            ->with('error', 'This lead has already been converted to a student.');
    }

    $request->validate([
        'name'           => 'required|string|max:255',
        'contact_number' => 'required|string|digits_between:7,15',
        'email'          => 'nullable|email',
        'classes_per_week' => 'nullable|integer',
        'selected_days'    => 'nullable|array',
        'starting_date'    => 'nullable|date',
    ]);

    DB::transaction(function () use ($request, $lead) {

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

        $phone = $request->contact_number;

        Student::create([

            'student_lead_id' => $lead->id,

            'name' => $request->name,
            'dob' => $request->dob,
            'email' => $request->email,
            'contact_number' => $request->contact_number,
            'whatsapp_number' => $request->whatsapp_number ?? $request->contact_number,
            'parent_name' => $request->parent_name,
            'address' => $request->address,

            'phone' => $phone,
            'password' => Hash::make($phone),

            'photo' => $photo,
            'id_proof' => $idProof,

            // Class Schedule
            'classes_per_week' => $request->classes_per_week,
            'selected_days' => $request->selected_days ?? [],
            'time_slot' => $request->time_slot,
            'starting_date' => $request->starting_date,

            'status' => $request->status ?? 'active'
        ]);

        // Update lead status
        $lead->update([
            'status' => 'admitted'
        ]);
    });

    return redirect()
        ->route('staff.student-leads.index')
        ->with('success','Student created successfully.');
}
}

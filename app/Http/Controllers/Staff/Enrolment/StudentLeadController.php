<?php

namespace App\Http\Controllers\Staff\Enrolment;

use App\Http\Controllers\Controller;
use App\Models\LeadNote;
use Illuminate\Http\Request;
use App\Models\StudentLead;
use App\Models\Source;
use App\Models\Student;
use App\Models\Country;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class StudentLeadController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $leads = StudentLead::with(['source', 'notes.staff']);

        // Search by name or contact
        if ($request->filled('search')) {
            $search = $request->search;
            $leads->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact_number', 'like', "%{$search}%")
                  ->orWhere('whatsapp_number', 'like', "%{$search}%");
            });
        }

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

        $leads = $leads->latest()->paginate(utility('pagination', 50))->withQueryString();

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
        $countries = \App\Models\Country::orderBy('name', 'asc')->get();

        return view('staff.student_leads.create', compact('sources', 'countries'));
    }

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->merge([
            'contact_number' => preg_replace('/[^0-9]/', '', $request->contact_number),
            'whatsapp_number' => preg_replace('/[^0-9]/', '', $request->whatsapp_number),
        ]);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'country_id' => ['required', 'exists:countries,id'],
            'contact_number' => ['required', 'string', 'digits_between:7,15', 'unique:student_leads,contact_number'],
            'email' => ['nullable', 'email'],
            'source_id' => ['nullable', 'exists:sources,id'],
        ]);

        $country = \App\Models\Country::find($request->country_id);
        $isWhatsappDifferent = $request->has('is_whatsapp_different');
        if ($isWhatsappDifferent) {
            $whatsapp_number = $request->whatsapp_number;
        } else {
            $countryCode = $country ? preg_replace('/[^0-9]/', '', $country->code) : '91';
            $whatsapp_number = $countryCode . $request->contact_number;
        }

        StudentLead::create([
            'name' => $request->name,
            'country_id' => $request->country_id,
            'contact_number' => $request->contact_number,
            'whatsapp_number' => $whatsapp_number,
            'is_whatsapp_different' => $isWhatsappDifferent,
            'email' => $request->email,
            'source_id' => $request->source_id,
            'status' => 'pending',
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
        $countries = \App\Models\Country::orderBy('name', 'asc')->get();

        return view('staff.student_leads.create', compact('lead', 'sources', 'countries'));
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        $request->merge([
            'contact_number' => preg_replace('/[^0-9]/', '', $request->contact_number),
            'whatsapp_number' => preg_replace('/[^0-9]/', '', $request->whatsapp_number),
        ]);

        $lead = StudentLead::findOrFail(decrypt($id));

        if ($lead->status === 'converted') {
            return redirect()->route('staff.student-leads.index')->with('error', 'Cannot update a converted lead.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'country_id' => ['required', 'exists:countries,id'],
            'contact_number' => ['required', 'string', 'digits_between:7,15', 'unique:student_leads,contact_number,' . $lead->id],
            'email' => ['nullable', 'email'],
            'source_id' => ['nullable', 'exists:sources,id'],
        ]);

        $country = \App\Models\Country::find($request->country_id);
        $isWhatsappDifferent = $request->has('is_whatsapp_different');
        if ($isWhatsappDifferent) {
            $whatsapp_number = $request->whatsapp_number;
        } else {
            $countryCode = $country ? preg_replace('/[^0-9]/', '', $country->code) : '91';
            $whatsapp_number = $countryCode . $request->contact_number;
        }

        $lead->update([
            'name' => $request->name,
            'country_id' => $request->country_id,
            'contact_number' => $request->contact_number,
            'whatsapp_number' => $whatsapp_number,
            'is_whatsapp_different' => $isWhatsappDifferent,
            'email' => $request->email,
            'source_id' => $request->source_id,
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
        $lead = StudentLead::withCount('notes')->with('student')->findOrFail(decrypt($id));

        if ($lead->student) {
            return back()->with('error', "Cannot delete \"{$lead->name}\" — already converted to a student.");
        }

        $lead->delete();

        return back()->with('success', 'Lead deleted successfully.');
    }


    public function storeNote(Request $request, $leadId)
    {
        $lead = StudentLead::findOrFail($leadId);

        if ($lead->status === 'converted') {
            return back()->with('error', 'Cannot add notes to a converted lead.');
        }

        $request->validate([
            'note' => 'required|string',
            'status' => 'required|in:pending,follow_up,no_response,not_interested,interested'
        ]);

        LeadNote::create([
            'student_lead_id' => $leadId,
            'staff_id' => auth('staff')->id(),
            'note' => $request->note,
            'status' => $request->status,
        ]);

        $lead->update(['status' => $request->status]);

        return back()->with('success', 'Note added successfully.');
    }


    public function convertToStudent(Request $request, $id)
    {
        $request->merge([
            'contact_number' => preg_replace('/[^0-9]/', '', $request->contact_number),
            'whatsapp_number' => preg_replace('/[^0-9]/', '', $request->whatsapp_number),
        ]);

        $lead = StudentLead::with('student')->findOrFail(decrypt($id));

        // Prevent duplicate conversion
        if ($lead->student) {
            return redirect()
                ->route('staff.student-leads.edit', $lead->id)
                ->with('error', 'This lead has already been converted to a student.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'contact_number' => 'required|string|digits_between:7,15',
            'email' => 'nullable|email',
            'classes_per_week' => 'nullable|integer',
            'time_slot' => 'nullable|string',
            'selected_days' => 'nullable|array',
            'starting_date' => 'nullable|date',
        ]);

        try {
            DB::transaction(function () use ($request, $lead) {

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

                $phone = $request->contact_number;

                $isWhatsappDifferent = $request->has('is_whatsapp_different');
                if ($isWhatsappDifferent) {
                    $whatsapp_number = $request->whatsapp_number;
                } else {
                    $country = \App\Models\Country::find($request->country_id);
                    $countryCode = $country ? preg_replace('/[^0-9]/', '', $country->code) : '91';
                    $whatsapp_number = $countryCode . $request->contact_number;
                }

                $admissionNo = generateAdmissionNo();

                Student::create([
                    'admission_no' => $admissionNo,
                    'student_lead_id' => $lead->id,
                    'country_id' => $request->country_id,
                    'is_whatsapp_different' => $isWhatsappDifferent,
                    'name' => $request->name,
                    'dob' => $request->dob,
                    'email' => $request->email,
                    'contact_number' => $request->contact_number,
                    'whatsapp_number' => $whatsapp_number,
                    'parent_name' => $request->parent_name,
                    'address' => $request->address,
                    'phone' => $phone,
                    'password' => Hash::make($phone),
                    'photo' => $photo,
                    'id_proof' => $idProof,
                    'classes_per_week' => $request->classes_per_week,
                    'selected_days' => $request->selected_days ?? [],
                    'time_slot' => $request->time_slot,
                    'starting_date' => $request->starting_date,
                    'status' => 'active'
                ]);

                // Update lead status
                $lead->update([
                    'status' => 'converted'
                ]);
            });

            return redirect()
                ->route('staff.student-leads.index')
                ->with('success', 'Student created successfully.');

        } catch (\Exception $e) {
            Log::error("Student Lead Conversion Failed: " . $e->getMessage(), [
                'lead_id' => $lead->id,
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->with('error', 'Error converting lead: ' . $e->getMessage());
        }
    }



    public function regenerateLink($id)
    {
        $lead = StudentLead::findOrFail(decrypt($id));
        $lead->regenerateFormToken();

        return back()->with('success', 'Admission form link regenerated successfully.');
    }
}

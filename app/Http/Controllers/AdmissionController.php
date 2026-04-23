<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentLead;
use App\Models\TeacherLead;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class AdmissionController extends Controller
{

    public function showForm($type, $token)
    {

        if ($type == 'student') {
            $lead = StudentLead::where('form_token', $token)->firstOrFail();
        } elseif ($type == 'teacher') {
            $lead = TeacherLead::where('form_token', $token)->firstOrFail();
        } else {
            abort(404);
        }

        if ($lead->form_disabled) {
            abort(403, 'This form has been disabled.');
        }

        if ($lead->form_expires_at && now()->gt($lead->form_expires_at)) {
            abort(403, 'This link has expired.');
        }

        if (!$lead->form_opened_at) {
            $lead->update([
                'form_opened_at' => now()
            ]);
        }

        $countries = \App\Models\Country::orderBy('name', 'asc')->get();
        return view('admission.form', compact('lead', 'type', 'countries'));
    }



    public function submitForm(Request $request, $type, $token)
    {
        if ($type == 'student') {
            $lead = StudentLead::where('form_token', $token)->firstOrFail();
        } elseif ($type == 'teacher') {
            $lead = TeacherLead::where('form_token', $token)->firstOrFail();
        } else {
            abort(404);
        }

        $request->merge(['phone' => $request->contact_number]);

        $request->validate([
            'name' => 'required|string|max:255',
            'contact_number' => 'required|string|digits_between:7,15',
            'phone' => [
                'required',
                $type == 'student'
                ? Rule::unique('students', 'phone')->where('country_id', $lead->country_id)
                : Rule::unique('teachers', 'phone')->where('country_id', $lead->country_id)
            ],
            'photo' => 'required|image|max:2048',
            'id_proof' => 'required|file|max:4096',
            'whatsapp_number' => 'nullable|required_if:is_whatsapp_different,1|string|digits_between:7,15'
        ], [
            'phone.unique' => 'This contact number is already registered under this country.'
        ]);

        try {
            $photo = null;
            $idProof = null;

            if ($request->hasFile('photo')) {
                $photo = $request->file('photo')->store('profiles/photos', 'public');
            }

            if ($request->hasFile('id_proof')) {
                $idProof = $request->file('id_proof')->store('profiles/id_proofs', 'public');
            }

            $phone = $request->contact_number;
            $whatsappUrl = '';

            if ($type == 'student') {
                $isWhatsappDifferent = $request->has('is_whatsapp_different');
                if ($isWhatsappDifferent) {
                    $whatsapp_number = $request->whatsapp_number;
                } else {
                    $countryCode = $lead->country ? preg_replace('/[^0-9]/', '', $lead->country->code) : '91';
                    $whatsapp_number = $countryCode . $phone;
                }

                $admissionNo = generateAdmissionNo();

                $student = Student::create([
                    'admission_no' => $admissionNo,
                    'student_lead_id' => $lead->id,
                    'country_id' => $lead->country_id,
                    'is_whatsapp_different' => $isWhatsappDifferent,
                    'name' => $request->name,
                    'dob' => $request->dob,
                    'email' => $request->email,
                    'contact_number' => $phone,
                    'whatsapp_number' => $whatsapp_number,
                    'parent_name' => $request->parent_name,
                    'address' => $request->address,
                    'phone' => $phone,
                    'password' => Hash::make($phone),
                    'photo' => $photo,
                    'id_proof' => $idProof,
                    'status' => 'active'
                ]);

                $lead->update([
                    'status' => 'admitted',
                    'form_disabled' => true,
                    'whatsapp_number' => $whatsapp_number,
                    'is_whatsapp_different' => $isWhatsappDifferent
                ]);

                $whatsappUrl = studentWhatsappMessage($student, $phone);
            } elseif ($type == 'teacher') {
                $isWhatsappDifferent = $request->has('is_whatsapp_different');
                if ($isWhatsappDifferent) {
                    $whatsapp_number = $request->whatsapp_number;
                } else {
                    $countryCode = $lead->country ? preg_replace('/[^0-9]/', '', $lead->country->code) : '91';
                    $whatsapp_number = $countryCode . $phone;
                }

                $teacher = Teacher::create([
                    'teacher_lead_id' => $lead->id,
                    'country_id' => $lead->country_id,
                    'is_whatsapp_different' => $isWhatsappDifferent,
                    'name' => $request->name,
                    'dob' => $request->dob,
                    'email' => $request->email,
                    'contact_number' => $phone,
                    'whatsapp_number' => $whatsapp_number,
                    'qualification' => $request->qualification,
                    'experience' => $request->experience,
                    'upi_number' => $request->upi_number,
                    'address' => $request->address,
                    'phone' => $phone,
                    'password' => Hash::make($phone),
                    'photo' => $photo,
                    'id_proof' => $idProof,
                    'status' => 'active'
                ]);

                $lead->update([
                    'status' => 'approved',
                    'form_disabled' => true,
                    'whatsapp_number' => $whatsapp_number,
                    'is_whatsapp_different' => $isWhatsappDifferent
                ]);

                $whatsappUrl = teacherWhatsappMessage($teacher, $phone);
            }

            return view('admission.success', compact('whatsappUrl', 'type'));

        } catch (\Exception $e) {
            Log::error("Public Admission Submission Failed: " . $e->getMessage(), [
                'type' => $type,
                'lead_id' => $lead->id,
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->with('error', 'Something went wrong during admission: ' . $e->getMessage());
        }
    }

}

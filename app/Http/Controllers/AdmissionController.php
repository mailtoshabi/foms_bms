<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentLead;
use App\Models\TeacherLead;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Facades\Hash;

class AdmissionController extends Controller
{

public function showForm($type,$token)
{

    if($type=='student'){
        $lead = StudentLead::where('form_token',$token)->firstOrFail();
    }

    elseif($type=='teacher'){
        $lead = TeacherLead::where('form_token',$token)->firstOrFail();
    }

    else{
        abort(404);
    }

    if ($lead->form_disabled) {
        abort(403,'This form has been disabled.');
    }

    if ($lead->form_expires_at && now()->gt($lead->form_expires_at)) {
        abort(403,'This link has expired.');
    }

    if(!$lead->form_opened_at){
        $lead->update([
            'form_opened_at'=>now()
        ]);
    }

    $countries = \App\Models\Country::orderBy('name', 'asc')->get();
    return view('admission.form',compact('lead','type', 'countries'));
}



public function submitForm(Request $request,$type,$token)
{

    if($type=='student'){
        $lead = StudentLead::where('form_token',$token)->firstOrFail();
    }

    elseif($type=='teacher'){
        $lead = TeacherLead::where('form_token',$token)->firstOrFail();
    }

    else{
        abort(404);
    }

    $photo=null;
    $idProof=null;

    if ($request->hasFile('photo')) {
        $photo=$request->file('photo')->store('profiles/photos','public');
    }

    if ($request->hasFile('id_proof')) {
        $idProof=$request->file('id_proof')->store('profiles/id_proofs','public');
    }


    $phone = $request->contact_number;


    if($type=='student'){

        $model = Student::create([

            'student_lead_id'    => $lead->id,
            'country_id'         => $lead->country_id,

            'name'           => $request->name,
            'dob'            => $request->dob,
            'email'          => $request->email,
            'contact_number' => $request->contact_number,
            'whatsapp_number'=> $request->whatsapp_number ?? $request->contact_number,
            'parent_name'    => $request->parent_name,
            'address'        => $request->address,

            'phone'    => $phone,
            'password' => Hash::make($phone),

            'photo'    => $photo,
            'id_proof' => $idProof,

            'classes_per_week' => $request->classes_per_week,
            'selected_days'    => $request->selected_days ?? [],
            'time_slot'        => $request->time_slot,
            'starting_date'    => $request->starting_date,

            'status' => 'active'
        ]);

        $lead->update([
            'status'=>'admitted',
            'form_disabled'=>true
        ]);

        $whatsappUrl = studentWhatsappMessage($model,$phone);

    }


    if($type=='teacher'){

        $model = Teacher::create([

            'teacher_lead_id'=>$lead->id,

            'name'=>$request->name,
            'dob'=>$request->dob,
            'email'=>$request->email,
            'contact_number'=>$request->contact_number,
            'whatsapp_number'=>$request->whatsapp_number ?? $request->contact_number,
            'qualification'=>$request->qualification,
            'experience'=>$request->experience,
            'upi_number'=>$request->upi_number,
            'address'=>$request->address,

            'phone'=>$phone,
            'password'=>Hash::make($phone),

            'photo'=>$photo,
            'id_proof'=>$idProof,

            'status'=>'active'
        ]);

        $lead->update([
            'status'=>'approved',
            'form_disabled'=>true
        ]);

        $whatsappUrl = teacherWhatsappMessage($model,$phone);

    }

    return view('admission.success',compact('whatsappUrl','type'));

}

}

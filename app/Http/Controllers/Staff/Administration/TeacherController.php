<?php

namespace App\Http\Controllers\Staff\Administration;

use App\Http\Controllers\Controller;
use App\Models\ClassNote;
use App\Models\ClassRoom;
use Illuminate\Http\Request;
use App\Models\Teacher;
use App\Models\Country;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class TeacherController extends Controller
{

    public function index(Request $request)
    {
        $teachers = Teacher::query()->with(['lead', 'country']);

        if ($request->filled('search')) {
            $teachers->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('contact_number', 'like', '%' . $request->search . '%');
            });
        }

        $teachers = $teachers->latest()->paginate(utility('pagination', 50));

        return view('staff.teachers.index', compact('teachers'));
    }


    public function create()
    {
        $countries = Country::orderBy('name', 'asc')->get();
        return view('staff.teachers.create', compact('countries'));
    }


    public function store(Request $request)
    {
        $request->merge([
            'contact_number' => preg_replace('/[^0-9]/', '', $request->contact_number),
            'whatsapp_number' => preg_replace('/[^0-9]/', '', $request->whatsapp_number),
        ]);

        $request->merge(['phone' => $request->contact_number]);
        $request->validate([
            'name' => 'required',
            'country_id' => 'required|exists:countries,id',
            'contact_number' => 'required|string|digits_between:7,15',
            'phone' => [
                'required',
                Rule::unique('teachers')->where('country_id', $request->country_id)
            ],
            'password' => 'required|min:6'
        ]);

        $photo = null;
        $idProof = null;

        if ($request->hasFile('photo')) {
            $photo = $request->file('photo')->store('teachers/photos', 'public');
        }

        if ($request->hasFile('id_proof')) {
            $idProof = $request->file('id_proof')->store('teachers/id_proofs', 'public');
        }

        $country = Country::find($request->country_id);
        $isWhatsappDifferent = $request->has('is_whatsapp_different');
        if ($isWhatsappDifferent) {
            $whatsapp_number = $request->whatsapp_number;
        } else {
            $countryCode = $country ? preg_replace('/[^0-9]/', '', $country->code) : '91';
            $whatsapp_number = $countryCode . $request->contact_number;
        }

        Teacher::create([
            'teacher_lead_id' => null,
            'country_id' => $request->country_id,
            'is_whatsapp_different' => $isWhatsappDifferent,

            'name' => $request->name,
            'dob' => $request->dob,
            'email' => $request->email,
            'contact_number' => $request->contact_number,
            'whatsapp_number' => $whatsapp_number,
            'upi_number' => $request->upi_number,
            'address' => $request->address,

            'qualification' => $request->qualification,
            'experience' => $request->experience,

            'phone' => $request->phone,
            'password' => Hash::make($request->password),

            'photo' => $photo,
            'id_proof' => $idProof,

            'status' => 'active'
        ]);

        return redirect()
            ->route('staff.teachers.index')
            ->with('success', 'Teacher created.');
    }


    public function edit($id)
    {
        $teacher = Teacher::findOrFail(decrypt($id));
        $countries = Country::orderBy('name', 'asc')->get();

        return view('staff.teachers.create', compact('teacher', 'countries'));
    }


    public function update(Request $request, $id)
    {
        $teacher = Teacher::findOrFail(decrypt($id));

        $request->merge([
            'contact_number' => preg_replace('/[^0-9]/', '', $request->contact_number),
            'whatsapp_number' => preg_replace('/[^0-9]/', '', $request->whatsapp_number),
        ]);

        $request->merge(['phone' => $request->contact_number]);

        $request->validate([
            'name' => 'required|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'contact_number' => 'required|string|digits_between:7,15',
            'phone' => [
                'required',
                Rule::unique('teachers')->where('country_id', $request->country_id)->ignore($teacher->id)
            ],
            'email' => 'nullable|email|max:255',
            'dob' => 'nullable|date',
            'qualification' => 'nullable|string|max:255',
            'experience' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'upi_number' => 'nullable|string|max:20',
            'whatsapp_number' => 'nullable|string|digits_between:7,15',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'id_proof' => 'nullable|file|max:4096',
            'password' => 'nullable|min:6',
        ]);

        $country = Country::find($request->country_id);
        $isWhatsappDifferent = $request->has('is_whatsapp_different');
        if ($isWhatsappDifferent) {
            $whatsapp_number = $request->whatsapp_number;
        } else {
            $countryCode = $country ? preg_replace('/[^0-9]/', '', $country->code) : '91';
            $whatsapp_number = $countryCode . $request->contact_number;
        }

        $data = $request->only([
            'name',
            'country_id',
            'dob',
            'email',
            'contact_number',
            'upi_number',
            'address',
            'qualification',
            'experience',
            'phone',
            'status',
        ]);

        $data['whatsapp_number'] = $whatsapp_number;
        $data['is_whatsapp_different'] = $isWhatsappDifferent;

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $teacher->update($data);

        return redirect()
            ->route('staff.teachers.index')
            ->with('success', 'Teacher updated.');
    }

    public function show($id)
    {
        $teacher = Teacher::with([
            'classRooms.course',
            'classRooms.classType',
            'salaries'
        ])->findOrFail(decrypt($id));

        $assignedClasses = $teacher->classRooms->pluck('id')->toArray();

        $notes = ClassNote::where('teacher_id', $teacher->id)->latest()->get();

        return view('staff.teachers.show', [
            'teacher' => $teacher,
            'assignedClasses' => $assignedClasses,
            'notes' => $notes
        ]);
    }


    public function destroy($id)
    {
        $teacher = Teacher::findOrFail(decrypt($id));
        $teacher->delete(); // soft delete

        return back()->with('success', "Teacher \"{$teacher->name}\" deleted successfully.");
    }

    public function search(Request $request)
    {
        $term = $request->input('q', '');
        $results = Teacher::where('name', 'like', "%{$term}%")
            ->orWhere('contact_number', 'like', "%{$term}%")
            ->limit(30)
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'text' => $t->name,
            ]);

        return response()->json(['results' => $results]);
    }
}

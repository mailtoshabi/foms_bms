<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $query = Staff::with('roles');

        if ($request->filled('name')) {
            $query->where('name','like','%'.$request->name.'%');
        }

        if ($request->filled('phone')) {
            $query->where('phone','like','%'.$request->phone.'%');
        }

        if ($request->filled('status')) {
            if ($request->status == 'active') {
                $query->where('is_blocked',0);
            } else {
                $query->where('is_blocked',1);
            }
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', function($q) use ($request){
                $q->where('roles.id',$request->role);
            });
        }

        $staffs = $query->latest()->paginate(10)->withQueryString();

        $roles = Role::all();

        return view('admin.staffs.index', compact('staffs','roles'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('admin.staffs.create',compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'phone'         => 'required|string|max:20|unique:staffs,phone',
            'email'         => 'nullable|email|max:255',
            'password'      => 'required|min:4',
            'photo'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'id_proof'      => 'nullable|file|max:4096',
            'roles'         => 'nullable|array'
        ]);

        DB::beginTransaction();

        try {

            $data = $request->only([
                'name',
                'phone',
                'email',
                'address',
                'gpay_number'
            ]);

            // Password hash
            $data['password'] = Hash::make($request->password);

            // ================= Upload Photo =================
            if ($request->hasFile('photo')) {
                $data['photo'] = $request->file('photo')->store('staffs/photos','public');
            }

            // ================= Upload ID Proof =================
            if ($request->hasFile('id_proof')) {
                $data['id_proof'] = $request->file('id_proof')->store('staffs/id_proofs','public');
            }

            // ================= Create Staff =================
            $staff = Staff::create($data);

            // ================= Attach Roles =================
            if ($request->filled('roles')) {
                $staff->roles()->sync($request->roles);
            }

            DB::commit();

            return redirect()
                ->route('admin.staffs.index')
                ->with('success','Staff created successfully');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->with('error','Something went wrong');
        }
    }

    public function edit($id)
    {
        $staff = Staff::with('roles')->findOrFail(decrypt($id));
        $roles = Role::all();
        return view('admin.staffs.create',compact('staff','roles'));
    }

    public function update(Request $request)
    {
        $staffId = decrypt($request->staff_id);

        $staff = Staff::findOrFail($staffId);

        $request->validate([
            'name'      => 'required|string|max:255',
            'phone'     => 'required|string|max:20|unique:staffs,phone,' . $staff->id,
            'email'     => 'nullable|email|max:255',
            'password'  => 'nullable|min:4',
            'photo'     => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'id_proof'  => 'nullable|file|max:4096',
            'roles'     => 'nullable|array'
        ]);

        DB::beginTransaction();

        try {

            $data = $request->only([
                'name',
                'phone',
                'email',
                'address',
                'gpay_number'
            ]);

            // ================= Password Update =================
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            // ================= Photo Upload =================
            if ($request->hasFile('photo')) {

                if ($staff->photo && Storage::disk('public')->exists($staff->photo)) {
                    Storage::disk('public')->delete($staff->photo);
                }

                $data['photo'] = $request->file('photo')->store('staffs/photos','public');
            }

            // ================= ID Proof Upload =================
            if ($request->hasFile('id_proof')) {

                if ($staff->id_proof && Storage::disk('public')->exists($staff->id_proof)) {
                    Storage::disk('public')->delete($staff->id_proof);
                }

                $data['id_proof'] = $request->file('id_proof')->store('staffs/id_proofs','public');
            }

            // ================= Update Staff =================
            $staff->update($data);

            // ================= Sync Roles =================
            if ($request->has('roles')) {
                $staff->roles()->sync($request->roles);
            }

            DB::commit();

            return redirect()
                ->route('admin.staffs.index')
                ->with('success','Staff updated successfully');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->with('error','Something went wrong');
        }
    }

    public function destroy($id)
    {
        Staff::findOrFail(decrypt($id))->delete();
        return back()->with('success','Deleted');
    }

    public function toggleBlock($id)
    {
        $staff = Staff::findOrFail(decrypt($id));
        $staff->is_blocked = !$staff->is_blocked;
        $staff->save();

        return back()->with('success','Status changed');
    }
}

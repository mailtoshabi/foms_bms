<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('staffs')->latest()->get();
        return view('admin.roles.index',compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'=>'required|unique:roles'
        ]);

        Role::create([
            'name'=>strtolower($request->name)
        ]);

        return back()->with('success','Role created');
    }

    public function destroy($id)
    {
        Role::findOrFail(decrypt($id))->delete();

        return back()->with('success','Role deleted');
    }

    public function updateName(Request $request)
    {
        $role = Role::findOrFail(decrypt($request->id));
        $role->update([
            'name'=>strtolower($request->name)
        ]);

        return response()->json(['success'=>true]);
    }

    public function destroyAjax(Request $request)
    {
        Role::findOrFail(decrypt($request->id))->delete();
        return response()->json(['success'=>true]);
    }
}

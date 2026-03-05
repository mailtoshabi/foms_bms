<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Utility;

class UtilityController extends Controller
{
    public function index()
    {
        $utilities = Utility::pluck('value','key')->toArray();

        return view('admin.utilities.index', compact('utilities'));
    }

    public function update(Request $request)
    {
        foreach ($request->except('_token') as $key => $value) {
            Utility::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return back()->with('success','Settings updated successfully.');
    }
}

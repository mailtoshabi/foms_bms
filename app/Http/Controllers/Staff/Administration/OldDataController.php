<?php

namespace App\Http\Controllers\Staff\Administration;

use App\Http\Controllers\Controller;
use App\Imports\ClassRoomStartingDateImport;
use App\Imports\StudentOldDataImport;
use App\Imports\StudentClassRoomImport;
use App\Imports\TeacherOldDataImport;
use App\Imports\TeacherClassRoomImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class OldDataController extends Controller
{
    public function index()
    {
        return view('staff.old_data.index');
    }

    public function importStartingDates(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new ClassRoomStartingDateImport, $request->file('file'));
            return back()->with('success', 'Classroom dates updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error during import: ' . $e->getMessage());
        }
    }

    public function importStudentData(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new StudentOldDataImport, $request->file('file'));
            return back()->with('success', 'Student data updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error during import: ' . $e->getMessage());
        }
    }

    public function importStudentClassRoom(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new StudentClassRoomImport, $request->file('file'));
            return back()->with('success', 'Student assignments updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error during import: ' . $e->getMessage());
        }
    }

    public function importTeacherData(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new TeacherOldDataImport, $request->file('file'));
            return back()->with('success', 'Teacher details updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error during import: ' . $e->getMessage());
        }
    }

    public function importTeacherClassRoom(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new TeacherClassRoomImport, $request->file('file'));
            return back()->with('success', 'Teacher assignments updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error during import: ' . $e->getMessage());
        }
    }
}

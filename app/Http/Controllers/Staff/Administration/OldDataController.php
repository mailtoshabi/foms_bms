<?php

namespace App\Http\Controllers\Staff\Administration;

use App\Http\Controllers\Controller;
use App\Imports\ClassRoomStartingDateImport;
use App\Imports\StudentOldDataImport;
use App\Imports\StudentClassRoomImport;
use App\Imports\TeacherOldDataImport;
use App\Imports\TeacherClassRoomImport;
use App\Imports\StudentBulkCreateImport;
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
        $request->validateWithBag('classrooms', [
            'file' => 'required|extensions:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new ClassRoomStartingDateImport, $request->file('file'));
            return back()->with('success_classrooms', 'Classroom dates updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error_classrooms', 'Error during import: ' . $e->getMessage());
        }
    }

    public function importStudentData(Request $request)
    {
        $request->validateWithBag('students', [
            'file' => 'required|extensions:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new StudentOldDataImport, $request->file('file'));
            return back()->with('success_students', 'Student data updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error_students', 'Error during import: ' . $e->getMessage());
        }
    }

    public function importStudentClassRoom(Request $request)
    {
        $request->validateWithBag('student_assignments', [
            'file' => 'required|extensions:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new StudentClassRoomImport, $request->file('file'));
            return back()->with('success_student_assignments', 'Student assignments updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error_student_assignments', 'Error during import: ' . $e->getMessage());
        }
    }

    public function importTeacherData(Request $request)
    {
        $request->validateWithBag('teachers', [
            'file' => 'required|extensions:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new TeacherOldDataImport, $request->file('file'));
            return back()->with('success_teachers', 'Teacher details updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error_teachers', 'Error during import: ' . $e->getMessage());
        }
    }

    public function importTeacherClassRoom(Request $request)
    {
        $request->validateWithBag('teacher_assignments', [
            'file' => 'required|extensions:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new TeacherClassRoomImport, $request->file('file'));
            return back()->with('success_teacher_assignments', 'Teacher assignments updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error_teacher_assignments', 'Error during import: ' . $e->getMessage());
        }
    }

    public function bulkCreateStudents(Request $request)
    {
        $request->validateWithBag('students_bulk_create', [
            'file' => 'required|extensions:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new StudentBulkCreateImport, $request->file('file'));
            return back()->with('success_students_bulk_create', 'Students created successfully.');
        } catch (\Exception $e) {
            return back()->with('error_students_bulk_create', 'Error during import: ' . $e->getMessage());
        }
    }
}

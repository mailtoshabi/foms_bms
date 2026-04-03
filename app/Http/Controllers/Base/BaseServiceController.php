<?php

namespace App\Http\Controllers\Base;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\ClassService;
use App\Services\FeeService;
use App\Services\AttendanceService;
use App\Services\SalaryService;

class BaseServiceController extends Controller
{
    protected $staff;
    protected $admin;
    protected $user; // unified current user

    protected $classService;
    protected $feeService;
    protected $attendanceService;
    protected $salaryService;

    public function __construct(
        ClassService $classService,
        FeeService $feeService,
        AttendanceService $attendanceService,
        SalaryService $salaryService
    )
    {
        /*
        |--------------------------------------------------------------------------
        | Detect Logged User (Admin OR Staff)
        |--------------------------------------------------------------------------
        */

        $this->staff = Auth::guard('staff')->user();
        $this->admin = Auth::guard('admin')->user();

        // unified reference
        $this->user = $this->admin ?? $this->staff;

        /*
        |--------------------------------------------------------------------------
        | Inject Services
        |--------------------------------------------------------------------------
        */
        $this->classService = $classService;
        $this->feeService = $feeService;
        $this->attendanceService = $attendanceService;
        $this->salaryService = $salaryService;
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    protected function currentStaff()
    {
        return $this->staff;
    }

    protected function currentAdmin()
    {
        return $this->admin;
    }

    protected function currentUser()
    {
        return $this->user;
    }

    protected function isAdmin()
    {
        return !is_null($this->admin);
    }

    protected function isStaff()
    {
        return !is_null($this->staff);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Services\ClassService;
use App\Services\FeeService;
use App\Services\AttendanceService;

class BaseServiceController extends Controller
{
    protected $staff;
    protected $admin;
    protected $user; // unified current user

    protected $classService;
    protected $feeService;
    protected $attendanceService;

    public function __construct(
        ClassService $classService,
        FeeService $feeService,
        AttendanceService $attendanceService
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

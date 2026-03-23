<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\ClassRoomController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\StaffMessageController;
use App\Http\Controllers\AdmissionController;

Route::get('/', function () {
    return view('home');
});

Route::get('/all_cache', function() {

    Artisan::call('cache:clear');
    Artisan::call('optimize');
    Artisan::call('route:cache');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('config:cache');
    return '<h1>All cache cleared</h1>';
});


Route::get('/admission/{type}/{token}', [AdmissionController::class,'showForm'])
    ->name('admission.form');

Route::post('/admission/{type}/{token}', [AdmissionController::class,'submitForm'])
    ->name('admission.submit');

Route::prefix('admin')->name('admin.')->group(function () {

    Route::get('/', function () {
        return view('admin.auth.login');
    });

    // Login
    Route::get('/login', [AdminLoginController::class,'showLoginForm'])->name('login');
    Route::post('/login', [AdminLoginController::class,'login'])->name('login.submit');

    // Logout
    Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');

    Route::middleware(['auth:admin'])->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'dashboard'])
            ->name('dashboard');

        Route::controller(StaffController::class)
        ->prefix('staffs')
        ->name('staffs.')
        ->group(function () {

            // List
            Route::get('/', 'index')->name('index');

            // Create
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');

            // Edit
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::put('/update', 'update')->name('update');

            // Delete
            Route::delete('/delete/{id}', 'destroy')->name('destroy');

            // Block / Unblock
            Route::get('/toggle-block/{id}', 'toggleBlock')->name('toggleBlock');
        });

        Route::controller(RoleController::class)
        ->prefix('roles')
        ->name('roles.')
        ->group(function () {

            Route::get('/', 'index')->name('index');
            Route::post('/store', 'store')->name('store');
            Route::delete('/delete/{id}', 'destroy')->name('destroy');
            Route::post('/roles/update-name','updateName')->name('updateName');
            Route::post('/roles/delete-ajax','destroyAjax')->name('destroyAjax');

        });

        Route::controller(CourseController::class)
        ->prefix('courses')
        ->name('courses.')
        ->group(function(){

            Route::get('/','index')->name('index');
            Route::get('/create','create')->name('create');
            Route::post('/store','store')->name('store');

            Route::get('/edit/{id}','edit')->name('edit');
            Route::put('/update','update')->name('update');

            Route::delete('/delete/{id}','destroy')->name('destroy');
        });

        Route::controller(ClassRoomController::class)
        ->prefix('class_rooms')
        ->name('class_rooms.')
        ->group(function () {

            Route::get('/','index')->name('index');
            Route::get('/create','create')->name('create');
            Route::post('/store','store')->name('store');

            Route::get('/edit/{id}','edit')->name('edit');
            Route::put('/update','update')->name('update');

            Route::delete('/delete/{id}','destroy')->name('destroy');

            Route::get('/status/{id}','changeStatus')->name('changeStatus');
        });

        Route::controller(StaffMessageController::class)
        ->prefix('messages')
        ->name('messages.')
        ->group(function () {

            Route::get('/','index')->name('index');
            Route::get('/create','create')->name('create');
            Route::post('/store','store')->name('store');
            Route::get('/show/{id}','show')->name('show');
            Route::post('/reply/{id}','reply')->name('reply');

        });

    });

});


use App\Http\Controllers\Staff\Auth\LoginController as StaffLoginController;
use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use App\Http\Controllers\Staff\Enrolment\ClassRoomController as EnrolmentClassRoomController;
use App\Http\Controllers\Staff\Enrolment\StudentLeadController;
use App\Http\Controllers\Staff\Enrolment\StudentController;
use App\Http\Controllers\Staff\MessageController;
use App\Http\Controllers\Staff\Administration\TeacherLeadController;
use App\Http\Controllers\Staff\Administration\TeacherController;
use App\Http\Controllers\Staff\Administration\TeacherAssignmentController;
use App\Http\Controllers\Staff\Administration\TeacherSalaryController;

Route::prefix('departments')->name('staff.')->group(function () {

    Route::get('/', function () {
        return view('staff.auth.login');
    });

    Route::get('login', [StaffLoginController::class, 'showLoginForm'])
        ->name('login');

    Route::post('login', [StaffLoginController::class, 'login'])
        ->name('login.submit');

    Route::post('logout', [StaffLoginController::class, 'logout'])
        ->name('logout');

    Route::middleware(['auth:staff'])->group(function () {

        Route::get('/dashboard', [StaffDashboardController::class, 'dashboard'])
            ->name('dashboard');



        Route::middleware('role:id_enrolment_dept')
            ->group(function () {

            Route::resource('student-leads', StudentLeadController::class);
            Route::post(
                'student-leads/{lead}/notes',
                [StudentLeadController::class, 'storeNote']
            )->name('student-leads.notes.store');

            Route::post(
                'student-leads/{lead}/convert',
                [StudentLeadController::class, 'convertToStudent']
            )->name('student-leads.convert');

            Route::post(
            '/students/assign-class',
            [StudentController::class,'assignClass']
            )->name('students.assign.class');

            Route::post(
            '/students/fee-exemption',
            [StudentController::class,'saveFeeExemption']
            )->name('students.fee.exemption');

            Route::resource('students', StudentController::class)
            ->names('students');

            Route::controller(EnrolmentClassRoomController::class)
            ->prefix('class_rooms')
            ->name('class_rooms.')
            ->group(function () {

                Route::get('/','index')->name('index');
                Route::get('/create','create')->name('create');
                Route::post('/store','store')->name('store');

                Route::get('/edit/{id}','edit')->name('edit');
                Route::put('/update','update')->name('update');
                Route::get('/show/{id}', 'show')->name('show');

                Route::delete('/delete/{id}','destroy')->name('destroy');

                Route::get('/status/{id}','changeStatus')->name('changeStatus');

                Route::post(
                '/class-rooms/assign-teacher','assignTeacher'
                )->name('assign.teacher');

                Route::post(
                '/class-rooms/remove-teacher','removeTeacher'
                )->name('remove.teacher');

                Route::post(
                '/class-rooms/assign-students','assignStudents'
                )->name('assign.students');

                Route::post(
                '/class-rooms/remove-student','removeStudent'
                )->name('remove.student');
            });

            Route::controller(MessageController::class)
            ->prefix('messages')
            ->name('messages.')
            ->group(function () {

                Route::get('/','index')->name('index');
                Route::get('/create','create')->name('create');
                Route::post('/store','store')->name('store');
                Route::get('/show/{id}','show')->name('show');
                Route::post('/reply/{id}','reply')->name('reply');

            });

        });

    Route::middleware('role:id_administrator_dept')
            ->group(function () {



            Route::resource('teacher-leads', TeacherLeadController::class);
            Route::post(
                'teacher-leads/{lead}/notes',
                [TeacherLeadController::class, 'storeNote']
            )->name('teacher-leads.notes.store');

            Route::post(
                'teacher-leads/{lead}/convert',
                [TeacherLeadController::class, 'convertToTeacher']
            )->name('teacher-leads.convert');





            Route::controller(EnrolmentClassRoomController::class)
            ->prefix('class_rooms')
            ->name('class_rooms.')
            ->group(function () {

                Route::get('/','index')->name('index');
                Route::get('/create','create')->name('create');
                Route::post('/store','store')->name('store');

                Route::get('/edit/{id}','edit')->name('edit');
                Route::put('/update','update')->name('update');

                Route::delete('/delete/{id}','destroy')->name('destroy');

                Route::get('/status/{id}','changeStatus')->name('changeStatus');
            });

            Route::controller(MessageController::class)
            ->prefix('messages')
            ->name('messages.')
            ->group(function () {

                Route::get('/','index')->name('index');
                Route::get('/create','create')->name('create');
                Route::post('/store','store')->name('store');
                Route::get('/show/{id}','show')->name('show');
                Route::post('/reply/{id}','reply')->name('reply');

            });

            Route::post(
            '/teachers/assign-classrooms',
            [TeacherAssignmentController::class,'assign']
            )->name('teachers.assign.classrooms');

            Route::put(
            '/teachers/update-wage',
            [TeacherAssignmentController::class,'updateWage']
            )->name('teachers.update.wage');

            Route::resource('teachers', TeacherController::class)
            ->names('teachers');

            // Route::delete(
            //     '/teachers/remove-class',
            //     [TeacherAssignmentController::class,'destroy']
            //     )->name('teachers.remove.class');

                Route::delete(
                    '/teachers/{teacher}/classrooms/{class_room}',
                    [TeacherAssignmentController::class,'destroy']
                )->name('teachers.classrooms.destroy');

            Route::get(
                '/{teacher}/salary/create',
                [TeacherSalaryController::class,'create']
            )->name('teacher-salaries.create');

            Route::post(
                '/{teacher}/salary',
                [TeacherSalaryController::class,'store']
            )->name('teacher-salaries.store');

            Route::put('/teacher-salaries/{salary}',
                [TeacherSalaryController::class,'update'])
                ->name('teacher-salaries.update');

                Route::delete('/teacher-salaries/{salary}',
                    [TeacherSalaryController::class,'destroy'])
                    ->name('teacher-salaries.destroy');


        });


    });


});


use App\Http\Controllers\Teacher\Auth\LoginController as TeacherLoginController;
use App\Http\Controllers\Teacher\ClassNoteController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\TeacherController as TeacherServiceController;

Route::prefix('teacher')->name('teacher.')->group(function () {
Route::get('/', function () {
        return view('teacher.auth.login');
    });

// Login
Route::get('/login', [TeacherLoginController::class,'showLoginForm'])->name('login');
Route::post('/login', [TeacherLoginController::class,'login'])->name('login.submit');
});


Route::prefix('teacher')
    ->middleware('auth:teacher')
    ->name('teacher.')
    ->group(function () {
        Route::post('/logout', [TeacherLoginController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [TeacherDashboardController::class, 'dashboard'])
            ->name('dashboard');

        Route::get('classes',
            [TeacherServiceController::class,'classes']
        )->name('classes.index');

        Route::get('classes/{id}',
            [TeacherServiceController::class,'classShow']
        )->name('classes.show');

        Route::post('classes/start',
            [TeacherServiceController::class,'startClass']
        )->name('classes.start');

        Route::put(
        '/class-hours/{id}',
        [TeacherServiceController::class,'updateClassHour']
        )->name('class-hours.update');

        // Route::put(
        // '/class-hours/{id}/complete',
        // [TeacherServiceController::class,'markClassHourCompleted']
        // )->name('class-hours.complete');

        // Get students
        Route::get(
        '/class-hours/{id}/students',
        [TeacherServiceController::class,'getClassHourStudents']
        );

        // Submit attendance + complete
        Route::post(
        '/class-hours/{id}/complete',
        [TeacherServiceController::class,'markClassHourCompleted']
        )->name('class-hours.complete');

        Route::resource('class-notes',ClassNoteController::class);
    });


use App\Http\Controllers\Student\Auth\LoginController as StudentLoginController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;

Route::prefix('student')->name('student.')->group(function () {
Route::get('/', function () {
        return view('student.auth.login');
    });

// Login
Route::get('/login', [StudentLoginController::class,'showLoginForm'])->name('login');
Route::post('/login', [StudentLoginController::class,'login'])->name('login.submit');
});

    Route::prefix('student')
    ->middleware('auth:student')
    ->name('student.')
    ->group(function () {
        Route::post('/logout', [StudentLoginController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [StudentDashboardController::class, 'dashboard'])
            ->name('dashboard');

    });


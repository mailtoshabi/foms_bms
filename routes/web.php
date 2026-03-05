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
use App\Http\Controllers\UtilityController;

Route::get('/', function () {
    return view('admin.auth.login');
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

// Route::prefix('admin')->name('admin.')->middleware('auth:admin')->group(function () {

//     Route::get('utilities', [UtilityController::class, 'index'])
//         ->name('utilities.index');

//     Route::post('utilities', [UtilityController::class, 'update'])
//         ->name('utilities.update');

// });

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
        ->prefix('classes')
        ->name('classes.')
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
use App\Http\Controllers\Staff\Enrolment\StudentLeadController;

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

        });
    });


});



Route::prefix('teacher')
    ->middleware('auth:teacher')
    ->name('teacher.')
    ->group(function () {



});

Route::prefix('student')
    ->middleware('auth:student')
    ->name('student.')
    ->group(function () {


});

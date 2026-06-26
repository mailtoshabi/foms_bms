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
use App\Http\Controllers\Admin\SalaryController as AdminSalaryController;
use App\Http\Controllers\Admin\StudentTeacherMessageController;
use App\Http\Controllers\AdmissionController;
use App\Exports\DefaultersExport;
use App\Http\Controllers\Admin\ReportController;
use Maatwebsite\Excel\Facades\Excel;

Route::get('/', function () {
    return view('home');
});

Route::get('/all_cache', function () {

    Artisan::call('cache:clear');
    Artisan::call('optimize');
    Artisan::call('route:cache');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('config:cache');

    try {
        Artisan::call('storage:link');
        $storageMsg = 'Storage linked.';
    } catch (\Exception $e) {
        $storageMsg = 'Storage link already exists.';
    }

    return '<h1>All cache cleared</h1><p>' . $storageMsg . '</p>';
});


Route::get('/admission/{type}/{token}', [AdmissionController::class, 'showForm'])
    ->name('admission.form');

Route::post('/admission/{type}/{token}', [AdmissionController::class, 'submitForm'])
    ->name('admission.submit');

Route::prefix('admin')->name('admin.')->group(function () {

    Route::get('/', function () {
        return view('admin.auth.login');
    });

    // Login
    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminLoginController::class, 'login'])->name('login.submit');

    // Logout
    Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');

    Route::middleware(['auth:admin'])->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'dashboard'])
            ->name('dashboard');
        Route::get('/profile', [DashboardController::class, 'profile'])
            ->name('profile');

        Route::get('reports/fee', [ReportController::class, 'fees'])
            ->name('reports.fee');

        Route::get(
            'reports/fee/export',
            [ReportController::class, 'exportFee']
        )->name('reports.fee.export');

        Route::get('reports/fee-collection', [ReportController::class, 'feeCollection'])
            ->name('reports.fee.collection');

        Route::get('reports/fee-collection/export', [ReportController::class, 'exportFeeCollection'])
            ->name('reports.fee.collection.export');

        Route::delete('reports/fee/{id}', [ReportController::class, 'destroyFee'])
            ->name('reports.fee.destroy');

        Route::get('reports/finance-expense', [ReportController::class, 'financeExpense'])
            ->name('reports.finance.expense');

        Route::post('reports/finance-expense', [ReportController::class, 'storeExpense'])
            ->name('reports.finance.expense.store');



        Route::get('reports/teacher-salary', [ReportController::class, 'teacherSalary'])
            ->name('reports.teacher.salary');

        Route::get('reports/teacher-salary/export', [ReportController::class, 'exportTeacherSalary'])
            ->name('reports.teacher.salary.export');

        Route::get('/reports/teachers/leads', [ReportController::class, 'teacherLeadReport'])
            ->name('reports.teacher-leads');

        Route::get('/reports/teachers/leads/export', [ReportController::class, 'exportTeacherLeads'])
            ->name('reports.teacher-leads.export');

        Route::get('/reports/teachers', [ReportController::class, 'teacherReport'])
            ->name('reports.teachers');

        Route::get('/reports/teachers/export', [ReportController::class, 'exportTeachers'])
            ->name('reports.teachers.export');

        Route::get('/reports/teachers/leads/notes', [ReportController::class, 'teacherLeadNotes'])
            ->name('reports.teacher-lead-notes');

        Route::get('reports/teachers/{id}', [ReportController::class, 'showTeacher'])
            ->name('reports.teachers.show');

        Route::get('/reports/students/leads', [ReportController::class, 'studentLeadReport'])
            ->name('reports.student-leads');

        Route::get('/reports/students/leads/export', [ReportController::class, 'exportStudentLeads'])
            ->name('reports.student-leads.export');

        Route::get('reports/students/attendance', [ReportController::class, 'attendance'])
            ->name('reports.attendance');

        Route::get('reports/students/attendance/export', [ReportController::class, 'exportAttendance'])
            ->name('reports.attendance.export');

        Route::get('/reports/students', [ReportController::class, 'studentReport'])
            ->name('reports.students');

        Route::get('/reports/students/export', [ReportController::class, 'exportStudents'])
            ->name('reports.students.export');

        Route::get('/reports/student-advances', [ReportController::class, 'studentAdvances'])
            ->name('reports.student-advances');

        Route::get('/reports/staffs', [ReportController::class, 'staffReport'])
            ->name('reports.staffs');

        Route::get('/reports/staffs/export', [ReportController::class, 'exportStaffs'])
            ->name('reports.staffs.export');

        Route::get('/reports/staff-salary', [ReportController::class, 'staffSalaryReport'])
            ->name('reports.staff.salary');

        Route::get('/reports/staff-salary/export', [ReportController::class, 'exportStaffSalary'])
            ->name('reports.staff.salary.export');

        Route::get('/reports/students/leads/notes', [ReportController::class, 'studentLeadNotes'])
            ->name('reports.student-lead-notes');

        Route::get('reports/students/{id}', [ReportController::class, 'showStudent'])
            ->name('reports.students.show');

        Route::get('reports/students/{id}/toggle-block', [ReportController::class, 'toggleBlockStudent'])
            ->name('reports.students.toggleBlock');

        Route::delete('reports/students/{id}/relations/{related_id}', [ReportController::class, 'removeRelation'])
            ->name('students.relations.destroy');

        Route::post('reports/students/{id}/relations', [ReportController::class, 'addRelation'])
            ->name('students.relations.store');

        Route::get('reports/students/{id}/search-relations', [ReportController::class, 'searchStudentsForRelations'])
            ->name('students.search-relations');

        Route::post('/students/assign-class', [ReportController::class, 'assignClass'])
            ->name('students.assign.class');

        Route::post('/students/change-class', [ReportController::class, 'changeClass'])
            ->name('students.change.class');

        Route::post('/students/promote-class', [ReportController::class, 'promoteClass'])
            ->name('students.promote.class');

        Route::get('/students-active-classes/search', [ReportController::class, 'searchActiveClasses'])
            ->name('students.active-classes.search');

        Route::post('/students/{id}/wallet/toggle-autopay', [ReportController::class, 'toggleWalletAutopay'])
            ->name('students.wallet.toggle-autopay');

        Route::post('/fees/wallet/deposit', [ReportController::class, 'depositWallet'])
            ->name('fees.wallet.deposit');

        Route::post('/fees/wallet/refund', [ReportController::class, 'refundWallet'])
            ->name('fees.wallet.refund');

        Route::post('/fees/refund', [ReportController::class, 'refundFee'])
            ->name('fees.refund');

        Route::get('/fees/{id}/refunds', [ReportController::class, 'getRefunds'])
            ->name('fees.refunds');

        Route::get('reports/class-hours', [ReportController::class, 'classHours'])
            ->name('reports.class-hours');

        Route::get('teachers/search', [ReportController::class, 'searchTeachers'])
            ->name('teachers.search');


        Route::controller(StaffController::class)
            ->prefix('staffs')
            ->name('staffs.')
            ->group(function () {

                // List
                Route::get('/', 'index')->name('index');

                // Create
                Route::get('/create', 'create')->name('create');
                Route::post('/store', 'store')->name('store');

                // Show
                Route::get('/show/{id}', 'show')->name('show');

                // Edit
                Route::get('/edit/{id}', 'edit')->name('edit');
                Route::put('/update', 'update')->name('update');

                // Salary
                Route::post('/salary/store', 'storeSalary')->name('salary.store');
                Route::put('/salary/update', 'updateSalary')->name('salary.update');
                Route::put('/salary/payment/update', 'updatePayment')->name('salary.payment.update');
                Route::post('/salary/pay-balance', 'payBalance')->name('salary.pay-balance');
                Route::put('/salary/amount/update', 'updateSalaryAmount')->name('salary.amount.update');

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
                Route::post('/roles/update-name', 'updateName')->name('updateName');
                Route::post('/roles/delete-ajax', 'destroyAjax')->name('destroyAjax');

            });

        Route::controller(CourseController::class)
            ->prefix('courses')
            ->name('courses.')
            ->group(function () {

                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/store', 'store')->name('store');

                Route::get('/edit/{id}', 'edit')->name('edit');
                Route::put('/update', 'update')->name('update');

                Route::delete('/delete/{id}', 'destroy')->name('destroy');
            });

        Route::controller(ClassRoomController::class)
            ->prefix('class_rooms')
            ->name('class_rooms.')
            ->group(function () {

                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/store', 'store')->name('store');

                Route::get('/edit/{id}', 'edit')->name('edit');
                Route::put('/update', 'update')->name('update');
                Route::get('/show/{id}', 'show')->name('show');

                Route::delete('/delete/{id}', 'destroy')->name('destroy');

                Route::get('/status/{id}', 'changeStatus')->name('changeStatus');

                Route::post(
                    '/class-rooms/assign-teacher',
                    'assignTeacher'
                )->name('assign.teacher');

                Route::post(
                    '/class-rooms/remove-teacher',
                    'removeTeacher'
                )->name('remove.teacher');

                Route::post(
                    '/class-rooms/assign-students',
                    'assignStudents'
                )->name('assign.students');

                Route::post(
                    '/class-rooms/remove-student',
                    'removeStudent'
                )->name('remove.student');

                Route::get('/search', 'search')->name('search');
            });

        Route::controller(StaffMessageController::class)
            ->prefix('messages')
            ->name('messages.')
            ->group(function () {

                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/store', 'store')->name('store');
                Route::get('/show/{id}', 'show')->name('show');
                Route::post('/reply/{id}', 'reply')->name('reply');

            });

        Route::controller(StudentTeacherMessageController::class)
            ->prefix('student-teacher-messages')
            ->name('st-messages.')
            ->group(function () {

                Route::get('/', 'index')->name('index');
                Route::get('/show/{id}', 'show')->name('show');

            });

        Route::controller(AdminSalaryController::class)
            ->prefix('salaries')
            ->name('salaries.')
            ->group(function () {

                Route::get('/', 'index')->name('index');
                Route::post('/pay', 'pay')->name('pay');
                Route::post('/{salary}/deposit', 'moveToDeposit')->name('deposit');
                Route::post('/{salary}/release', 'releaseDeposit')->name('release');

            });

        Route::controller(\App\Http\Controllers\Admin\DepositController::class)
            ->prefix('deposits')
            ->name('deposits.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/pay', 'pay')->name('pay');
            });

        Route::controller(\App\Http\Controllers\Admin\FeeController::class)
            ->prefix('fees')
            ->name('fees.')
            ->group(function () {
                Route::get('/create', 'create')->name('create');
                Route::post('/store', 'store')->name('store');
                Route::get('/students/search', 'searchStudents')->name('students.search');
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
use App\Http\Controllers\Staff\Finance\FeeController;
use App\Http\Controllers\Staff\Finance\SalaryController;
use App\Http\Controllers\Staff\Finance\ExpenseController;
use App\Http\Controllers\Staff\Administration\OldDataController;
use App\Http\Controllers\Staff\ReportController as StaffReportController;


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

    // Route::middleware(['auth:staff'])->group(function () {
    Route::middleware([
        'auth:staff',
        \App\Http\Middleware\DailySalaryFeeRunner::class
    ])->group(function () {

        Route::get('/dashboard', [StaffDashboardController::class, 'dashboard'])
            ->name('dashboard');
        Route::get('/profile', [StaffDashboardController::class, 'profile'])
            ->name('profile');

        Route::controller(MessageController::class)
            ->prefix('messages')
            ->name('messages.')
            ->group(function () {

                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/store', 'store')->name('store');
                Route::get('/show/{id}', 'show')->name('show');
                Route::post('/reply/{id}', 'reply')->name('reply');

            });

        Route::controller(EnrolmentClassRoomController::class)
            ->prefix('class_rooms')
            ->name('class_rooms.')
            ->group(function () {
                Route::get('/search', 'search')->name('search');
            });

        Route::middleware('role:id_enrolment_dept,id_operation_dept')
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
                    'student-leads/{lead}/regenerate-link',
                    [StudentLeadController::class, 'regenerateLink']
                )->name('student-leads.regenerate-link');



                Route::post(
                    '/students/fee-exemption',
                    [StudentController::class, 'saveFeeExemption']
                )->name('students.fee.exemption');

                Route::post(
                    '/students/discount',
                    [StudentController::class, 'saveDiscount']
                )->name('students.discount');


                Route::get(
                    '/students/{id}/check-related',
                    [StudentController::class, 'checkRelated']
                )->name('students.check_related');

                Route::resource('students', StudentController::class)
                    ->names('students')
                    ->except(['index', 'show']);

            });

        Route::middleware('role:id_operation_dept')
            ->group(function () {

                Route::controller(EnrolmentClassRoomController::class)
                    ->prefix('class_rooms')
                    ->name('class_rooms.')
                    ->group(function () {

                        Route::delete('/delete/{id}', 'destroy')->name('destroy');

                        Route::get('/status/{id}', 'changeStatus')->name('changeStatus');
                    });
            });

        Route::middleware('role:id_administrator_dept,id_operation_dept')
            ->group(function () {

                Route::controller(EnrolmentClassRoomController::class)
                    ->prefix('class_rooms')
                    ->name('class_rooms.')
                    ->group(function () {

                        Route::post(
                            '/class-rooms/assign-teacher',
                            'assignTeacher'
                        )->name('assign.teacher');

                        Route::post(
                            '/class-rooms/remove-teacher',
                            'removeTeacher'
                        )->name('remove.teacher');

                        Route::post(
                            '/class-rooms/remove-student',
                            'removeStudent'
                        )->name('remove.student');

                    });

                Route::resource('teacher-leads', TeacherLeadController::class);
                Route::post(
                    'teacher-leads/{lead}/notes',
                    [TeacherLeadController::class, 'storeNote']
                )->name('teacher-leads.notes.store');

                Route::post(
                    'teacher-leads/{lead}/convert',
                    [TeacherLeadController::class, 'convertToTeacher']
                )->name('teacher-leads.convert');

                Route::post(
                    'teacher-leads/{lead}/regenerate-link',
                    [TeacherLeadController::class, 'regenerateLink']
                )->name('teacher-leads.regenerate-link');


                Route::post(
                    '/teachers/assign-classrooms',
                    [TeacherAssignmentController::class, 'assign']
                )->name('teachers.assign.classrooms');

                Route::put(
                    '/teachers/update-wage',
                    [TeacherAssignmentController::class, 'updateWage']
                )->name('teachers.update.wage');

                Route::put(
                    '/teachers/salaries/{salary}',
                    [TeacherSalaryController::class, 'update']
                )
                    ->name('teacher-salaries.update');

                Route::delete(
                    '/teachers/{teacher}/classrooms/{class_room}',
                    [TeacherAssignmentController::class, 'destroy']
                )->name('teachers.classrooms.destroy');

            });

        Route::middleware('role:id_finance_dept,id_operation_dept,id_enrolment_dept')
            ->group(function () {

                Route::get(
                    '/fees',
                    [FeeController::class, 'index']
                )->name('fees.index');

                Route::post(
                    '/fees/pay',
                    [FeeController::class, 'pay']
                )->name('fees.pay');

                Route::get(
                    '/fees/{id}/invoice',
                    [FeeController::class, 'invoice']
                )->name('fees.invoice');

                Route::get(
                    '/fees/{id}/invoice/download',
                    [FeeController::class, 'downloadInvoice']
                )->name('fees.invoice.download');

                Route::get(
                    '/fees/{id}/payments',
                    [FeeController::class, 'getPayments']
                )->name('fees.payments');

                Route::get(
                    '/fees/{id}/refunds',
                    [FeeController::class, 'getRefunds']
                )->name('fees.refunds');

                Route::post(
                    '/fees/send-notification',
                    [FeeController::class, 'sendNotification']
                )->name('fees.send-notification');

                Route::post(
                    '/fees/send-bulk-notifications',
                    [FeeController::class, 'sendBulkNotifications']
                )->name('fees.send-bulk-notifications');


                Route::delete(
                    '/fees/{id}',
                    [FeeController::class, 'destroy']
                )->name('fees.destroy');

            });

        Route::middleware('role:id_finance_dept,id_operation_dept')
            ->group(function () {

                Route::controller(ExpenseController::class)
                    ->prefix('expenses')
                    ->name('expenses.')
                    ->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::get('/create', 'create')->name('create');
                        Route::post('/store', 'store')->name('store');
                        Route::get('/edit/{id}', 'edit')->name('edit');
                        Route::put('/update/{id}', 'update')->name('update');
                        Route::delete('/delete/{id}', 'destroy')->name('destroy');
                    });
            });

        Route::middleware('role:id_hr_dept,id_operation_dept,id_administrator_dept')
            ->group(function () {
                Route::get(
                    '/teacher-salaries',
                    [SalaryController::class, 'index']
                )->name('salaries.index');

                Route::post(
                    '/teacher-salaries/pay',
                    [SalaryController::class, 'pay']
                )->name('salaries.pay');

                Route::get(
                    '/process/teacher/{id}/salary',
                    [TeacherSalaryController::class, 'processTeacherSalary']
                )->name('process.teacher.salary');

            });

        Route::middleware('role:id_hr_dept,id_operation_dept,id_administrator_dept,id_finance_dept')
            ->group(function () {
                Route::get('/class-hours', [\App\Http\Controllers\Staff\Administration\ClassHourController::class, 'index'])
                    ->name('class-hours.index');
            });

        Route::middleware('role:id_enrolment_dept,id_administrator_dept,id_hr_dept,id_finance_dept,id_operation_dept')
            ->group(function () {

                Route::controller(EnrolmentClassRoomController::class)
                    ->prefix('class_rooms')
                    ->name('class_rooms.')
                    ->group(function () {

                        Route::get('/', 'index')->name('index');
                        Route::get('/create', 'create')->name('create');
                        Route::post('/store', 'store')->name('store');

                        Route::get('/edit/{id}', 'edit')->name('edit');
                        Route::put('/update', 'update')->name('update');
                        Route::get('/show/{id}', 'show')->name('show');

                        Route::post(
                            '/class-rooms/assign-students',
                            'assignStudents'
                        )->name('assign.students');

                    });

            });

        Route::middleware('role:id_enrolment_dept,id_administrator_dept,id_hr_dept,id_finance_dept,id_operation_dept')
            ->group(function () {
                Route::get(
                    '/students',
                    [StudentController::class, 'index']
                )->name('students.index');

                Route::get(
                    '/students/{id}/toggle-block',
                    [StudentController::class, 'toggleBlock']
                )->name('students.toggleBlock');

                Route::delete(
                    '/students/{id}/relations/{related_id}',
                    [StudentController::class, 'removeRelation']
                )->name('students.relations.destroy');

                Route::post(
                    '/students/{id}/relations',
                    [StudentController::class, 'addRelation']
                )->name('students.relations.store');

                Route::get(
                    '/students/{id}/search-relations',
                    [StudentController::class, 'searchStudentsForRelations']
                )->name('students.search-relations');

                Route::post(
                    '/students/assign-class',
                    [StudentController::class, 'assignClass']
                )->name('students.assign.class');

                Route::post(
                    '/students/change-class',
                    [StudentController::class, 'changeClass']
                )->name('students.change.class');

                Route::post(
                    '/students/promote-class',
                    [StudentController::class, 'promoteClass']
                )->name('students.promote.class');
                Route::get(
                    '/students-active-classes/search',
                    [StudentController::class, 'searchActiveClasses']
                )->name('students.active-classes.search');
                Route::get(
                    '/students-search',
                    [StudentController::class, 'searchStudents']
                )->name('students.search');


            });

        Route::middleware('role:id_administrator_dept,id_hr_dept,id_operation_dept,id_finance_dept')
            ->group(function () {
                Route::get('teachers/search', [TeacherController::class, 'search'])
                    ->name('teachers.search');

                Route::resource('teachers', TeacherController::class)
                    ->names('teachers')->except(['show']);

                Route::get('/deposits', [\App\Http\Controllers\Staff\Finance\DepositController::class, 'index'])
                    ->name('deposits.index');
            });

        Route::middleware('role:id_operation_dept')->group(function () {
            Route::get('/old-data', [OldDataController::class, 'index'])->name('old_data.index');
            Route::post('/old-data/import', [OldDataController::class, 'importStartingDates'])->name('old_data.import');
            Route::post('/old-data/students-import', [OldDataController::class, 'importStudentData'])->name('old_data.students_import');
            Route::post('/old-data/student-assignments-import', [OldDataController::class, 'importStudentClassRoom'])->name('old_data.student_assignments_import');
            Route::post('/old-data/teachers-import', [OldDataController::class, 'importTeacherData'])->name('old_data.teachers_import');
            Route::post('/old-data/teacher-assignments-import', [OldDataController::class, 'importTeacherClassRoom'])->name('old_data.teacher_assignments_import');
            Route::post('/old-data/students-bulk-create', [OldDataController::class, 'bulkCreateStudents'])->name('old_data.students_bulk_create');
        });

        Route::middleware('role:id_hr_dept,id_finance_dept,id_operation_dept,id_administrator_dept')
            ->group(function () {
                Route::get('/reports/attendance', [StaffReportController::class, 'attendance'])
                    ->name('reports.attendance');
            });

        Route::get(
            '/students/{id}',
            [StudentController::class, 'show']
        )->name('students.show');
        Route::get(
            '/teachers/{id}',
            [TeacherController::class, 'show']
        )->name('teachers.show');

    });




});


use App\Http\Controllers\Teacher\Auth\LoginController as TeacherLoginController;
use App\Http\Controllers\Teacher\ClassNoteController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\MessageController as TeacherMessageController;
use App\Http\Controllers\Teacher\TeacherController as TeacherServiceController;

Route::prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('teacher.login');
    });

    // Login
    Route::get('/login', [TeacherLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [TeacherLoginController::class, 'login'])->name('login.submit');
});


Route::prefix('teacher')
    ->middleware('auth:teacher')
    ->name('teacher.')
    ->group(function () {
        Route::post('/logout', [TeacherLoginController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [TeacherDashboardController::class, 'dashboard'])
            ->name('dashboard');
        Route::get('/profile', [TeacherDashboardController::class, 'profile'])
            ->name('profile');
        Route::post('/profile/update-photo', [TeacherDashboardController::class, 'updatePhoto'])
            ->name('profile.update-photo');

        Route::get(
            'classes',
            [TeacherServiceController::class, 'classes']
        )->name('classes.index');

        Route::get(
            'sessions',
            [TeacherServiceController::class, 'sessions']
        )->name('sessions.index');

        Route::get(
            'classes/{id}',
            [TeacherServiceController::class, 'classShow']
        )->name('classes.show');

        Route::post(
            'classes/start',
            [TeacherServiceController::class, 'startClass']
        )->name('classes.start');

        Route::put(
            '/class-hours/{id}',
            [TeacherServiceController::class, 'updateClassHour']
        )->name('class-hours.update');

        // Get students
        Route::get(
            '/class-hours/{id}/students',
            [TeacherServiceController::class, 'getClassHourStudents']
        );

        // Join class (updates join_teacher_at)
        Route::get(
            '/class-hours/{id}/join',
            [TeacherServiceController::class, 'joinClass']
        )->name('class-hours.join');

        // Submit attendance + complete
        Route::post(
            '/class-hours/{id}/complete',
            [TeacherServiceController::class, 'markClassHourCompleted']
        )->name('class-hours.complete');

        Route::controller(ClassNoteController::class)
            ->prefix('notes')
            ->name('notes.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/store', 'store')->name('store');
                Route::delete('/destroy/{id}', 'destroy')->name('destroy');
                Route::get('/show/{id}', 'show')->name('show');
                Route::get('/file/{id}', 'downloadFile')->name('file.download');
                Route::get('/class-rooms/search', 'searchClassRooms')->name('class_rooms.search');
            });

        Route::controller(TeacherMessageController::class)
            ->prefix('messages')
            ->name('messages.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/store', 'store')->name('store');
                Route::get('/show/{id}', 'show')->name('show');
                Route::post('/reply/{id}', 'reply')->name('reply');
            });
    });


use App\Http\Controllers\Student\Auth\LoginController as StudentLoginController;
use App\Http\Controllers\Student\ClassController as StudentClassController;
use App\Http\Controllers\Student\ClassNoteController as StudentClassNoteController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\MessageController as StudentMessageController;

Route::prefix('student')->name('student.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('student.login');
    });

    // Login
    Route::get('/login', [StudentLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [StudentLoginController::class, 'login'])->name('login.submit');
});

Route::prefix('student')
    ->middleware([
        'auth:student',
        \App\Http\Middleware\CheckStudentBlocked::class
    ])
    ->name('student.')
    ->group(function () {
        Route::post('/logout', [StudentLoginController::class, 'logout'])->name('logout');
        Route::post('/switch/{id}', [StudentLoginController::class, 'switchAccount'])->name('switch');
        Route::get('/dashboard', [StudentDashboardController::class, 'dashboard'])
            ->name('dashboard');
        Route::get('/profile', [StudentDashboardController::class, 'profile'])
            ->name('profile');

        Route::controller(StudentClassNoteController::class)
            ->prefix('notes')
            ->name('notes.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/show/{id}', 'show')->name('show');
            });

        Route::controller(StudentClassController::class)
            ->prefix('classes')
            ->name('classes.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/join/{id}', 'joinClass')->name('join');
                Route::get('/{id}', 'show')->name('show');
            });

        Route::controller(StudentMessageController::class)
            ->prefix('messages')
            ->name('messages.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/store', 'store')->name('store');
                Route::get('/show/{id}', 'show')->name('show');
                Route::post('/reply/{id}', 'reply')->name('reply');
            });

    });


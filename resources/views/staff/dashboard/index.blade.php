@extends('staff.layouts.master')
@section('title') Dashboard @endsection
@section('css')

    <link href="{{ URL::asset('/assets/libs/admin-resources/admin-resources.min.css') }}" rel="stylesheet">

@endsection
@section('content')

    @php
        $staff = auth('staff')->user();
        $enrolmentRoleId = utility('id_enrolment_dept');
        $administratorRoleId = utility('id_administrator_dept');
        $financeRoleId = utility('id_finance_dept');
        $hrRoleId = utility('id_hr_dept');
        $operationRoleId = utility('id_operation_dept');

        $isEnrolment = $staff->hasRoleId($enrolmentRoleId) || $staff->hasRoleId($operationRoleId);
        $isAdministrator = $staff->hasRoleId($administratorRoleId) || $staff->hasRoleId($operationRoleId);
        $isFinance = $staff->hasRoleId($financeRoleId) || $staff->hasRoleId($operationRoleId);
        $isHr = $staff->hasRoleId($hrRoleId) || $staff->hasRoleId($operationRoleId);
        $isStudentViewer = $isEnrolment || $isAdministrator; // enrolment | administrator | operation
    @endphp

    {{-- ===== Row 1: Core Stats ===== --}}
    <div class="row">

        {{-- Students: enrolment | administrator | operation --}}
        @if($isStudentViewer)
            <div class="col-xl-3 col-md-6">
                <a href="{{ route('staff.students.index') }}" class="text-decoration-none">
                    <div class="card card-h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <span class="text-muted d-block">Students</span>
                                    <h4 class="mb-0">{{ $stats['students'] }}</h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="fas fa-user-graduate fa-2x text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endif

        {{-- Teachers: administrator | operation --}}
        @if($isAdministrator)
            <div class="col-xl-3 col-md-6">
                <a href="{{ route('staff.teachers.index') }}" class="text-decoration-none">
                    <div class="card card-h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <span class="text-muted d-block">Teachers</span>
                                    <h4 class="mb-0">{{ $stats['teachers'] }}</h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="fas fa-chalkboard-teacher fa-2x text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endif

        {{-- Fee Collected this month: finance | operation --}}
        @if($isFinance)
            <div class="col-xl-3 col-md-6">
                <a href="{{ route('staff.fees.index') }}" class="text-decoration-none">
                    <div class="card card-h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <span class="text-muted d-block">Fee Collected <small class="text-muted">(this
                                            month)</small></span>
                                    <h4 class="mb-0">₹{{ number_format($stats['fee'], 2) }}</h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="fas fa-rupee-sign fa-2x text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endif

        {{-- Pending Student Leads: enrolment | operation --}}
        @if($isEnrolment)
            <div class="col-xl-3 col-md-6">
                <a href="{{ route('staff.student-leads.index') }}" class="text-decoration-none">
                    <div class="card card-h-100 border-warning">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <span class="text-muted d-block">Pending Student Leads</span>
                                    <h4 class="mb-0">{{ $pendingStudentLeads }}</h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="fas fa-user-clock fa-2x text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endif

        {{-- Pending Teacher Leads: administrator | operation --}}
        @if($isAdministrator)
            <div class="col-xl-3 col-md-6">
                <a href="{{ route('staff.teacher-leads.index') }}" class="text-decoration-none">
                    <div class="card card-h-100 border-warning">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <span class="text-muted d-block">Pending Teacher Leads</span>
                                    <h4 class="mb-0">{{ $pendingTeacherLeads }}</h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="mdi mdi-account-clock fa-2x text-warning" style="font-size:2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endif

        {{-- Unpaid / Partial Fees: finance | operation --}}
        @if($isFinance)
            <div class="col-xl-3 col-md-6">
                <a href="{{ route('staff.fees.index') }}" class="text-decoration-none">
                    <div class="card card-h-100 border-danger">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <span class="text-muted d-block">Unpaid Fees</span>
                                    <h4 class="mb-0">{{ $unpaidFeesCount }} <small
                                            class="fs-6 text-danger">₹{{ number_format($unpaidFeesAmount, 2) }}</small></h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-circle fa-2x text-danger"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-3 col-md-6">
                <a href="{{ route('staff.fees.index', ['tab' => 'overdue']) }}" class="text-decoration-none">
                    <div class="card card-h-100 border-danger bg-soft-danger">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <span class="text-danger fw-bold d-block">Overdue Fees</span>
                                    <h4 class="mb-0">{{ $overdueFeesCount }} <small
                                            class="fs-6 text-danger">₹{{ number_format($overdueFeesAmount, 2) }}</small></h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="fas fa-clock fa-2x text-danger"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endif

        {{-- Unpaid Teacher Salaries: hr | operation --}}
        @if($isHr)
            <div class="col-xl-3 col-md-6">
                <a href="{{ route('staff.salaries.index') }}" class="text-decoration-none">
                    <div class="card card-h-100 border-danger">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <span class="text-muted d-block">Unpaid Teacher Salaries</span>
                                    <h4 class="mb-0">{{ $unpaidTeacherSalariesCount }} <small
                                            class="fs-6 text-danger">₹{{ number_format($unpaidTeacherSalariesAmount, 2) }}</small>
                                    </h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="fas fa-money-bill-wave fa-2x text-danger"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endif

        {{-- Expenses this month: hr | operation --}}
        @if($isHr)
            <!-- <div class="col-xl-3 col-md-6">
                        <a href="{{ route('staff.expenses.index') }}" class="text-decoration-none">
                            <div class="card card-h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <span class="text-muted d-block">Total Expenses <small class="text-muted">(this
                                                    month)</small></span>
                                            <h4 class="mb-0">₹{{ number_format($stats['expense'], 2) }}</h4>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-receipt fa-2x text-danger"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div> -->
        @endif

    </div>


    {{-- ===== Top Performing Teachers: administrator | hr | operation ===== --}}
    @if($isAdministrator || $isHr)
        @include('components.dashboard.top-teachers', ['topTeachers' => $topTeachers])
    @endif

@endsection
@section('script')
    <!-- apexcharts -->
    <script src="{{ URL::asset('/assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ URL::asset('/assets/libs/admin-resources/admin-resources.min.js') }}"></script>

    <!-- dashboard init -->
    <script src="{{ URL::asset('/assets/js/pages/dashboard.init.js') }}"></script>

@endsection

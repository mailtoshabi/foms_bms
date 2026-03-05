@extends('staff.layouts.master')
@section('title') Dashboard @endsection
@section('css')

<link href="{{ URL::asset('/assets/libs/admin-resources/admin-resources.min.css') }}" rel="stylesheet">

@endsection
@section('content')



<div class="row">

    <!-- Total Students -->
    <div class="col-xl-3 col-md-6">
        <div class="card card-h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted d-block">Total Students</span>
                        <h4 class="mb-0">{{ $stats['students'] }}</h4>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="fas fa-user-graduate fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Staff -->
    <div class="col-xl-3 col-md-6">
        <div class="card card-h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted d-block">Total Staff</span>
                        <h4 class="mb-0">{{ $stats['staffs'] }}</h4>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="fas fa-users fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Teachers -->
    <div class="col-xl-3 col-md-6">
        <div class="card card-h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted d-block">Total Teachers</span>
                        <h4 class="mb-0">{{ $stats['teachers'] }}</h4>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="fas fa-chalkboard-teacher fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Fee -->
    <div class="col-xl-3 col-md-6">
        <div class="card card-h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted d-block">Total Fee</span>
                        <h4 class="mb-0">₹ {{ number_format($stats['fee'],2) }}</h4>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="fas fa-rupee-sign fa-2x text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Expense -->
    <div class="col-xl-3 col-md-6">
        <div class="card card-h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted d-block">Total Expense</span>
                        <h4 class="mb-0">₹ {{ number_format($stats['expense'],2) }}</h4>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="fas fa-file-invoice-dollar fa-2x text-danger"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Balance Sheet -->
    <div class="col-xl-3 col-md-6">
        <div class="card card-h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted d-block">Balance Sheet</span>
                        <h4 class="mb-0">₹ {{ number_format($stats['balanceSheet'],2) }}</h4>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="fas fa-chart-line fa-2x text-dark"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>


@endsection
@section('script')
<!-- apexcharts -->
<script src="{{ URL::asset('/assets/libs/apexcharts/apexcharts.min.js') }}"></script>
<script src="{{ URL::asset('/assets/libs/admin-resources/admin-resources.min.js') }}"></script>

<!-- dashboard init -->
<script src="{{ URL::asset('/assets/js/pages/dashboard.init.js') }}"></script>

@endsection

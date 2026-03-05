@extends('admin.layouts.master')
@section('title') Dashboard @endsection
@section('css')

<link href="{{ URL::asset('/assets/libs/admin-resources/admin-resources.min.css') }}" rel="stylesheet">

@endsection
@section('content')



<div class="row">

    {{-- Total Branches --}}
    <div class="col-xl-3 col-md-6">
        <div class="card card-h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted d-block">Total Branches</span>
                        <h4 class="mb-0">{{ $totalBranches }}</h4>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="fas fa-code-branch fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Total Employees --}}
    <div class="col-xl-3 col-md-6">
        <div class="card card-h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted d-block">Total Employees</span>
                        <h4 class="mb-0">{{ $totalEmployees }}</h4>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="fas fa-users fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Total Reports --}}
    <div class="col-xl-3 col-md-6">
        <div class="card card-h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted d-block">Total Reports</span>
                        <h4 class="mb-0">{{ $totalReports }}</h4>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="fas fa-file-alt fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Activity Logs --}}
    <div class="col-xl-3 col-md-6">
        <div class="card card-h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted d-block">Activity Logs</span>
                        <h4 class="mb-0">{{ $totalActivities }}</h4>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="fas fa-history fa-2x text-danger"></i>
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


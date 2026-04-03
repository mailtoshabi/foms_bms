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
                        <span class="text-muted d-block">Students</span>
                        <h4 class="mb-0">{{ $stats['students'] }}</h4>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="fas fa-user-graduate fa-2x text-primary"></i>
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
                        <span class="text-muted d-block">Teachers</span>
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
                        <span class="text-muted d-block">Fee Collected</span>
                        <h4 class="mb-0">₹ {{ number_format($stats['fee'],2) }}</h4>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="fas fa-rupee-sign fa-2x text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
        <h5>Top Performing Teachers</h5>
        </div>

        <div class="card-body">

        <table class="table table-bordered">

        <thead>
        <tr>
        <th>#</th>
        <th>Name</th>
        <th>Classes</th>
        <th>Hours</th>
        <th>Attendance</th>
        <th>Score</th>
        </tr>
        </thead>

        <tbody>

        @foreach($topTeachers as $index => $t)

        <tr>

        <td>
        @if($index == 0) 🥇
        @elseif($index == 1) 🥈
        @elseif($index == 2) 🥉
        @else {{ $index+1 }}
        @endif
        </td>

        <td>{{ $t['teacher']->name }}</td>

        <td>{{ $t['classes'] }}</td>

        <td>{{ $t['hours'] }}</td>

        <td>{{ $t['attendance'] }}%</td>

        <td>
        <span class="badge bg-success">
        {{ $t['score'] }}
        </span>
        </td>

        </tr>

        @endforeach

        </tbody>

        </table>

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

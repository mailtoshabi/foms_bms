@extends('admin.layouts.master')
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

    <!-- Total Staff -->
    <div class="col-xl-3 col-md-6">
        <div class="card card-h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted d-block">Staff</span>
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

    <!-- Total Expense -->
    <div class="col-xl-3 col-md-6">
        <div class="card card-h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted d-block">Expense</span>
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

<div class="row mt-4">

<div class="col-md-6">
    <div class="card">

    <div class="card-header">
    <h4>Fee Status (This Month)</h4>
    </div>

    <div class="card-body">

    <canvas id="feeChart"></canvas>

    </div>

    </div>
</div>

<div class="col-md-6">

    <div class="card">

    <div class="card-header">
    <h4>Monthly Earnings vs Expenses</h4>
    </div>

    <div class="card-body">

    <canvas id="financeChart" height="200"></canvas>

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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const ctx = document.getElementById('financeChart');

new Chart(ctx, {
    type: 'line',

    data: {
        labels: @json($months),

        datasets: [

            {
                label: 'Fee Collection',
                data: @json($fees),
                borderWidth: 2,
                fill: false
            },

            {
                label: 'Expenses',
                data: @json($expenses),
                borderWidth: 2,
                fill: false
            }

        ]
    },

    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top'
            }
        }
    }

});

</script>

<script>

    const ctxFee = document.getElementById('feeChart');

    new Chart(ctxFee, {
        type: 'doughnut',

        data: {
            labels: ['Paid', 'Pending'],

            datasets: [{
                data: [
                    {{ $paidAmount }},
                    {{ $pendingAmount }}
                ],
                borderWidth: 1
            }]
        },

        options: {
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }

    });

</script>

@endsection

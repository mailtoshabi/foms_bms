@extends('admin.layouts.master')
@section('title') Dashboard @endsection
@section('css')

    <link href="{{ URL::asset('/assets/libs/admin-resources/admin-resources.min.css') }}" rel="stylesheet">

@endsection
@section('content')


    {{-- ===== Row 1: People ===== --}}
    <div class="row">

        <div class="col-xl-4 col-md-6">
            <a href="{{ route('admin.reports.students') }}" class="text-decoration-none">
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

        <div class="col-xl-4 col-md-6">
            <a href="{{ route('admin.reports.teachers') }}" class="text-decoration-none">
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

        <div class="col-xl-4 col-md-6">
            <a href="{{ route('admin.staffs.index') }}" class="text-decoration-none">
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
            </a>
        </div>

    </div>

    {{-- ===== Row 2: Finance ===== --}}
    <div class="row">

        <div class="col-xl-4 col-md-6">
            <a href="{{ route('admin.reports.fee.collection') }}" class="text-decoration-none">
                <div class="card card-h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <span class="text-muted d-block">Fee Collected <small>(this month)</small></span>
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

        <div class="col-xl-4 col-md-6">
            <a href="{{ route('admin.reports.finance.expense') }}" class="text-decoration-none">
                <div class="card card-h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <span class="text-muted d-block">Expenses <small>(this month)</small></span>
                                <h4 class="mb-0">₹{{ number_format($stats['expense'], 2) }}</h4>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="fas fa-file-invoice-dollar fa-2x text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl-4 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <span class="text-muted d-block">Balance Sheet <small>(this month)</small></span>
                            <h4 class="mb-0 {{ $stats['balanceSheet'] >= 0 ? 'text-success' : 'text-danger' }}">
                                ₹{{ number_format($stats['balanceSheet'], 2) }}
                            </h4>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-chart-line fa-2x text-dark"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ===== Row 3: Action Alerts ===== --}}
    <div class="row">

        <div class="col-xl-3 col-md-6">
            <a href="{{ route('admin.reports.student-leads') }}" class="text-decoration-none">
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

        <div class="col-xl-3 col-md-6">
            <a href="{{ route('admin.reports.teacher-leads') }}" class="text-decoration-none">
                <div class="card card-h-100 border-warning">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <span class="text-muted d-block">Pending Teacher Leads</span>
                                <h4 class="mb-0">{{ $pendingTeacherLeads }}</h4>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="mdi mdi-account-clock text-warning" style="font-size:2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6">
            <a href="{{ route('admin.reports.fee') }}" class="text-decoration-none">
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
            <a href="{{ route('admin.reports.teacher.salary') }}" class="text-decoration-none">
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

    </div>

    <div class="row">

        <div class="card">
            <div class="card-header">
                <h5>Top Performing Teachers</h5>
            </div>

            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">

                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Rank</th>
                                <th>Name</th>
                                <th>Classes</th>
                                <th>Hours</th>
                                <th>Attendance</th>
                                <th>Score</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach($topTeachers as $index => $t)

                                @php
                                    $score = $t['score'];
                                    if ($score >= 70) {
                                        $stars = 5;
                                        $rankLabel = 'Elite';
                                        $rankColor = 'warning';
                                    } elseif ($score >= 50) {
                                        $stars = 4;
                                        $rankLabel = 'Expert';
                                        $rankColor = 'primary';
                                    } elseif ($score >= 30) {
                                        $stars = 3;
                                        $rankLabel = 'Advanced';
                                        $rankColor = 'info';
                                    } elseif ($score >= 15) {
                                        $stars = 2;
                                        $rankLabel = 'Intermediate';
                                        $rankColor = 'secondary';
                                    } else {
                                        $stars = 1;
                                        $rankLabel = 'Beginner';
                                        $rankColor = 'light';
                                    }
                                @endphp

                                <tr>

                                    <td>
                                        @if($index == 0) &#x1F947;
                                        @elseif($index == 1) &#x1F948;
                                        @elseif($index == 2) &#x1F949;
                                        @else {{ $index + 1 }}
                                        @endif
                                    </td>

                                    <td>
                                        <span class="badge bg-{{ $rankColor }} me-1">{{ $rankLabel }}</span><br>
                                        <small>
                                            @for($s = 1; $s <= 5; $s++)
                                                @if($s <= $stars)
                                                    <span class="text-warning">&#9733;</span>
                                                @else
                                                    <span class="text-muted">&#9733;</span>
                                                @endif
                                            @endfor
                                        </small>
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
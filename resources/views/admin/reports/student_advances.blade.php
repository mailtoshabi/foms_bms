@extends('admin.layouts.master')
@section('title', 'Student Advances Report')

@section('content')

    <div class="card mb-3">
        <div class="card-body p-2">
            <ul class="nav nav-pills">

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.reports.student-leads') ? 'active' : '' }}"
                        href="{{ route('admin.reports.student-leads') }}">
                        Student Leads
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.reports.students') ? 'active' : '' }}"
                        href="{{ route('admin.reports.students') }}">
                        Students
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.reports.student-advances') ? 'active' : '' }}"
                        href="{{ route('admin.reports.student-advances') }}">
                        Student Advances
                    </a>
                </li>

            </ul>
        </div>
    </div>

    <div class="card">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <a href="javascript:window.history.back();"
                    class="btn btn-sm btn-light border-0 shadow-sm me-2 rounded-circle" title="Go Back">
                    <i class="fas fa-chevron-left"></i>
                </a>
                Student Advances Report
            </h4>
        </div>

        <div class="card-body table-responsive">

            {{-- Stat Cards --}}
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="card p-3 shadow-sm border border-success">
                        <span class="text-muted small d-block">Overall Advance Amount</span>
                        <h4 class="text-success mb-0 fw-bold">₹{{ number_format($totalSystemAdvance, 2) }}</h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3 shadow-sm border border-success">
                        <span class="text-muted small d-block">Overall Students with Advance</span>
                        <h4 class="text-success mb-0 fw-bold">{{ $studentsWithAdvanceCount }}</h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3 shadow-sm border border-info">
                        <span class="text-muted small d-block">Filtered Advance Amount</span>
                        <h4 class="text-info mb-0 fw-bold">₹{{ number_format($filteredAdvanceAmount, 2) }}</h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3 shadow-sm border border-info">
                        <span class="text-muted small d-block">Filtered Students Count</span>
                        <h4 class="text-info mb-0 fw-bold">{{ $filteredStudentsCount }}</h4>
                    </div>
                </div>
            </div>

            {{-- Filter Form --}}
            <form method="GET" id="filter-form" class="row mb-3 align-items-end">

                <div class="col-md-2 mb-2">
                    <label class="form-label fw-bold">Search</label>
                    <input type="text" name="name" class="form-control" value="{{ request('name') }}"
                        placeholder="Search Name/Phone/Adm No">
                </div>

                <div class="col-md-2 mb-2">
                    <label class="form-label fw-bold">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>

                <div class="col-md-2 mb-2">
                    <label class="form-label fw-bold">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>

                <div class="col-md-2 mb-2">
                    <label class="form-label fw-bold">Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="passout" {{ request('status') == 'passout' ? 'selected' : '' }}>Passout</option>
                        <option value="dropout" {{ request('status') == 'dropout' ? 'selected' : '' }}>Dropout</option>
                    </select>
                </div>

                <div class="col-md-2 mb-2">
                    <label class="form-label fw-bold">Wallet Option</label>
                    <select name="balance_option" class="form-control">
                        <option value="has_balance" {{ $balanceOption == 'has_balance' ? 'selected' : '' }}>With Balance Only</option>
                        <option value="no_balance" {{ $balanceOption == 'no_balance' ? 'selected' : '' }}>No Balance Only</option>
                        <option value="all" {{ $balanceOption == 'all' ? 'selected' : '' }}>All Students</option>
                    </select>
                </div>

                <div class="col-md-2 mb-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                    <a href="{{ route('admin.reports.student-advances') }}" class="btn btn-secondary w-100">Reset</a>
                </div>

            </form>

            {{-- Table --}}
            <table class="table table-bordered align-middle table-nowrap mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Admission No</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Date Joined</th>
                        <th class="text-end">Wallet Balance</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        <tr>
                            <td>
                                <a href="{{ route('admin.reports.students.show', encrypt($student->id)) }}">
                                    {{ $student->admission_no }}
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('admin.reports.students.show', encrypt($student->id)) }}">
                                    {{ $student->name }}
                                </a>
                            </td>
                            <td>{{ $student->formatted_contact_number }}
                                @if($student->is_whatsapp_different)
                                    <br><small class="text-success"><i class="mdi mdi-whatsapp"></i>
                                        {{ $student->formatted_whatsapp_number }}</small>
                                @endif
                            </td>
                            <td>
                                <span
                                    class="badge bg-{{ $student->status == 'active' ? 'success' : ($student->status == 'passout' ? 'info' : 'danger') }}">
                                    {{ ucfirst($student->status) }}
                                </span>
                                @if($student->is_blocked)
                                    <span class="badge bg-danger">Blocked</span>
                                @endif
                            </td>
                            <td>{{ $student->created_at->format('d M Y') }}</td>
                            <td class="text-end fw-bold">
                                @if($student->wallet_balance > 0)
                                    <span class="text-success">₹{{ number_format($student->wallet_balance, 2) }}</span>
                                @else
                                    <span class="text-muted">₹{{ number_format($student->wallet_balance, 2) }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.reports.students.show', encrypt($student->id)) }}"
                                    class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> View Profile
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No students found with selected filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                {{ $students->links() }}
            </div>

        </div>
    </div>

@endsection

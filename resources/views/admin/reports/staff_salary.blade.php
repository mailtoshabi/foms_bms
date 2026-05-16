@extends('admin.layouts.master')
@section('title', 'Staff Salary Report')

@section('content')

    <div class="card mb-3">
        <div class="card-body p-2">
            <ul class="nav nav-pills">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.reports.staffs') ? 'active' : '' }}"
                        href="{{ route('admin.reports.staffs') }}">
                        Staff List
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.reports.staff.salary') ? 'active' : '' }}"
                        href="{{ route('admin.reports.staff.salary') }}">
                        Salary
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
                Staff Salary Report
            </h4>
        </div>

        <div class="card-body table-responsive">

            <form method="GET" action="{{ route('admin.reports.staff.salary') }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Staff Name/Phone</label>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                            placeholder="Search staff...">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-bold">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                            <option value="not_paid" {{ request('status') == 'not_paid' ? 'selected' : '' }}>Not Paid</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-bold">From Month</label>
                        <input type="month" name="from_month" value="{{ request('from_month') }}" class="form-control">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-bold">To Month</label>
                        <input type="month" name="to_month" value="{{ request('to_month') }}" class="form-control">
                    </div>

                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary px-3">
                            <i class="mdi mdi-filter"></i> Filter
                        </button>

                        <a href="{{ route('admin.reports.staff.salary') }}" class="btn btn-light px-3">
                            <i class="mdi mdi-refresh"></i> Reset
                        </a>

                        <a href="{{ route('admin.reports.staff.salary.export', request()->query()) }}"
                            class="btn btn-success px-3">
                            <i class="mdi mdi-file-excel"></i> Export
                        </a>
                    </div>
                </div>
            </form>

            <hr class="my-4">

            @if (isset($isFiltered) && $isFiltered)
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card bg-soft-info border-info">
                            <div class="card-body d-flex justify-content-between align-items-center p-3">
                                <div>
                                    <h5 class="text-info mb-1"><i class="mdi mdi-information-outline me-1"></i>
                                        Filtering Summary</h5>
                                    <p class="text-muted mb-0 small">Showing total results based on
                                        your selected criteria.</p>
                                </div>
                                <div class="text-end">
                                    <p class="text-muted mb-1 small uppercase fw-bold">Total
                                        Salary Amount</p>
                                    <h3 class="text-primary mb-0 fw-bold">₹
                                        {{ number_format($totalAmount ?? 0, 2) }}
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Staff</th>
                        <th>Phone</th>
                        <th>Month</th>
                        <th>Salary</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Paid Date</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($data as $row)
                        <tr>
                            <td><a href="{{ route('admin.staffs.show', encrypt($row->staff_id)) }}">{{ $row->name }}</a></td>
                            <td>{{ $row->phone ?? 'N/A' }}</td>
                            <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $row->salary_month)->format('M Y') }}</td>
                            <td>₹ {{ number_format($row->salary_amount, 2) }}</td>
                            <td>₹ {{ number_format($row->paid_amount, 2) }}</td>
                            <td>₹ {{ number_format($row->balance_due, 2) }}</td>
                            <td>
                                <span
                                    class="badge {{ $row->status == 'paid' ? 'bg-success' : ($row->status == 'partial' ? 'bg-warning text-dark' : 'bg-danger') }}">
                                    {{ ucfirst(str_replace('_', ' ', $row->status)) }}
                                </span>
                            </td>
                            <td>{{ $row->paid_date ? \Carbon\Carbon::parse($row->paid_date)->format('d M Y') : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">No Records Found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $data->links() }}

        </div>
    </div>

@endsection
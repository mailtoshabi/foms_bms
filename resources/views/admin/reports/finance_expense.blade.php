@extends('admin.layouts.master')
@section('title', 'Expense Report')

@section('content')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <a href="javascript:window.history.back();" class="btn btn-sm btn-light border-0 shadow-sm me-2 rounded-circle" title="Go Back">
                    <i class="fas fa-chevron-left"></i>
                </a>
                Expense Report
            </h4>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                <i class="fas fa-plus"></i> Add Direct Expense
            </button>
        </div>

        <div class="card-body table-responsive">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="card p-3">Total Expense: ₹ {{ number_format($totalAmount, 2) }}</div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3">Staff Salary: ₹
                        {{ number_format((float) ($sourceTotals['staff_salary'] ?? 0), 2) }}</div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3">Teacher Salary: ₹
                        {{ number_format((float) ($sourceTotals['teacher_salary'] ?? 0), 2) }}</div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3">Other Expense: ₹ {{ number_format((float) ($sourceTotals['expense'] ?? 0), 2) }}</div>
                </div>
            </div>

            <form method="GET" class="row mb-3 align-items-end">

                <div class="col-md-3">
                    <label class="form-label fw-bold">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                        placeholder="Search name/particular/remarks">
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-bold">Type</label>
                    <select name="type" class="form-control">
                        <option value="">All Types</option>
                        <option value="staff_salary" {{ request('type') == 'staff_salary' ? 'selected' : '' }}>Staff Salary
                        </option>
                        <option value="teacher_salary" {{ request('type') == 'teacher_salary' ? 'selected' : '' }}>Teacher
                            Salary</option>
                        <option value="expense" {{ request('type') == 'expense' ? 'selected' : '' }}>Expense</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-bold">From Date</label>
                    <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control">
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-bold">To Date</label>
                    <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control">
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-primary">Filter</button>

                    <a href="{{ route('admin.reports.finance.expense') }}" class="btn btn-light">Reset</a>
                </div>

            </form>

            <table class="table table-bordered align-middle">

                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Name</th>
                        <th>Particular</th>
                        <th>Method</th>
                        <th>Amount</th>
                        <th>Remarks</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($data as $row)

                        <tr>
                            <td>{{ \Carbon\Carbon::parse($row->transaction_date)->format('d M Y') }}</td>
                            <td>
                                <span
                                    class="badge {{ $row->source == 'staff_salary' ? 'bg-info' : ($row->source == 'teacher_salary' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                    {{ ucfirst(str_replace('_', ' ', $row->source)) }}
                                </span>
                            </td>
                            <td>{{ $row->person_name ?: '-' }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $row->particular)) }}</td>
                            <td>{{ $row->payment_method ? ucfirst(str_replace('_', ' ', $row->payment_method)) : '-' }}</td>
                            <td>₹ {{ number_format((float) $row->amount, 2) }}</td>
                            <td>{{ $row->remarks ?: '-' }}</td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No Records Found</td>
                        </tr>
                    @endforelse

                </tbody>

            </table>

            {{ $data->links() }}

        </div>
    </div>

    {{-- Add Expense Modal --}}
    <div class="modal fade" id="addExpenseModal" tabindex="-1" aria-labelledby="addExpenseModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.reports.finance.expense.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addExpenseModalLabel">Add New Expense</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                            <select name="category_id" id="category_id" class="form-control" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $id => $name)
                                    <option value="{{ $id }}">{{ ucfirst($name) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                            <input type="number" name="amount" id="amount" class="form-control" step="0.01" min="0.01"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="expense_date" class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" name="expense_date" id="expense_date" class="form-control"
                                value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea name="remarks" id="remarks" class="form-control" rows="3"
                                placeholder="Enter expense details..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Expense</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
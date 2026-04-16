@extends('staff.layouts.master')

@section('title', 'Expenses')

@section('content')

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <div class="col-12">
            {{-- Header with Create Button --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Expense Management</h4>
                <a href="{{ route('staff.expenses.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Expense
                </a>
            </div>

            {{-- Summary Card --}}
            <div class="card mb-3">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Total Expenses</h6>
                            <h3 class="text-primary">₹ {{ number_format($totalExpense, 2) }}</h3>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Total Records</h6>
                            <h3 class="text-info">{{ $expenses->total() }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filters --}}
            <form method="GET" action="{{ route('staff.expenses.index') }}" class="mb-3">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row">
                            {{-- Category Filter --}}
                            <div class="col-md-3 mb-2">
                                <select name="category_id" class="form-control select2">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $id => $name)
                                        <option value="{{ $id }}" {{ request('category_id') == $id ? 'selected' : '' }}>
                                            {{ ucfirst($name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- From Date --}}
                            <div class="col-md-2 mb-2">
                                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}"
                                    placeholder="From Date">
                            </div>

                            {{-- To Date --}}
                            <div class="col-md-2 mb-2">
                                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}"
                                    placeholder="To Date">
                            </div>

                            {{-- Search Remarks --}}
                            <div class="col-md-3 mb-2">
                                <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                                    placeholder="Search remarks...">
                            </div>

                            {{-- Buttons --}}
                            <div class="col-md-2 mb-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-12">
                                <a href="{{ route('staff.expenses.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            {{-- Expenses Table --}}
            <div class="card">
                <div class="card-header">
                    <h5>Expense List</h5>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 5%">#</th>
                                    <th>Category</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Remarks</th>
                                    <th style="width: 10%">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($expenses as $index => $expense)
                                    <tr>
                                        <td>{{ $expenses->firstItem() + $index }}</td>

                                        <td>
                                            <span class="badge bg-info">
                                                {{ ucfirst($expense->category->name ?? '-') }}
                                            </span>
                                        </td>

                                        <td>
                                            <strong class="text-danger">
                                                ₹ {{ number_format($expense->amount, 2) }}
                                            </strong>
                                        </td>

                                        <td>
                                            {{ $expense->expense_date->format('d M Y') }}
                                        </td>

                                        <td>
                                            <small>{{ Str::limit($expense->remarks, 50) }}</small>
                                        </td>

                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('staff.expenses.edit', $expense->id) }}"
                                                    class="btn btn-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                <form action="{{ route('staff.expenses.destroy', $expense->id) }}" method="POST"
                                                    style="display: inline;"
                                                    onsubmit="return confirm('Are you sure you want to delete this expense?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox"></i> No expenses found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-3">
                        {{ $expenses->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@extends('staff.layouts.master')

@section('title', 'Teacher Deposits')

@section('content')

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <div class="col-12">
            {{-- Filter --}}
            <form method="GET" action="{{ route('staff.deposits.index') }}">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row align-items-end">
                            <div class="col-md-4 mb-2">
                                <label class="form-label fw-bold">Teacher</label>
                                <select name="teacher_id" class="form-control select2">
                                    <option value="">All Teachers</option>
                                    @foreach($teachers as $id => $name)
                                        <option value="{{ $id }}" {{ request('teacher_id') == $id ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label fw-bold">Status</label>
                                <select name="status" class="form-control">
                                    <option value="">All Statuses</option>
                                    <option value="not paid" {{ request('status') == 'not paid' ? 'selected' : '' }}>Not Paid</option>
                                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-2">
                                <button class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                                <a href="{{ route('staff.deposits.index') }}" class="btn btn-secondary">Reset</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            {{-- Table --}}
            <div class="card">
                <div class="card-header">
                    <h5>Teacher Deposits List</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle table-nowrap mb-0">
                            <thead>
                                <tr>
                                    <th>Teacher</th>
                                    <th>Deposit Amount</th>
                                    <th>Deposited Date</th>
                                    <th>Due Date (plus 6 months)</th>
                                    <th>Paid Amount</th>
                                    <th>Remaining Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($deposits as $deposit)
                                    <tr>
                                        <td><a href="{{ route('staff.teachers.show', encrypt($deposit->teacher->id)) }}">{{ $deposit->teacher->name ?? '-' }}</a></td>
                                        <td><strong>₹{{ number_format($deposit->amount, 2) }}</strong></td>
                                        <td>{{ $deposit->deposited_date ? $deposit->deposited_date->format('d M Y') : '-' }}</td>
                                        <td>{{ $deposit->due_date ? $deposit->due_date->format('d M Y') : '-' }}</td>
                                        <td>₹{{ number_format($deposit->paid_amount, 2) }}</td>
                                        <td>₹{{ number_format($deposit->amount - $deposit->paid_amount, 2) }}</td>
                                        <td>
                                            @if($deposit->status == 'paid')
                                                <span class="badge bg-success">Paid</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Not Paid</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No deposits found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $deposits->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

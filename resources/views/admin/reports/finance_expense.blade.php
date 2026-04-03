@extends('admin.layouts.master')
@section('title','Expense Report')

@section('content')

<div class="card">

<div class="card-header d-flex justify-content-between">
<h4>Expense Report</h4>
</div>

<div class="card-body table-responsive">

<div class="row mb-3">
    <div class="col-md-3">
        <div class="card p-3">Total Expense: ₹ {{ number_format($totalAmount, 2) }}</div>
    </div>
    <div class="col-md-3">
        <div class="card p-3">Staff Salary: ₹ {{ number_format((float) ($sourceTotals['staff_salary'] ?? 0), 2) }}</div>
    </div>
    <div class="col-md-3">
        <div class="card p-3">Teacher Salary: ₹ {{ number_format((float) ($sourceTotals['teacher_salary'] ?? 0), 2) }}</div>
    </div>
    <div class="col-md-3">
        <div class="card p-3">Other Expense: ₹ {{ number_format((float) ($sourceTotals['expense'] ?? 0), 2) }}</div>
    </div>
</div>

<form method="GET" class="row mb-3">

<div class="col-md-3">
<input type="text"
name="search"
value="{{ request('search') }}"
class="form-control"
placeholder="Search name/particular/remarks">
</div>

<div class="col-md-2">
<select name="type" class="form-control">
<option value="">All Types</option>
<option value="staff_salary" {{ request('type') == 'staff_salary' ? 'selected' : '' }}>Staff Salary</option>
<option value="teacher_salary" {{ request('type') == 'teacher_salary' ? 'selected' : '' }}>Teacher Salary</option>
<option value="expense" {{ request('type') == 'expense' ? 'selected' : '' }}>Expense</option>
</select>
</div>

<div class="col-md-2">
<input type="date"
name="from_date"
value="{{ request('from_date') }}"
class="form-control">
</div>

<div class="col-md-2">
<input type="date"
name="to_date"
value="{{ request('to_date') }}"
class="form-control">
</div>

<div class="col-md-3 d-flex gap-2">
<button class="btn btn-primary">Filter</button>

<a href="{{ route('admin.reports.finance.expense') }}"
class="btn btn-light">Reset</a>
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
<span class="badge {{ $row->source == 'staff_salary' ? 'bg-info' : ($row->source == 'teacher_salary' ? 'bg-warning text-dark' : 'bg-secondary') }}">
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

@endsection

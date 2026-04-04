@extends('admin.layouts.master')

@section('title','Teacher Salaries')

@section('content')

<div class="row">

<div class="col-12">

<div class="card mb-3">
<div class="card-body p-2">

<ul class="nav nav-pills">

    <li class="nav-item">
    <a class="nav-link {{ $tab == 'unpaid' ? 'active' : '' }}"
    href="{{ route('admin.salaries.index', array_merge(request()->except('page'), ['tab'=>'unpaid'])) }}">

    Unpaid Salaries
    <span class="badge {{ $tab == 'unpaid' ? 'bg-white text-primary' : 'bg-light text-dark' }}">
    {{ $unpaidCount }}
    </span>

    </a>
    </li>

    <li class="nav-item">
    <a class="nav-link {{ $tab == 'paid' ? 'active' : '' }}"
    href="{{ route('admin.salaries.index', array_merge(request()->except('page'), ['tab'=>'paid'])) }}">

    Paid Salaries
    <span class="badge {{ $tab == 'paid' ? 'bg-white text-primary' : 'bg-light text-dark' }}">
    {{ $paidCount }}
    </span>

    </a>
    </li>

</ul>

</div>
</div>

{{-- ================= FILTER ================= --}}
<form method="GET" action="{{ route('admin.salaries.index') }}">
    <input type="hidden" name="tab" value="{{ $tab }}">

<div class="card mb-3">
<div class="card-body">

<div class="row">

<div class="col-md-3 mb-2">
<select name="teacher_id" class="form-control select2">
<option value="">All Teachers</option>

@foreach($teachers as $id => $name)
<option value="{{ $id }}"
{{ request('teacher_id') == $id ? 'selected' : '' }}>
{{ $name }}
</option>
@endforeach

</select>
</div>

<div class="col-md-3 mb-2">
<input type="date"
name="from_date"
class="form-control"
value="{{ request('from_date') }}">
</div>

<div class="col-md-3 mb-2">
<input type="date"
name="to_date"
class="form-control"
value="{{ request('to_date') }}">
</div>

<div class="col-md-3 mb-2">

<button class="btn btn-primary">
<i class="fas fa-search"></i> Filter
</button>

<a href="{{ route('admin.salaries.index') }}"
class="btn btn-secondary">
Reset
</a>

</div>

</div>

</div>
</div>

</form>

{{-- ================= TABLE ================= --}}
<div class="card">

<div class="card-header">
<h5>Teacher Salary List</h5>
</div>

<div class="card-body">

<div class="table-responsive">
<table class="table table-bordered">

<thead>
<tr>
<th>Teacher</th>
<th>Cycle</th>
<th>Total Hours</th>
<th>Total Amount</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>

<tbody>

@forelse($salaries as $salary)

<tr>

<td>{{ $salary->teacher->name }}</td>

<td>
{{ \Carbon\Carbon::parse($salary->cycle_start)->format('d M Y') }}
-
{{ \Carbon\Carbon::parse($salary->cycle_end)->format('d M Y') }}
</td>

<td>
<span class="badge bg-info">
{{ number_format($salary->total_hours, 2) }} hrs
</span>
</td>

<td>
<strong class="{{ $salary->status == 'paid' ? 'text-success' : 'text-danger' }}">
â‚¹ {{ number_format($salary->total_amount, 2) }}
</strong>
@if($salary->status == 'paid')
<br><small class="text-muted">
Paid on {{ optional($salary->payment_date)->format('d M Y') }}
</small>
@endif
</td>

<td>
<span class="badge
    {{ $salary->status == 'paid' ? 'bg-success' : 'bg-warning text-dark' }}">
    {{ ucfirst($salary->status) }}
</span>
</td>

<td>

<button class="btn btn-sm btn-primary paySalaryBtn {{ $salary->status == 'paid' ? 'disabled' : '' }}"
data-id="{{ $salary->id }}"
data-amount="{{ $salary->total_amount }}"
data-status="{{ $salary->status }}"
data-date="{{ optional($salary->payment_date)->format('Y-m-d') }}"
data-method="{{ $salary->payment_method }}"
data-ref="{{ $salary->reference_number }}"
data-notes="{{ $salary->notes }}">

<i class="fas fa-money-bill"></i>

</button>

</td>

</tr>

@empty

<tr>
<td colspan="6" class="text-center text-muted">
{{ $tab == 'paid' ? 'No paid salaries found' : 'No unpaid salaries found' }}
</td>
</tr>

@endforelse

</tbody>

</table>
</div>

<div class="mt-3">
{{ $salaries->links() }}
</div>

</div>

</div>

</div>

</div>

{{-- Payment Modal --}}

<div class="modal fade" id="salaryPaymentModal">

<div class="modal-dialog">
<div class="modal-content">

<form method="POST" action="{{ route('admin.salaries.pay') }}">
@csrf

<input type="hidden" name="salary_id" id="salary_id">

<div class="modal-header">
<h5>Salary Payment</h5>
</div>

<div class="modal-body">

<div class="mb-3">
<label>Amount</label>
<input type="text" id="salary_amount" class="form-control" readonly>
</div>

<div class="mb-3">
<label>Payment Date</label>
<input type="date" name="payment_date" id="payment_date" class="form-control">
</div>

<div class="mb-3">
<label>Payment Method</label>
<select name="payment_method" id="payment_method" class="form-control">
<option value="cash">Cash</option>
<option value="upi">UPI</option>
<option value="bank_transfer">Bank Transfer</option>
</select>
</div>

<div class="mb-3">
<label>Reference Number</label>
<input type="text" name="reference_number" id="reference_number" class="form-control">
</div>

<div class="mb-3">
<label>Notes</label>
<textarea name="notes" id="notes" class="form-control"></textarea>
</div>

</div>

<div class="modal-footer">
<button class="btn btn-success">Save Payment</button>
</div>

</form>

</div>
</div>

</div>

{{-- Payment Modal End --}}

@endsection

@section('script')
    <script>

    $('.paySalaryBtn').click(function(){

        let btn = $(this);

        $('#salary_id').val(btn.data('id'));
        $('#salary_amount').val('â‚¹ ' + parseFloat(btn.data('amount')).toFixed(2));

        $('#payment_date').val(btn.data('date') || new Date().toISOString().split('T')[0]);
        $('#payment_method').val(btn.data('method') || 'cash');
        $('#reference_number').val(btn.data('ref') || '');
        $('#notes').val(btn.data('notes') || '');

        $('#salaryPaymentModal').modal('show');

    });

    </script>
@endsection

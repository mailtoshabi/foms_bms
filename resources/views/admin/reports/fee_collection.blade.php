@extends('admin.layouts.master')
@section('title','Fee Collection Report')

@section('content')

<div class="card">

<div class="card-header d-flex justify-content-between">
<h4>Fee Collection Report</h4>
</div>

<div class="card-body table-responsive">

<form method="GET" class="row mb-3">

<div class="col-md-2">
<input type="text"
name="search"
value="{{ request('search') }}"
class="form-control"
placeholder="Search student name">
</div>

<div class="col-md-2">
<select name="category_id" class="form-control">
<option value="">All Categories</option>
@forelse($categories as $id => $name)
<option value="{{ $id }}" {{ request('category_id')==$id?'selected':'' }}>{{ ucfirst($name) }}</option>
@empty
@endforelse
</select>
</div>

<div class="col-md-2">
<select name="class_room_id" class="form-control">
<option value="">All Classes</option>
@foreach($classRooms as $id => $name)
<option value="{{ $id }}" {{ request('class_room_id')==$id?'selected':'' }}>{{ $name }}</option>
@endforeach
</select>
</div>

<div class="col-md-2">
<select name="payment_method" class="form-control">
<option value="">All Methods</option>
<option value="cash" {{ request('payment_method')=='cash'?'selected':'' }}>Cash</option>
<option value="upi" {{ request('payment_method')=='upi'?'selected':'' }}>UPI</option>
<option value="card" {{ request('payment_method')=='card'?'selected':'' }}>Card</option>
<option value="bank_transfer" {{ request('payment_method')=='bank_transfer'?'selected':'' }}>Bank</option>
</select>
</div>

<div class="col-md-2">
<input type="date"
name="date"
value="{{ request('date') }}"
class="form-control">
</div>

<div class="col-md-4 d-flex gap-2">
<button class="btn btn-primary">Filter</button>

<a href="{{ route('admin.reports.fee.collection') }}"
class="btn btn-light">Reset</a>

<a href="{{ route('admin.reports.fee.collection.export',request()->query()) }}"
class="btn btn-success">
<i class="mdi mdi-file-excel"></i> Export
</a>
</div>

</form>

<table class="table table-bordered align-middle">

<thead>
<tr>
<th>Student</th>
<th>Contact</th>
<th>Class</th>
<th>Category</th>
<th>Amount</th>
<th>Method</th>
<th>Date</th>
</tr>
</thead>

<tbody>

@forelse($data as $row)

<tr>
<td>{{ $row->name }}</td>
<td>{{ $row->contact_number }}</td>
<td>{{ $row->class_name }}</td>
<td><span class="badge bg-info">{{ ucfirst($row->category_name) }}</span></td>
<td>₹ {{ number_format($row->paid_amount,2) }}</td>
<td>{{ ucfirst(str_replace('_',' ',$row->payment_method)) }}</td>
<td>{{ \Carbon\Carbon::parse($row->paid_date)->format('d M Y') }}</td>
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

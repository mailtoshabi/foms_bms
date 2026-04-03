@extends('staff.layouts.master')

@section('title','Invoice')

@section('content')

<div class="card">

<div class="card-header">
<h4>Fee Invoice</h4>
</div>

<div class="card-body">

<p><strong>Student:</strong> {{ $fee->student->name }}</p>
<p><strong>Class:</strong> {{ $fee->classRoom->name }}</p>

<hr>

<p><strong>Total Fee:</strong> ₹ {{ number_format($fee->amount,2) }}</p>

<p><strong>Status:</strong> {{ ucfirst($fee->status) }}</p>

<hr>

<h5>Payments</h5>

<table class="table table-bordered">

<thead>
<tr>
<th>Date</th>
<th>Amount</th>
<th>Method</th>
</tr>
</thead>

<tbody>

@foreach($fee->payments as $payment)

<tr>
<td>{{ $payment->paid_date }}</td>
<td>₹ {{ number_format($payment->paid_amount,2) }}</td>
<td>{{ ucfirst($payment->payment_method) }}</td>
</tr>

@endforeach

</tbody>

</table>

</div>

</div>

@endsection

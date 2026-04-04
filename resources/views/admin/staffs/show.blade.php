@extends('admin.layouts.master')

@section('title','Staff Details')

@section('content')

<div class="row">

{{-- =========================
   STAFF PROFILE
========================= --}}
<div class="col-md-4">

<div class="card">

<div class="card-header">
<h5>Staff Profile</h5>
</div>

<div class="card-body text-center">

@if($staff->photo)
<img src="{{ asset('storage/'.$staff->photo) }}"
class="rounded-circle mb-3"
width="120">
@endif

<h5>{{ $staff->name }}</h5>

<p class="text-muted mb-1">
{{ $staff->phone }}
</p>

<p class="text-muted mb-1">
{{ $staff->email ?? '-' }}
</p>

<span class="badge {{ $staff->is_blocked ? 'bg-danger' : 'bg-success' }}">
{{ $staff->is_blocked ? 'Blocked' : 'Active' }}
</span>

<hr>

<p><strong>Department(s):</strong></p>
<div class="text-muted mb-3">
@forelse($staff->roles as $role)
<span class="badge bg-info">{{ $role->name }}</span>
@empty
<p class="text-muted">-</p>
@endforelse
</div>

<p><strong>Phone:</strong></p>
<p class="text-muted">
{{ $staff->phone ?? '-' }}
</p>

<p><strong>Address:</strong></p>
<p class="text-muted">
{{ $staff->address ?? '-' }}
</p>

<p><strong>G-Pay Number:</strong></p>
<p class="text-muted">
{{ $staff->gpay_number ?? '-' }}
</p>

<hr>

<p><strong>Default Salary Amount (â‚¹):</strong></p>
<div class="input-group input-group-sm mb-2">
<input type="number"
       step="0.01"
       id="salaryAmountInput"
       class="form-control"
       value="{{ $staff->salary_amount ?? 0 }}"
       placeholder="0.00">
<button class="btn btn-primary" type="button" id="updateSalaryBtn">
<i class="fas fa-save"></i>
</button>
</div>

</div>

</div>

</div>


{{-- =========================
   SALARY PAYMENT HISTORY
========================= --}}
<div class="col-md-8">

<div class="card">

<div class="card-header d-flex justify-content-between align-items-center">

<h5 class="mb-0">Salary Payment History</h5>

<button class="btn btn-sm btn-primary"
data-bs-toggle="modal"
data-bs-target="#salaryModal">

<i class="fas fa-plus"></i> Record Salary

</button>

</div>

<div class="card-body">

<div class="table-responsive">
<table class="table table-bordered table-sm">

<thead>
<tr>
<th>Salary Month</th>
<th>Salary Amount</th>
<th>Paid Amount</th>
<th>Status</th>
<th>Paid Date</th>
<th width="100">Action</th>
</tr>
</thead>

<tbody>

@forelse($staff->salaries as $salary)

<tr>

<td>{{ $salary->salary_month_formatted }}</td>

<td>â‚¹ {{ number_format($salary->salary_amount, 2) }}</td>

<td>â‚¹ {{ number_format($salary->paid_amount, 2) }}</td>

<td>
<span class="badge
    @if($salary->status == 'paid') bg-success
    @elseif($salary->status == 'partial') bg-warning
    @else bg-danger @endif">
{{ ucfirst(str_replace('_', ' ', $salary->status)) }}
</span>
</td>

<td>{{ optional($salary->paid_date)->format('d M Y') ?? '-' }}</td>

<td>
@if($salary->status != 'paid')
    <button class="btn btn-sm editSalary" title="Edit Salary"
    data-id="{{ $salary->id }}"
    data-month="{{ $salary->salary_month }}"
    data-salary_amount="{{ $salary->salary_amount }}"
    data-paid_amount="{{ $salary->paid_amount }}"
    data-paid_date="{{ optional($salary->paid_date)->format('Y-m-d') }}"
    data-payment_method="{{ $salary->payments->first()?->payment_method ?? '' }}"
    data-remarks="{{ $salary->remarks }}">

    <i class="fas fa-pencil-alt text-primary"></i>

    </button>
@endif

<button class="btn btn-sm viewPaymentHistory" title="View Payment History"
data-id="{{ $salary->id }}"
data-salary_month="{{ $salary->salary_month_formatted }}"
data-salary_amount="{{ $salary->salary_amount }}"
data-paid_amount="{{ $salary->paid_amount }}"
data-payments="{{ json_encode($salary->payments->map(function($p) { return ['id' => $p->id, 'amount' => $p->paid_amount, 'method' => $p->payment_method, 'date' => optional($p->paid_date)->format('d M Y'), 'notes' => $p->notes]; })->values()->all()) }}">

<i class="fas fa-history text-info"></i>

</button>

@if($salary->paid_amount < $salary->salary_amount)
<button class="btn btn-sm payBalance" title="Pay Balance"
data-id="{{ $salary->id }}"
data-salary_amount="{{ $salary->salary_amount }}"
data-paid_amount="{{ $salary->paid_amount }}"
data-salary_month="{{ $salary->salary_month_formatted }}"
data-balance="{{ $salary->salary_amount - $salary->paid_amount }}">

<i class="fas fa-money-bill text-success"></i>

</button>
@endif

</td>

</tr>

@empty

<tr>
<td colspan="6" class="text-center text-muted">
No salary payments recorded
</td>
</tr>

@endforelse

</tbody>

</table>
</div>

</div>

</div>

</div>


{{-- =========================
   ID PROOF
========================= --}}
@if($staff->id_proof)
<div class="col-md-12">

<div class="card">

<div class="card-header">
<h5>ID Proof Document</h5>
</div>

<div class="card-body">

<a href="{{ asset('storage/'.$staff->id_proof) }}" target="_blank" class="btn btn-sm btn-info">
<i class="fas fa-file"></i> View ID Proof
</a>

</div>

</div>

</div>
@endif


</div>


{{-- ================= SALARY MODAL ================= --}}
<div class="modal fade" id="salaryModal">

<div class="modal-dialog">

<div class="modal-content">

<form method="POST" id="salaryForm"
action="{{ route('admin.staffs.salary.store') }}">

@csrf

<div class="modal-header">

<h5 class="salary_modal modal-title">Record Salary Payment</h5>

<button type="button" class="btn-close"
data-bs-dismiss="modal"></button>

</div>

<div class="modal-body">

<input type="hidden" name="staff_id" value="{{ $staff->id }}">
<input type="hidden" name="staff_salary_id" id="staffSalaryId" value="">
<input type="hidden" name="_method" id="formMethod" value="POST">

<div class="mb-3">

<label class="form-label">Salary Month</label>

<input type="month"
name="salary_month"
class="form-control"
required>

</div>

<div class="mb-3">

<label class="form-label">Payment Amount (â‚¹)</label>

<input type="number"
step="0.01"
name="paid_amount"
id="paidAmountField"
class="form-control"
value="{{ $staff->salary_amount ?? 0 }}">

</div>

<div class="mb-3">

<label class="form-label">Payment Method</label>

@php
$methods = [
    'cash' => 'Cash',
    'card' => 'Card',
    'upi' => 'UPI',
    'bank_transfer' => 'Bank Transfer'
];
@endphp

<select name="payment_method" class="form-control">
    <option value="">- Select Payment Method -</option>

    @foreach($methods as $key => $label)
        <option value="{{ $key }}"
            {{ old('payment_method', $staff->payment_method ?? '') == $key ? 'selected' : '' }}>
            {{ $label }}
        </option>
    @endforeach
</select>

</div>

<div class="mb-3">

<label class="form-label">Payment Date</label>

<input type="date"
name="payment_date"
id="paymentDateField"
class="form-control"
value="{{ date('Y-m-d') }}">

</div>

<div class="mb-3">

<label class="form-label">Remarks</label>

<textarea name="remarks"
class="form-control"
rows="2"></textarea>

</div>

</div>

<div class="modal-footer">

<button type="button"
class="btn btn-secondary"
data-bs-dismiss="modal">

Cancel

</button>

<button class="btn btn-primary">

Save Salary

</button>

</div>

</form>

</div>

</div>

</div>


{{-- ================= BALANCE PAYMENT MODAL ================= --}}
<div class="modal fade" id="balancePaymentModal">

<div class="modal-dialog">

<div class="modal-content">

<form method="POST" id="balancePaymentForm"
action="{{ route('admin.staffs.salary.pay-balance') }}">

@csrf

<div class="modal-header">

<h5 class="modal-title">Pay Balance Amount</h5>

<button type="button" class="btn-close"
data-bs-dismiss="modal"></button>

</div>

<div class="modal-body">

<input type="hidden" name="staff_salary_id" id="balanceStaffSalaryId" value="">

<div class="mb-3">

<label class="form-label">Salary Month</label>

<input type="text"
id="balanceSalaryMonth"
class="form-control"
disabled>

</div>

<div class="mb-3">

<label class="form-label">Salary Amount (â‚¹)</label>

<input type="number"
step="0.01"
id="balanceSalaryAmount"
class="form-control"
disabled>

</div>

<div class="mb-3">

<label class="form-label">Already Paid (â‚¹)</label>

<input type="number"
step="0.01"
id="balanceAlreadyPaid"
class="form-control"
disabled>

</div>

<div class="mb-3">

<label class="form-label text-danger"><strong>Balance Due (â‚¹)</strong></label>

<input type="number"
step="0.01"
id="balanceDueAmount"
class="form-control"
disabled>

</div>

<div class="mb-3">

<label class="form-label">Payment Amount (â‚¹)</label>

<input type="number"
step="0.01"
name="payment_amount"
id="balancePaymentAmount"
class="form-control"
required
placeholder="Enter amount to pay">

</div>

<div class="mb-3">

<label class="form-label">Payment Method</label>

@php
$methods = [
    'cash' => 'Cash',
    'card' => 'Card',
    'upi' => 'UPI',
    'bank_transfer' => 'Bank Transfer'
];
@endphp

<select name="payment_method" class="form-control" required>
<option value="">- Select Payment Method -</option>

@foreach($methods as $key => $label)
<option value="{{ $key }}">{{ $label }}</option>
@endforeach

</select>

</div>

<div class="mb-3">

<label class="form-label">Payment Date</label>

<input type="date"
name="payment_date"
id="balancePaymentDate"
class="form-control"
value="{{ date('Y-m-d') }}">

</div>

<div class="mb-3">

<label class="form-label">Remarks</label>

<textarea name="remarks"
class="form-control"
rows="2"></textarea>

</div>

</div>

<div class="modal-footer">

<button type="button"
class="btn btn-secondary"
data-bs-dismiss="modal">

Cancel

</button>

<button class="btn btn-success" type="submit">

Pay Balance

</button>

</div>

</form>

</div>

</div>

</div>


{{-- ================= PAYMENT HISTORY MODAL ================= --}}
<div class="modal fade" id="paymentHistoryModal">

<div class="modal-dialog modal-lg">

<div class="modal-content">

<div class="modal-header">

<h5 class="modal-title">Payment History - <span id="historyMonth"></span></h5>

<button type="button" class="btn-close"
data-bs-dismiss="modal"></button>

</div>

<div class="modal-body">

<div class="mb-3">
<strong>Total Salary:</strong> â‚¹<span id="historyTotalSalary">0.00</span>
&nbsp;&nbsp;&nbsp;
<strong>Total Paid:</strong> â‚¹<span id="historyTotalPaid">0.00</span>
&nbsp;&nbsp;&nbsp;
<strong>Balance Due:</strong> â‚¹<span id="historyBalanceDue">0.00</span>
</div>

<div class="table-responsive">
<table class="table table-bordered table-sm">

<thead class="table-light">
<tr>
<th>Amount</th>
<th>Payment Method</th>
<th>Payment Date</th>
<th>Notes</th>
</tr>
</thead>

<tbody id="paymentHistoryTable">
<tr>
<td colspan="4" class="text-center text-muted">No payments recorded</td>
</tr>
</tbody>

</table>
</div>

</div>

<div class="modal-footer">

<button type="button"
class="btn btn-secondary"
data-bs-dismiss="modal">

Close

</button>

</div>

</div>

</div>

</div>

@endsection


@section('script')

<script>

// Update Salary Amount
$('#updateSalaryBtn').click(function() {
    let newAmount = $('#salaryAmountInput').val();

    if (!newAmount || newAmount < 0) {
        alert('Please enter a valid salary amount');
        return;
    }

    $.ajax({
        url: '{{ route("admin.staffs.salary.amount.update") }}',
        type: 'PUT',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        data: {
            staff_id: {{ $staff->id }},
            salary_amount: newAmount
        },
        success: function(response) {
            alert('Salary amount updated successfully');
            location.reload();
        },
        error: function(xhr) {
            alert('Error: ' + (xhr.responseJSON?.message || 'Unknown error'));
        }
    });
});

// Open salary modal for new record
$('#salaryModal').on('show.bs.modal', function() {
    let today = new Date().toISOString().split('T')[0]; // YYYY-MM-DD format
    let todayMonth = today.substring(0, 7); // YYYY-MM format for month input
    let defaultSalary = {{ $staff->salary_amount ?? 0 }};

    // Reset form for new record if not editing
    if (!$(this).data('isEditing')) {
        $('#salaryForm').attr('action', '{{ route("admin.staffs.salary.store") }}');
        $('#formMethod').val('POST');
        $('#staffSalaryId').val('');
        $('input[name=salary_month]').val(todayMonth);
        $('input[name=salary_month]').prop('disabled', false);
        $('#salaryAmountField').val(defaultSalary);
        $('#salaryAmountField').prop('disabled', false);
        $('#paidAmountField').val(defaultSalary);
        $('#paidAmountField').prop('disabled', false);
        $('#paymentDateField').val(today);
        $('#paymentDateField').prop('disabled', false);
        $('select[name=payment_method]').val('');
        $('select[name=payment_method]').prop('disabled', false);
        $('textarea[name=remarks]').val('');
        $('textarea[name=remarks]').prop('disabled', false);
        $('.salary_modal.modal-title').text('Record Salary Payment');
    }
    $(this).data('isEditing', false);
});

// Edit existing salary
$('.editSalary').click(function(){
    let id = $(this).data('id');
    let month = $(this).data('month'); // Format: YYYY-MM
    let salary_amount = $(this).data('salary_amount');
    let paid_amount = $(this).data('paid_amount');
    let paid_date = $(this).data('paid_date');
    let payment_method = $(this).data('payment_method') || '';
    let remarks = $(this).data('remarks');

    // Set form for edit mode
    $('#salaryForm').attr('action', '{{ route("admin.staffs.salary.update") }}');
    $('#formMethod').val('PUT');
    $('#staffSalaryId').val(id);

    $('input[name=salary_month]').val(month);
    $('#salaryAmountField').val(salary_amount);
    $('#paidAmountField').val(paid_amount);
    $('#paymentDateField').val(paid_date);
    $('select[name=payment_method]').val(payment_method);
    $('textarea[name=remarks]').val(remarks);

    $('.salary_modal.modal-title').text('Edit Salary Payment');
    $('#salaryModal').data('isEditing', true);
    $('#salaryModal').modal('show');
});

// Pay balance for partial payment
$('.payBalance').click(function(){
    let id = $(this).data('id');
    let salaryAmount = $(this).data('salary_amount');
    let paidAmount = $(this).data('paid_amount');
    let salaryMonth = $(this).data('salary_month');
    let balance = $(this).data('balance');

    $('#balanceStaffSalaryId').val(id);
    $('#balanceSalaryMonth').val(salaryMonth);
    $('#balanceSalaryAmount').val(salaryAmount);
    $('#balanceAlreadyPaid').val(paidAmount);
    $('#balanceDueAmount').val(balance);
    $('#balancePaymentAmount').val('');
    $('#balancePaymentDate').val('{{ date('Y-m-d') }}');
    $('select[name=payment_method]').val('');
    $('textarea[name=remarks]').val('');

    $('#balancePaymentModal').modal('show');
});

// View payment history
$('.viewPaymentHistory').click(function(){
    let salaryMonth = $(this).data('salary_month');
    let salaryAmount = $(this).data('salary_amount');
    let paidAmount = $(this).data('paid_amount');
    let payments = $(this).data('payments');

    // Set header and summary
    $('#historyMonth').text(salaryMonth);
    $('#historyTotalSalary').text(parseFloat(salaryAmount).toFixed(2));
    $('#historyTotalPaid').text(parseFloat(paidAmount).toFixed(2));
    $('#historyBalanceDue').text((parseFloat(salaryAmount) - parseFloat(paidAmount)).toFixed(2));

    // Clear previous rows
    $('#paymentHistoryTable').html('');

    // If no payments, show empty message
    if (!payments || payments.length === 0) {
        $('#paymentHistoryTable').html('<tr><td colspan="4" class="text-center text-muted">No payments recorded</td></tr>');
    } else {
        // Add payment rows
        let html = '';
        payments.forEach(function(payment) {
            html += '<tr>';
            html += '<td>â‚¹ ' + parseFloat(payment.amount).toFixed(2) + '</td>';
            html += '<td><span class="badge bg-secondary">' + (payment.method ? payment.method.charAt(0).toUpperCase() + payment.method.slice(1).replace('_', ' ') : 'N/A') + '</span></td>';
            html += '<td>' + (payment.date || '-') + '</td>';
            html += '<td>' + (payment.notes || '-') + '</td>';
            html += '</tr>';
        });
        $('#paymentHistoryTable').html(html);
    }

    $('#paymentHistoryModal').modal('show');
});

// Validate balance payment amount on input change
$('#balancePaymentAmount').on('change keyup', function(){
    let paymentAmount = parseFloat($(this).val()) || 0;
    let balanceDue = parseFloat($('#balanceDueAmount').val()) || 0;

    if (paymentAmount > balanceDue) {
        $(this).addClass('is-invalid');
        $('.balance-error').remove();
        $(this).after('<div class="invalid-feedback d-block balance-error">Payment cannot exceed balance due (â‚¹' + balanceDue.toFixed(2) + ')</div>');
    } else {
        $(this).removeClass('is-invalid');
        $('.balance-error').remove();
    }
});

// Prevent form submission if payment amount exceeds balance
$('#balancePaymentForm').on('submit', function(e){
    let paymentAmount = parseFloat($('#balancePaymentAmount').val()) || 0;
    let balanceDue = parseFloat($('#balanceDueAmount').val()) || 0;

    if (paymentAmount > balanceDue) {
        e.preventDefault();
        alert('Payment amount cannot exceed balance due (â‚¹' + balanceDue.toFixed(2) + ')');
        return false;
    }

    if (paymentAmount <= 0) {
        e.preventDefault();
        alert('Payment amount must be greater than 0');
        return false;
    }
});

</script>

@endsection

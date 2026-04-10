
@section('title','Fees')

@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row">

<div class="col-12">

    <div class="card mb-3">
<div class="card-body p-2">

<ul class="nav nav-pills">

<li class="nav-item">
<a class="nav-link {{ $tab == 'unpaid' ? 'active' : '' }}"
href="{{ $routeTemplateUnPaid }}">
Pending
</a>
</li>

<li class="nav-item">
<a class="nav-link {{ $tab == 'overdue' ? 'active' : '' }}"
href="{{ $routeTemplateOverdue }}">
Over Due
</a>
</li>

<li class="nav-item">
<a class="nav-link {{ $tab == 'paid' ? 'active' : '' }}"
href="{{ $routeTemplatePaid }}">
Full Paid
</a>
</li>

</ul>

</div>
</div>
<div class="card">

<div class="card-header d-flex justify-content-between">

<h4>
@if($tab == 'paid')
    Fee Paid
@elseif($tab == 'overdue')
    Over Due Fee
@else
    Fee Pending
@endif
</h4>

</div>

<div class="card-body table-responsive">

{{-- FILTER --}}

<form method="GET" action="{{ $filterRoute }}">

    <div class="card mb-3">

    <div class="card-body">

    <div class="row">

    {{-- 🔍 Search --}}
    <div class="col-md-3 mb-2">
    <input type="text"
    name="search"
    class="form-control"
    placeholder="Search student..."
    value="{{ request('search') }}">
    </div>

    {{-- 📚 Class --}}
    <div class="col-md-2 mb-2">
    <select name="class_room_id" class="form-control select2-class-ajax"
        data-ajax-url="{{ $classRoomSearchUrl }}"
        data-selected-id="{{ request('class_room_id') }}"
        data-selected-text="{{ $selectedClassName ?? '' }}">
    @if(request('class_room_id') && isset($selectedClassName))
    <option value="{{ request('class_room_id') }}" selected>{{ $selectedClassName }}</option>
    @endif
    </select>
    </div>

    {{-- 🏷 Type --}}
    <div class="col-md-2 mb-2">
    <select name="type" class="form-control">
    <option value="">All Types</option>
    <option value="admission"
    {{ request('type') == 'admission' ? 'selected' : '' }}>
    Admission
    </option>
    <option value="monthly"
    {{ request('type') == 'monthly' ? 'selected' : '' }}>
    Monthly
    </option>
    </select>
    </div>

    {{-- 📊 Status --}}
    <div class="col-md-2 mb-2">
    <select name="status" class="form-control">
    <option value="">All Status</option>
    <option value="unpaid"
    {{ request('status') == 'unpaid' ? 'selected' : '' }}>
    Unpaid
    </option>
    <option value="partial"
    {{ request('status') == 'partial' ? 'selected' : '' }}>
    Partial
    </option>
    </select>
    </div>

    {{-- ⏳ Sort --}}
    <div class="col-md-2 mb-2">
    <select name="sort" class="form-control">
    <option value="latest"
    {{ request('sort') == 'latest' ? 'selected' : '' }}>
    Latest
    </option>
    <option value="due_date"
    {{ request('sort') == 'due_date' ? 'selected' : '' }}>
    Due Date
    </option>
    <option value="amount"
    {{ request('sort') == 'amount' ? 'selected' : '' }}>
    Amount
    </option>
    </select>
    </div>

    </div>

    {{-- Buttons --}}


    <div class="col-md-3 d-flex gap-2">

    <button class="btn btn-primary">Filter</button>

    <a href="{{ $filterRoute }}"
    class="btn btn-light">Reset</a>
    @if($isExport=='true')
        <a href="{{ route('admin.reports.fee.export', request()->query()) }}"
        class="btn btn-success">
            <i class="mdi mdi-file-excel"></i> Export
        </a>
    @endif
    </div>

    </div>

    </div>

</form>


{{-- TABLE --}}
<table class="table table-bordered align-middle">

<thead>

<tr>
<th>Student</th>
<th>Class</th>
<th>Type</th>
<th>Amount</th>
<th>Due Date</th>
<th>Status</th>
@if($isAction=='true')
<th>Actions</th>
@endif
</tr>

</thead>

<tbody>

@forelse($fees as $fee)

@php
    $paid = $fee->paid_amount ?? 0;
    $remaining = $fee->amount - $paid;

    $dueDate = \Carbon\Carbon::parse($fee->due_date);

    // Only calculate overdue if still pending
    $daysOverdue = ($remaining > 0 && $dueDate->isPast())
    ? $dueDate->startOfDay()->diffInDays(now()->startOfDay())
    : 0;
@endphp

<tr class="{{ $fee->rowStyle['class'] }}" style="{{ $fee->rowStyle['style'] }}">

<td>{{ $fee->student->name ?? '-' }}</td>

<td>{{ $fee->classRoom->name ?? '-' }}</td>

<td>
<span class="badge bg-info">
{{ ucfirst($fee->type) }}
</span>
</td>

<td>
    <strong>₹ {{ number_format($fee->amount,2) }}</strong><br>

    <small class="text-success">
        Paid: ₹ {{ number_format($paid,2) }}
    </small><br>

    <small class="text-danger">
        Remaining: ₹ {{ number_format($remaining,2) }}
    </small>
    @php
        $percentage = $fee->amount > 0 ? ($paid / $fee->amount) * 100 : 0;
    @endphp

    <div class="progress mt-1" style="height:6px;" title="{{ round($percentage) }}% paid">
        <div class="progress-bar bg-success"
            style="width: {{ $percentage }}%">
        </div>
    </div>
</td>

<td>
{{ \Carbon\Carbon::parse($fee->due_date)->format('d M Y') }}
@if($daysOverdue > 0)
    <br><small class="badge bg-danger">{{ $daysOverdue }} days overdue</small>
@endif
</td>

<td>
    @php
        $badgeClasses = [
            'paid' => 'bg-success',
            'unpaid' => 'bg-danger',
            'partial' => 'bg-warning text-dark',
        ];
    @endphp
<span class="badge {{ $badgeClasses[$fee->status] ?? 'bg-danger' }}">{{ ucfirst($fee->status) }}</span>
</td>
@if($isAction=='true')
    <td>

        @if($tab !== 'paid')
        <button class="btn btn-sm btn-success markPaidBtn"
        data-id="{{ $fee->id }}"
        data-amount="{{ $fee->amount }}"
        data-remaining="{{ $remaining }}"
        {{ $remaining <= 0 ? 'disabled' : '' }}>
        <i class="fas fa-check"></i>
        </button>
        @endif

        <button class="btn btn-sm btn-info viewPaymentsBtn"
        data-url="{{ route('staff.fees.payments', $fee->id) }}">
        <i class="fas fa-eye"></i>
        </button>

        @if($tab === 'paid')
        <a href="{{ route('staff.fees.invoice.download', $fee->id) }}"
           class="btn btn-sm btn-danger"
           title="Download Invoice PDF"
           target="_blank">
            <i class="mdi mdi-file-pdf-box"></i>
        </a>
        @endif

        @if($tab !== 'paid')
        <button class="btn btn-sm btn-warning sendNotificationBtn"
        data-id="{{ $fee->id }}"
        title="Send notification">
        <i class="fas fa-bell"></i>
        </button>
        @endif

    </td>
@endif

</tr>

@empty

<tr>
<td colspan="8" class="text-center">
No Fees
</td>
</tr>

@endforelse

</tbody>

</table>

<div class="mt-3">
{{ $fees->links() }}
</div>

</div>

</div>

</div>

</div>

@if($isAction=='true')
    {{-- Payment Modal --}}

<div class="modal fade" id="paymentModal">

<div class="modal-dialog">
<div class="modal-content">

<form method="POST" action="{{ route('staff.fees.pay') }}">
@csrf

<input type="hidden" name="fee_id" id="fee_id">

<div class="modal-header">
<h5>Mark Payment</h5>
</div>

<div class="modal-body">

<div class="mb-3">
<label>Total Fee</label>
<input type="text" id="total_fee" class="form-control" readonly>
</div>

<div class="mb-3">
<label>Amount Paying</label>
<input type="number"
step="0.01"
name="paid_amount"
class="form-control"
required>
</div>

<div class="mb-3">
<label>Payment Method</label>
<select name="payment_method" class="form-control" required>
<option value="cash">Cash</option>
<option value="card">Card</option>
<option value="upi">UPI</option>
<option value="bank_transfer">Bank Transfer</option>
</select>
</div>

<div class="mb-3">
<label>Paid Date</label>
<input type="date"
name="paid_date"
class="form-control"
value="{{ date('Y-m-d') }}"
required>
</div>

<div class="mb-3">
<label>Notes</label>
<textarea name="notes" class="form-control"></textarea>
</div>

</div>

<div class="modal-footer">
<button class="btn btn-success" type="submit" onclick="this.disabled=true; this.innerText='Saving...'; this.form.submit();">Save Payment</button>
</div>

</form>

</div>
</div>

</div>

{{-- Payment History Modal --}}
    <div class="modal fade" id="paymentsModal">

<div class="modal-dialog modal-lg">
<div class="modal-content">

<div class="modal-header">
<h5>Payment History</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
    <p><strong>Total Paid:</strong> ₹ <span id="totalPaid"></span></p>

<table class="table table-bordered">

<thead>
<tr>
<th>Date</th>
<th>Amount</th>
<th>Method</th>
<th>Notes</th>
</tr>
</thead>

<tbody id="paymentsTableBody">

<tr>
<td colspan="4" class="text-center text-muted">
Loading...
</td>
</tr>

</tbody>

</table>

</div>

</div>
</div>

</div>

{{-- Payment History Modal End --}}
@endif

@endsection

@section('script')
@if($isAction=='true')

{{-- <script>
$('.select2').select2({
    placeholder: "Select option",
    allowClear: true
});
</script> --}}
    <script>

            $('.markPaidBtn').click(function(){

                let feeId = $(this).data('id');
                let amount = $(this).data('amount');

                $('#fee_id').val(feeId);
                $('#total_fee').val(amount);

                $('#paymentModal').modal('show');

            });

        </script>

        <script>

$('.viewPaymentsBtn').click(function(){

    let url = $(this).data('url');

    $('#paymentsTableBody').html(`
        <tr>
            <td colspan="4" class="text-center">Loading...</td>
        </tr>
    `);

    $('#totalPaid').text('0.00');

    $.get(url, function(res){

        let rows = '';
        let total = 0;

        if(res.payments.length === 0){

            rows = `
                <tr>
                    <td colspan="4" class="text-center text-muted">
                        No payments found
                    </td>
                </tr>
            `;

        } else {

            res.payments.forEach(p => {

                let amount = parseFloat(p.paid_amount);
                total += amount;

                rows += `
                    <tr>
                        <td>${formatDate(p.paid_date)}</td>
                        <td>₹ ${amount.toFixed(2)}</td>
                        <td>${formatMethod(p.payment_method)}</td>
                        <td>${p.notes ?? '-'}</td>
                    </tr>
                `;

            });

        }

        $('#paymentsTableBody').html(rows);
        $('#totalPaid').text(total.toFixed(2));

        $('#paymentsModal').modal('show');

    });

});

function formatDate(dateStr){
    let d = new Date(dateStr);
    return d.toLocaleDateString('en-IN', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
}

function formatMethod(method){
    return method.replace('_',' ').replace(/\b\w/g, l => l.toUpperCase());
}

// Send Notification Handler
$('.sendNotificationBtn').click(function(e){
    e.preventDefault();

    let feeId = $(this).data('id');
    let button = $(this);

    if(confirm('Send notification to student?')) {
        button.prop('disabled', true);
        button.html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            type: 'POST',
            url: '{{ route("staff.fees.send-notification") }}',
            data: {
                _token: '{{ csrf_token() }}',
                fee_id: feeId
            },
            success: function(response) {
                if(response.success) {
                    alert(response.message);
                    button.html('<i class="fas fa-check text-success"></i>');
                } else {
                    alert('Error: ' + response.message);
                    button.prop('disabled', false);
                    button.html('<i class="fas fa-bell"></i>');
                }
            },
            error: function(xhr) {
                let errorMsg = 'An error occurred';
                if(xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                alert('Error: ' + errorMsg);
                button.prop('disabled', false);
                button.html('<i class="fas fa-bell"></i>');
            }
        });
    }
});

</script>
@endif

@endsection

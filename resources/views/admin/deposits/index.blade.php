@extends('admin.layouts.master')

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
            <form method="GET" action="{{ route('admin.deposits.index') }}">
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
                                <a href="{{ route('admin.deposits.index') }}" class="btn btn-secondary">Reset</a>
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
                                    <th>Date</th>
                                    <th>Paid Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($deposits as $deposit)
                                    <tr>
                                        <td>{{ $deposit->teacher->name ?? '-' }}</td>
                                        <td><strong>₹{{ number_format($deposit->amount, 2) }}</strong></td>
                                        <td>Deposited: {{ $deposit->deposited_date ? $deposit->deposited_date->format('d M Y') : '-' }}
                                            @if ($deposit->status != 'paid')    
                                                <br>
                                                <small>Due: {{ $deposit->due_date ? $deposit->due_date->format('d M Y') : '-' }}</small>
                                            @else
                                                <br>
                                                <small>Paid: {{ $deposit->payment_date ? $deposit->payment_date->format('d M Y') : '-' }}</small>
                                            @endif
                                        </td>
                                        <td>₹{{ number_format($deposit->paid_amount, 2) }}
                                            @if ($deposit->status != 'paid')
                                                <br>
                                                <small>Remaining: {{ number_format($deposit->amount - $deposit->paid_amount, 2) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($deposit->status == 'paid')
                                                <span class="badge bg-success">Paid</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Not Paid</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary payDepositBtn" 
                                                data-id="{{ $deposit->id }}" 
                                                data-amount="{{ $deposit->amount }}" 
                                                data-paid="{{ $deposit->paid_amount }}"
                                                {{ $deposit->status == 'paid' ? 'disabled' : '' }}>
                                                <i class="fas fa-money-bill"></i> Pay
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">No deposits found.</td>
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

    {{-- Deposit Payment Modal --}}
    <div class="modal fade" id="depositPaymentModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.deposits.pay') }}">
                    @csrf
                    <input type="hidden" name="deposit_id" id="deposit_id">
                    <div class="modal-header">
                        <h5 class="modal-title">Deposit Payment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Total Deposit Amount</label>
                            <input type="text" id="total_amount_display" class="form-control" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Already Paid Amount</label>
                            <input type="text" id="paid_amount_display" class="form-control" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Remaining Amount</label>
                            <input type="text" id="remaining_amount_display" class="form-control" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amount to Pay</label>
                            <input type="number" step="0.01" name="amount_to_pay" id="amount_to_pay" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Date</label>
                            <input type="date" name="payment_date" id="payment_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" id="payment_method" class="form-control" required>
                                <option value="cash">Cash</option>
                                <option value="upi">UPI</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reference Number</label>
                            <input type="text" name="reference_number" id="reference_number" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" id="notes" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success" id="savePaymentBtn">Save Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script>
        $('.payDepositBtn').click(function () {
            let btn = $(this);
            let id = btn.data('id');
            let amount = parseFloat(btn.data('amount'));
            let paid = parseFloat(btn.data('paid'));
            let remaining = amount - paid;

            $('#deposit_id').val(id);
            $('#total_amount_display').val('₹ ' + amount.toFixed(2));
            $('#paid_amount_display').val('₹ ' + paid.toFixed(2));
            $('#remaining_amount_display').val('₹ ' + remaining.toFixed(2));
            $('#amount_to_pay').val(remaining.toFixed(2)).attr('max', remaining.toFixed(2));

            $('#payment_date').val(new Date().toISOString().split('T')[0]);
            $('#payment_method').val('cash');
            $('#reference_number').val('');
            $('#notes').val('');

            $('#depositPaymentModal').modal('show');
        });

        $('#savePaymentBtn').click(function() {
            $(this).prop('disabled', true).text('Saving...');
            $(this).closest('form').submit();
        });
    </script>
@endsection

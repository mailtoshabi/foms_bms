@extends('staff.layouts.master')

@section('title', 'Teacher Salaries')

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
                                href="{{ route('staff.salaries.index', array_merge(request()->except('page'), ['tab' => 'unpaid'])) }}">

                                Unpaid Salaries
                                <span class="badge {{ $tab == 'unpaid' ? 'bg-white text-primary' : 'bg-light text-dark' }}">
                                    {{ $unpaidCount }}
                                </span>

                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ $tab == 'paid' ? 'active' : '' }}"
                                href="{{ route('staff.salaries.index', array_merge(request()->except('page'), ['tab' => 'paid'])) }}">

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
            <form method="GET" action="{{ route('staff.salaries.index') }}">
                <input type="hidden" name="tab" value="{{ $tab }}">

                <div class="card mb-3">
                    <div class="card-body">

                        <div class="row">

                            {{-- Teacher --}}
                            <div class="col-md-3 mb-2">
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

                            {{-- Date Type --}}
                            <div class="col-md-2 mb-2">
                                <label class="form-label fw-bold">Date Type</label>
                                <select name="date_type" class="form-control">
                                    <option value="cycle_date" {{ request('date_type') == 'cycle_date' || !request('date_type') ? 'selected' : '' }}>Cycle Date</option>
                                    <option value="credit_date" {{ request('date_type') == 'credit_date' ? 'selected' : '' }}>
                                        Credit Date</option>
                                </select>
                            </div>

                            {{-- From Date --}}
                            <div class="col-md-2 mb-2">
                                <label class="form-label fw-bold">From Date</label>
                                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}"
                                    placeholder="From Date">
                            </div>

                            {{-- To Date --}}
                            <div class="col-md-2 mb-2">
                                <label class="form-label fw-bold">To Date</label>
                                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}"
                                    placeholder="To Date">
                            </div>

                            {{-- Buttons --}}
                            <div class="col-md-3 mb-2">
                                <label class="form-label d-none d-md-block">&nbsp;</label>
                                <button class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>

                                <a href="{{ route('staff.salaries.index') }}" class="btn btn-secondary">
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
                        <table class="table table-bordered  align-middle table-nowrap mb-0">

                            <thead>
                                <tr>
                                    <th>Teacher</th>
                                    <th>Cycle</th>
                                    <th>Credit Date</th>
                                    <th>Total Hours</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse($salaries as $salary)

                                    <tr>

                                        <td>
                                            <a
                                                href="{{ route('staff.teachers.show', encrypt($salary->teacher->id)) }}">{{ $salary->teacher->name ?? '-' }}</a>
                                            <div class="mt-1 ">
                                                @if(!empty($salary->teacher->whatsapp_number))
                                                    <a href="https://wa.me/{{ $salary->teacher->whatsapp_number }}" target="_blank"
                                                        class="text-success me-2 text-decoration-none">
                                                        <i class="mdi mdi-whatsapp"></i> +{{ $salary->teacher->whatsapp_number }}
                                                    </a>
                                                @endif
                                                @if(!empty($salary->teacher->upi_number))
                                                    <span class="text-muted d-block d-sm-inline-block mt-1 mt-sm-0">
                                                        <i class="mdi mdi-cash text-primary"></i> Gpay:
                                                        {{ $salary->teacher->upi_number }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        <td>
                                            {{ \Carbon\Carbon::parse($salary->cycle_start)->format('d M Y') }}
                                            -
                                            {{ \Carbon\Carbon::parse($salary->cycle_end)->format('d M Y') }}
                                        </td>

                                        <td>
                                            @if($salary->credit_date)
                                                {{ \Carbon\Carbon::parse($salary->credit_date)->format('d M Y') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>

                                        <td>
                                            <span class="badge bg-info">
                                                {{ number_format($salary->total_hours, 2) }} hrs
                                            </span>
                                        </td>

                                        <td>
                                            <strong class="{{ $salary->status == 'paid' ? 'text-success' : 'text-danger' }}">
                                                ₹ {{ number_format(round($salary->total_amount), 0) }}
                                            </strong>
                                            @if($salary->status == 'paid')
                                                <br><small class="text-muted">
                                                    Paid on {{ optional($salary->payment_date)->format('d M Y') }}
                                                </small>
                                            @endif
                                        </td>

                                        <td>
                                            @if($salary->status == 'paid')
                                                <span class="badge bg-success">Paid</span>
                                            @elseif($salary->status == 'deposit')
                                                <span class="badge bg-danger">Deposit</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Unpaid</span>
                                            @endif
                                        </td>

                                        <td>

                                            <button
                                                class="btn btn-sm btn-primary paySalaryBtn {{ $salary->status == 'paid' ? 'disabled' : '' }}"
                                                data-id="{{ $salary->id }}" data-amount="{{ $salary->total_amount }}"
                                                data-status="{{ $salary->status }}"
                                                data-date="{{ optional($salary->payment_date)->format('d M Y') }}"
                                                data-method="{{ $salary->payment_method }}"
                                                data-ref="{{ $salary->reference_number }}" data-notes="{{ $salary->notes }}">

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

                <form method="POST" action="{{ route('staff.salaries.pay') }}">
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
                            <input type="date" name="payment_date" id="payment_date"
                                class="form-control @error('payment_date') is-invalid @enderror"
                                value="{{ old('payment_date') }}">
                            @error('payment_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label>Payment Method</label>
                            <select name="payment_method" id="payment_method"
                                class="form-control @error('payment_method') is-invalid @enderror">
                                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="upi" {{ old('payment_method') == 'upi' ? 'selected' : '' }}>UPI</option>
                                <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            </select>
                            @error('payment_method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label>Reference Number</label>
                            <input type="text" name="reference_number" id="reference_number"
                                class="form-control @error('reference_number') is-invalid @enderror"
                                value="{{ old('reference_number') }}">
                            @error('reference_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label>Notes</label>
                            <textarea name="notes" id="notes"
                                class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-success" type="submit"
                            onclick="this.disabled=true; this.innerText='Saving...'; this.form.submit();">Save
                            Payment</button>
                    </div>

                </form>

            </div>
        </div>

    </div>

    {{-- Payment Modal End --}}

@endsection

@section('script')
    <script>

        $('.paySalaryBtn').click(function () {

            let btn = $(this);

            $('#salary_id').val(btn.data('id'));

            $('#salary_amount').val('₹ ' + parseFloat(btn.data('amount')).toFixed(2));

            // Prefill
            $('#payment_date').val(btn.data('date') || new Date().toISOString().split('T')[0]);
            $('#payment_method').val(btn.data('method') || 'cash');
            $('#reference_number').val(btn.data('ref') || '');
            $('#notes').val(btn.data('notes') || '');

            $('#salaryPaymentModal').modal('show');

        });

    </script>
@endsection
@extends('admin.layouts.master')
@section('title', 'Fee Collection Report')

@section('content')

    <div class="card">

        <div class="card-header d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
            <h4 class="mb-0">
                <a href="javascript:window.history.back();"
                    class="btn btn-sm btn-light border-0 shadow-sm me-2 rounded-circle" title="Go Back">
                    <i class="fas fa-chevron-left"></i>
                </a>
                Fee Collection Report
            </h4>
            <a href="{{ route('admin.reports.fee.collection.export', request()->query()) }}" class="btn btn-success w-75 mx-auto me-sm-0 ms-sm-auto w-sm-auto mt-2 mt-sm-0 text-center">
                <i class="fas fa-file-excel me-1"></i> Export Excel
            </a>
        </div>

        <div class="card-body table-responsive">

            <form method="GET" class="mb-4">
                <div class="row g-3">
                    {{-- Basic Filters --}}
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Search Student</label>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                            placeholder="Search by name...">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Course Category</label>
                        <select name="category_id" class="form-control">
                            <option value="">All Categories</option>
                            @forelse($categories as $id => $name)
                                <option value="{{ $id }}" {{ request('category_id') == $id ? 'selected' : '' }}>
                                    {{ ucfirst($name) }}
                                </option>
                            @empty
                            @endforelse
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Class Room</label>
                        <select name="class_room_id" class="form-control select2-class-ajax"
                            data-ajax-url="{{ route('admin.class_rooms.search') }}">
                            @if(request('class_room_id') && isset($selectedClassName))
                                <option value="{{ request('class_room_id') }}" selected>{{ $selectedClassName }}</option>
                            @endif
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Payment Method</label>
                        <select name="payment_method" class="form-control">
                            <option value="">All Methods</option>
                            <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="upi" {{ request('payment_method') == 'upi' ? 'selected' : '' }}>UPI</option>
                            <option value="card" {{ request('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
                            <option value="bank_transfer" {{ request('payment_method') == 'bank_transfer' ? 'selected' : '' }}>
                                Bank Transfer
                            </option>
                        </select>
                    </div>

                    {{-- Date Range --}}
                    <div class="col-md-3">
                        <label class="form-label fw-bold">From Date</label>
                        <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">To Date</label>
                        <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control">
                    </div>

                    {{-- Actions --}}
                    <div class="col-md-6 d-flex align-items-end justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="mdi mdi-filter"></i> Filter
                        </button>

                        <a href="{{ route('admin.reports.fee.collection') }}" class="btn btn-light px-4">
                            <i class="mdi mdi-refresh"></i> Reset
                        </a>
                    </div>
                </div>
            </form>

            <hr class="my-4">

            @if($isFiltered)
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card bg-soft-info border-info">
                            <div class="card-body p-3">
                                <h5 class="text-info mb-3"><i class="mdi mdi-information-outline me-1"></i> Filtering Summary</h5>
                                <div class="row text-center">
                                    <div class="col-md-4 border-end">
                                        <p class="text-muted mb-1 small uppercase fw-bold">Gross Collection</p>
                                        <h3 class="text-success mb-0 fw-bold">₹ {{ number_format($totalGross, 2) }}</h3>
                                    </div>
                                    <div class="col-md-4 border-end">
                                        <p class="text-muted mb-1 small uppercase fw-bold">Total Refunded</p>
                                        <h3 class="text-danger mb-0 fw-bold">₹ {{ number_format($totalRefunded, 2) }}</h3>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="text-muted mb-1 small uppercase fw-bold">Net Collection</p>
                                        <h3 class="text-primary mb-0 fw-bold">₹ {{ number_format($totalNet, 2) }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <table class="table table-bordered align-middle">

                <thead>
                    <tr>
                        <th>Student</th>
                        <!-- <th>Contact</th> -->
                        <th>Class</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Payment Date</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($data as $row)

                        <tr>
                            <td>
                                <a
                                    href="{{ route('admin.reports.students.show', encrypt($row->student_id)) }}">{{ $row->name }}</a>
                                <br><small class="text-muted">{{ $row->contact_number }}</small>
                                @if($row->is_whatsapp_different)
                                    <br><small class="text-success" style="font-size: 11px;">WA:
                                        +{{ $row->whatsapp_number }}</small>
                                @endif
                            </td>
                            <td>
                                <a
                                    href="{{ route('admin.class_rooms.show', encrypt($row->class_room_id)) }}">{{ $row->class_name }}</a>
                            </td>
                            <td><span class="badge bg-info">{{ ucfirst($row->category_name) }}</span></td>
                            <td>₹ {{ number_format($row->paid_amount, 2) }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $row->payment_method)) }}</td>
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
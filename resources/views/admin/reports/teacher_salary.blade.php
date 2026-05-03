@extends('admin.layouts.master')
@section('title', 'Teacher Salary Report')

@section('content')

       <div class="card mb-3">
              <div class="card-body p-2">
                     <ul class="nav nav-pills">
                            <li class="nav-item">
                                   <a class="nav-link {{ $tab == 'unpaid' ? 'active' : '' }}"
                                          href="{{ route('admin.reports.teacher.salary', array_merge(request()->except('page'), ['tab' => 'unpaid'])) }}">
                                          Pending Salaries
                                   </a>
                            </li>
                            <li class="nav-item">
                                   <a class="nav-link {{ $tab == 'paid' ? 'active' : '' }}"
                                          href="{{ route('admin.reports.teacher.salary', array_merge(request()->except('page'), ['tab' => 'paid'])) }}">
                                          Paid Salaries
                                   </a>
                            </li>
                     </ul>
              </div>
       </div>

       <div class="card">
              <div class="card-header d-flex align-items-center">
                     <a href="javascript:window.history.back();" class="btn btn-sm btn-light border-0 shadow-sm me-2 rounded-circle" title="Go Back">
                            <i class="fas fa-chevron-left"></i>
                     </a>
                     <h4 class="mb-0">
                            @if ($tab == 'paid')
                                   Paid Salaries
                            @else
                                   Pending Salaries
                            @endif
                     </h4>
              </div>

              <div class="card-body table-responsive">

                     <form method="GET" action="{{ route('admin.reports.teacher.salary') }}" class="mb-4">
                            <input type="hidden" name="tab" value="{{ $tab }}">
                            <div class="row g-3">
                                   <div class="col-md-2">
                                          <label class="form-label fw-bold">Teacher Name/Phone</label>
                                          <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                                                 placeholder="Search teacher...">
                                   </div>

                                   <div class="col-md-2">
                                          <label class="form-label fw-bold">Status</label>
                                          <select name="status" class="form-control">
                                                 <option value="">All Status</option>
                                                 @if ($tab == 'paid')
                                                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>
                                                               Paid</option>
                                                 @else
                                                        <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>
                                                               Unpaid
                                                        </option>
                                                        <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>
                                                               Partial
                                                        </option>
                                                 @endif
                                          </select>
                                   </div>

                                   <div class="col-md-2">
                                          <label class="form-label fw-bold">From Date</label>
                                          <input type="date" name="from_date" value="{{ request('from_date') }}"
                                                 class="form-control">
                                   </div>

                                   <div class="col-md-2">
                                          <label class="form-label fw-bold">To Date</label>
                                          <input type="date" name="to_date" value="{{ request('to_date') }}"
                                                 class="form-control">
                                   </div>

                                   <div class="col-md-4 d-flex align-items-end gap-2">
                                          <button type="submit" class="btn btn-primary px-3">
                                                 <i class="mdi mdi-filter"></i> Filter
                                          </button>

                                          <a href="{{ route('admin.reports.teacher.salary', ['tab' => $tab]) }}"
                                                 class="btn btn-light px-3">
                                                 <i class="mdi mdi-refresh"></i> Reset
                                          </a>

                                          <a href="{{ route('admin.reports.teacher.salary.export', request()->query()) }}"
                                                 class="btn btn-success px-3">
                                                 <i class="mdi mdi-file-excel"></i> Export
                                          </a>
                                   </div>
                            </div>
                     </form>

                     <hr class="my-4">

                     @if (isset($isFiltered) && $isFiltered)
                            <div class="row mb-4">
                                   <div class="col-md-12">
                                          <div class="card bg-soft-info border-info">
                                                 <div class="card-body d-flex justify-content-between align-items-center p-3">
                                                        <div>
                                                               <h5 class="text-info mb-1"><i
                                                                             class="mdi mdi-information-outline me-1"></i>
                                                                      Filtering Summary</h5>
                                                               <p class="text-muted mb-0 small">Showing total results based on
                                                                      your selected criteria.</p>
                                                        </div>
                                                        <div class="text-end">
                                                               <p class="text-muted mb-1 small uppercase fw-bold">Total
                                                                      Salary Amount</p>
                                                               <h3 class="text-primary mb-0 fw-bold">₹
                                                                      {{ number_format($totalAmount ?? 0, 2) }}
                                                               </h3>
                                                        </div>
                                                 </div>
                                          </div>
                                   </div>
                            </div>
                     @endif

                     <table class="table table-bordered align-middle">
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
                                   @forelse($data as $row)
                                          <tr>
                                                 <td>{{ $row->name ?? '-' }}</td>

                                                 <td>
                                                        {{ \Carbon\Carbon::parse($row->cycle_start)->format('d M Y') }}
                                                        -
                                                        {{ \Carbon\Carbon::parse($row->cycle_end)->format('d M Y') }}
                                                 </td>

                                                 <td>
                                                        <span class="badge bg-info">
                                                               {{ number_format($row->total_hours, 2) }} hrs
                                                        </span>
                                                 </td>

                                                 <td>
                                                        <strong class="{{ $row->status == 'paid' ? 'text-success' : 'text-danger' }}">
                                                               ₹ {{ number_format($row->total_amount, 2) }}
                                                        </strong>
                                                        @if($row->status == 'paid')
                                                               <br><small class="text-muted">
                                                                      Paid on
                                                                      {{ $row->payment_date ? \Carbon\Carbon::parse($row->payment_date)->format('d M Y') : '-' }}
                                                               </small>
                                                        @endif
                                                 </td>
                                                 <td>
                                                        <span
                                                               class="badge {{ $row->status == 'paid' ? 'bg-success' : 'bg-warning' }}">
                                                               {{ ucfirst($row->status) }}
                                                        </span>
                                                 </td>
                                                 <td>
                                                        <button class="btn btn-sm btn-primary paySalaryBtn {{ $row->status == 'paid' ? 'disabled' : '' }}"
                                                               data-id="{{ $row->id }}" data-amount="{{ $row->total_amount }}"
                                                               data-status="{{ $row->status }}"
                                                               data-date="{{ optional($row->payment_date)->format('Y-m-d') }}"
                                                               data-method="{{ $row->payment_method }}"
                                                               data-ref="{{ $row->reference_number }}" data-notes="{{ $row->notes }}">

                                                               <i class="fas fa-money-bill"></i>

                                                        </button>

                                                 </td>
                                          </tr>
                                   @empty
                                          <tr>
                                                 <td colspan="6" class="text-center">No Records Found</td>
                                          </tr>
                                   @endforelse
                            </tbody>
                     </table>

                     <div class="mt-3">
                            {{ $data->links() }}
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
                                                 <input type="text" name="reference_number" id="reference_number"
                                                        class="form-control">
                                          </div>

                                          <div class="mb-3">
                                                 <label>Notes</label>
                                                 <textarea name="notes" id="notes" class="form-control"></textarea>
                                          </div>

                                   </div>

                                   <div class="modal-footer">
                                          <button class="btn btn-success"
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

                     $('#payment_date').val(btn.data('date') || new Date().toISOString().split('T')[0]);
                     $('#payment_method').val(btn.data('method') || 'cash');
                     $('#reference_number').val(btn.data('ref') || '');
                     $('#notes').val(btn.data('notes') || '');

                     $('#salaryPaymentModal').modal('show');

              });

       </script>
@endsection

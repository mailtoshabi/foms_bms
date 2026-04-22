@extends('staff.layouts.master')
@section('title', 'Student Attendance Report')

@section('content')

        <div class="card">

                <div class="card-header d-flex justify-content-between">
                        <h4>Student Attendance Report</h4>
                </div>

                <div class="card-body table-responsive">

                        <form method="GET" action="{{ route('staff.reports.attendance') }}" class="mb-4">
                                <div class="row g-3">
                                        <div class="col-md-2">
                                                <label class="form-label fw-bold">Search Student</label>
                                                <input type="text" name="search" value="{{ request('search') }}"
                                                        class="form-control" placeholder="Name/Contact">
                                        </div>

                                        <div class="col-md-2">
                                                <label class="form-label fw-bold">Status</label>
                                                <select name="status" class="form-control">
                                                        <option value="">All</option>
                                                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>
                                                                Present
                                                        </option>
                                                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>
                                                                Absent
                                                        </option>
                                                </select>
                                        </div>

                                        <div class="col-md-2">
                                                <label class="form-label fw-bold">From Date</label>
                                                <input type="date" name="from_date" value="{{ request('from_date') }}"
                                                        class="form-control" placeholder="From Date">
                                        </div>

                                        <div class="col-md-2">
                                                <label class="form-label fw-bold">To Date</label>
                                                <input type="date" name="to_date" value="{{ request('to_date') }}"
                                                        class="form-control" placeholder="To Date">
                                        </div>

                                        <div class="col-md-4 d-flex align-items-end gap-2">
                                                <button type="submit" class="btn btn-primary px-3">
                                                        <i class="mdi mdi-filter"></i> Filter
                                                </button>

                                                <a href="{{ route('staff.reports.attendance') }}" class="btn btn-light px-3">
                                                        <i class="mdi mdi-refresh"></i> Reset
                                                </a>
                                        </div>
                                </div>
                        </form>

                        <hr class="my-4">

                        <table class="table table-bordered align-middle">

                                <thead>
                                        <tr>
                                                <th>Student</th>
                                                <th>Class</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                        </tr>
                                </thead>

                                <tbody>

                                        @forelse($data as $row)

                                                <tr>
                                                        <td>{{ $row->name }}
                                                                <br><small class="text-muted">{{ $row->contact_number }}</small>
                                                                @if($row->is_whatsapp_different)
                                                                    <br><small class="text-success" style="font-size: 11px;">WA: +{{ $row->whatsapp_number }}</small>
                                                                @endif
                                                        </td>
                                                        <td>{{ $row->class_name }}</td>
                                                        <td>{{ \Carbon\Carbon::parse($row->class_started_at)->format('d M Y') }}</td>
                                                        <td>
                                                                <span class="badge {{ $row->is_present ? 'bg-success' : 'bg-danger' }}">
                                                                        {{ $row->is_present ? 'Present' : 'Absent' }}
                                                                </span>
                                                        </td>
                                                </tr>

                                        @empty
                                                <tr>
                                                        <td colspan="4" class="text-center">No Records Found</td>
                                                </tr>
                                        @endforelse

                                </tbody>

                        </table>

                        {{ $data->links() }}

                </div>
        </div>

@endsection

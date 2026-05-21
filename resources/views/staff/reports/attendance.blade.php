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
                                                        <option value="">All Status</option>
                                                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>
                                                                Present
                                                        </option>
                                                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>
                                                                Absent
                                                        </option>
                                                </select>
                                        </div>

                                        <div class="col-md-2">
                                                <label class="form-label fw-bold">Class</label>
                                                <select name="class_room_id" class="form-control select2-class-ajax"
                                                        data-ajax-url="{{ $classRoomSearchUrl }}"
                                                        data-placeholder="Search Class...">
                                                        <option value="">All Classes</option>
                                                        @if(request('class_room_id') && isset($selectedClassName))
                                                                <option value="{{ request('class_room_id') }}" selected>
                                                                        {{ $selectedClassName }}
                                                                </option>
                                                        @endif
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

                        @if(isset($hasFilters) && $hasFilters && isset($summary))
                                <div class="row mb-4">
                                        <div class="col-md-4">
                                                <div class="card bg-soft-primary border-0 shadow-none">
                                                        <div class="card-body text-center">
                                                                <h6 class="text-primary mb-2">Total Records Found</h6>
                                                                <h3 class="mb-0 text-primary">{{ $summary['total'] }}</h3>
                                                        </div>
                                                </div>
                                        </div>
                                        <div class="col-md-4">
                                                <div class="card bg-soft-success border-0 shadow-none">
                                                        <div class="card-body text-center">
                                                                <h6 class="text-success mb-2">Total Present</h6>
                                                                <h3 class="mb-0 text-success">{{ $summary['present'] }}</h3>
                                                        </div>
                                                </div>
                                        </div>
                                        <div class="col-md-4">
                                                <div class="card bg-soft-danger border-0 shadow-none">
                                                        <div class="card-body text-center">
                                                                <h6 class="text-danger mb-2">Total Absent</h6>
                                                                <h3 class="mb-0 text-danger">{{ $summary['absent'] }}</h3>
                                                        </div>
                                                </div>
                                        </div>
                                </div>
                        @endif

                        <table class="table table-bordered align-middle">

                                <thead>
                                        <tr>
                                                <th>Student</th>
                                                <th>Class</th>
                                                <th>Attendance Date</th>
                                                <th>Status</th>
                                        </tr>
                                </thead>

                                <tbody>

                                        @forelse($data as $row)

                                                <tr>
                                                        <td>
                                                                <a href="{{ route('staff.students.show', encrypt($row->id)) }}">{{ $row->name }}
                                                                        <br><small class="text-muted">{{ $row->contact_number }}</small>
                                                                        @if($row->is_whatsapp_different)
                                                                                <br><small class="text-success" style="font-size: 11px;">WA:
                                                                                        +{{ $row->whatsapp_number }}</small>
                                                                        @endif</a>
                                                        </td>
                                                        <td>
                                                                {{ $row->class_name }}
                                                                @if(!empty($row->google_meet_link))
                                                                        <br>
                                                                        <small class="text-muted">{{ $row->google_meet_link }}</small>
                                                                @endif
                                                        </td>
                                                        <td>{{ \Carbon\Carbon::parse($row->created_at)->format('d M Y') }}</td>
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
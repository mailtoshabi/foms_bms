@extends('admin.layouts.master')
@section('title', 'Class Sessions Report')

@section('content')

    <div class="card">
        <div class="card-header d-flex align-items-center">
            <a href="javascript:window.history.back();" class="btn btn-sm btn-light border-0 shadow-sm me-2 rounded-circle" title="Go Back">
                <i class="fas fa-chevron-left"></i>
            </a>
            <h4 class="card-title mb-0">Class Sessions Report</h4>
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.class-hours') }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Class Room</label>
                        <select name="class_room_id" id="class_room_id" class="form-control class-select2">
                            @if(request('class_room_id'))
                                <option value="{{ request('class_room_id') }}" selected>{{ $selectedClassName }}</option>
                            @endif
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Teacher</label>
                        <select name="teacher_id" id="teacher_id" class="form-control teacher-select2">
                            @if(request('teacher_id'))
                                <option value="{{ request('teacher_id') }}" selected>{{ $selectedTeacherName }}</option>
                            @endif
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-bold">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-bold">From Date</label>
                        <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-bold">To Date</label>
                        <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control">
                    </div>

                    <div class="col-md-12 d-flex justify-content-end gap-2 mt-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="mdi mdi-filter"></i> Filter
                        </button>
                        <a href="{{ route('admin.reports.class-hours') }}" class="btn btn-light px-4">
                            <i class="mdi mdi-refresh"></i> Reset
                        </a>
                    </div>
                </div>
            </form>

            @if(request()->anyFilled(['class_room_id', 'teacher_id', 'status', 'from_date', 'to_date']))
                <div class="row mb-4">
                    <div class="col-md-6 col-xl-3">
                        <div class="card bg-primary bg-gradient text-white shadow-sm mb-0">
                            <div class="card-body p-3">
                                <h6 class="text-white-50 mb-2">Total Sessions</h6>
                                <h4 class="mb-0 text-white"><i class="fas fa-video me-2"></i>{{ $totalClassHours }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="card bg-success bg-gradient text-white shadow-sm mb-0">
                            <div class="card-body p-3">
                                <h6 class="text-white-50 mb-2">Total Duration</h6>
                                <h4 class="mb-0 text-white"><i class="fas fa-clock me-2"></i>{{ $totalDurationFormatted }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date & Time</th>
                            <th>Class Room</th>
                            <th>Teacher</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Links / Info</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $row)
                            <tr>
                                <td>
                                    {{ \Carbon\Carbon::parse($row->join_teacher_at)->format('d M Y') }}
                                    <br>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($row->join_teacher_at)->format('h:i A') }}</small>
                                </td>
                                <td>
                                    <strong>{{ $row->classRoom->name ?? 'N/A' }}</strong>
                                    <br>
                                    <small>{{ $row->classRoom->course->name ?? '' }}</small>
                                </td>
                                <td>{{ $row->teacher->name ?? 'N/A' }}</td>
                                <td>{{ $row->duration }} mins</td>
                                <td>
                                    <span class="badge {{ $row->status == 'completed' ? 'bg-success' : 'bg-warning' }}">
                                        {{ ucfirst($row->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($row->google_meet_link)
                                        <a href="{{ $row->google_meet_link }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="mdi mdi-google-meet"></i> Meet
                                        </a>
                                    @endif
                                    
                                    <div class="mt-1">
                                        @if($row->has_fee_calculated)
                                            <span class="badge badge-soft-info" title="Fees Calculated">Fee <i class="mdi mdi-check"></i></span>
                                        @endif
                                        @if($row->has_salary_calculated)
                                            <span class="badge badge-soft-success" title="Salary Calculated">Salary <i class="mdi mdi-check"></i></span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No sessions found matching your criteria.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $data->links() }}
            </div>
        </div>
    </div>

@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('.class-select2').select2({
            placeholder: "Search Class...",
            minimumInputLength: 2,
            allowClear: true,
            ajax: {
                url: "{{ route('admin.class_rooms.search') }}",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.results
                    };
                },
                cache: true
            }
        });

        $('.teacher-select2').select2({
            placeholder: "Search Teacher...",
            minimumInputLength: 2,
            allowClear: true,
            ajax: {
                url: "{{ route('admin.teachers.search') }}",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.results
                    };
                },
                cache: true
            }
        });
    });
</script>
@endsection

@extends('teacher.layouts.master')
@section('title', 'My Sessions')

@section('content')

    <div class="card">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">My Sessions</h4>
        </div>

        <div class="card-body">

            {{-- ── Filters ── --}}
            <form method="GET" class="row g-2 mb-4">

                <div class="col-md-3">
                    <label class="form-label fw-bold">Status</label>
                    <select name="filter" class="form-control">
                        <option value="">All Statuses</option>
                        <optgroup label="Session Status">
                            <option value="status_pending" {{ request('filter') == 'status_pending' ? 'selected' : '' }}>
                                Pending</option>
                            <option value="status_completed" {{ request('filter') == 'status_completed' ? 'selected' : '' }}>
                                Completed</option>
                        </optgroup>
                        <optgroup label="Salary">
                            <option value="salary_0" {{ request('filter') == 'salary_0' ? 'selected' : '' }}>Salary Pending
                            </option>
                            <option value="salary_1" {{ request('filter') == 'salary_1' ? 'selected' : '' }}>Salary Paid
                            </option>
                        </optgroup>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">From Date</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}"
                        placeholder="From Date">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">To Date</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}"
                        placeholder="To Date">
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button class="btn btn-primary">Filter</button>
                    <a href="{{ route('teacher.sessions.index') }}" class="btn btn-light">Reset</a>
                </div>

            </form>

            {{-- ── Table ── --}}
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Class</th>
                            <th>Duration</th>
                            <th>Hourly Wage</th>
                            <th>Status</th>
                            <th>Session Link</th>
                        </tr>
                    </thead>
                    <tbody>

                        @forelse($sessions as $session)
                                        <tr>
                                            <td>{{ $sessions->firstItem() + $loop->index }}</td>

                                            <td>
                                                {{ $session->updated_at
                            ? $session->updated_at->format('d M Y')
                            : '-' }}
                                            </td>

                                            <td>{{ optional($session->classRoom->course)->name ?? '-' }}:
                                                {{ $session->classRoom->name ?? '-' }}
                                            </td>

                                            {{-- <td>{{ $session->classRoom->course->name ?? '-' }}</td> --}}



                                            <td>
                                                @if($session->duration)
                                                    {{ floor($session->duration / 60) }}h
                                                    {{ $session->duration % 60 > 0 ? ($session->duration % 60) . 'm' : '' }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $session->hourly_wage ?? '-' }}</td>

                                            <td>
                                                @if($session->status == 'completed')
                                                    <span class="badge bg-success">Completed</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">Pending</span>
                                                @endif
                                            </td>

                                            <td>
                                                @if($session->google_meet_link && $session->status == 'pending')
                                                    <a href="{{ $session->google_meet_link }}" target="_blank" rel="noopener"
                                                        class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-video"></i> Join
                                                    </a>
                                                @else
                                                    {{-- <span class="text-muted">-</span> --}}
                                                @endif

                                                @if($session->status == 'pending')
                                                    <button class="btn btn-sm btn-warning editClassHour" data-id="{{ $session->id }}"
                                                        data-link="{{ $session->google_meet_link }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                @else
                                                    <button class="btn btn-sm btn-secondary" disabled>
                                                        <i class="fas fa-lock"></i>
                                                    </button>
                                                @endif
                                            </td>

                                        </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">No sessions found</td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>

            {{ $sessions->links() }}

        </div>
    </div>

    {{-- Edit Session Modal --}}
    <div class="modal fade" id="editClassHourModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="editClassHourForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5>Edit Session Link</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Session Link</label>
                            <input type="url" name="google_meet_link" id="edit_meet_link" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" type="submit"
                            onclick="this.disabled=true; this.innerText='Updating...'; this.form.submit();">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script>
        $('.editClassHour').click(function () {
            let id = $(this).data('id');
            let link = $(this).data('link');

            $('#edit_meet_link').val(link);
            $('#editClassHourForm').attr('action', '/teacher/class-hours/' + id);
            $('#editClassHourModal').modal('show');
        });
    </script>
@endsection
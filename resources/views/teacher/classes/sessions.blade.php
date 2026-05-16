@extends('teacher.layouts.master-layouts-noleft')
@section('title', 'My Sessions')

@section('content')

    @include('components.alerts')

    <div class="portal-page-header">
        <div class="d-flex align-items-center">
            <a href="javascript:window.history.back();" class="btn btn-sm btn-light border-0 shadow-sm me-3 rounded-circle"
                title="Go Back"
                style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-chevron-left"></i>
            </a>
            <h4 class="m-0 fw-bold text-dark">My Sessions</h4>
        </div>
    </div>

    <div class="portal-card">

        <div class="portal-card-header">
            <h4>Sessions Search & Filters</h4>
            <span class="portal-badge portal-badge-primary">Total Sessions: {{ $sessions->total() }}</span>
        </div>

        <div class="portal-card-body">

            {{-- ── Filters ── --}}
            <form method="GET" class="row g-3 mb-4">

                <div class="col-md-3">
                    <label class="portal-label">Status</label>
                    <select name="filter" class="portal-select">
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
                    <label class="portal-label">From Date</label>
                    <input type="date" name="date_from" class="portal-input" value="{{ request('date_from') }}">
                </div>

                <div class="col-md-3">
                    <label class="portal-label">To Date</label>
                    <input type="date" name="date_to" class="portal-input" value="{{ request('date_to') }}">
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button class="portal-btn portal-btn-primary w-100 justify-content-center">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('teacher.sessions.index') }}" class="portal-btn btn-light text-nowrap">
                        <i class="fas fa-undo"></i> Reset
                    </a>
                </div>

            </form>

            {{-- ── Table ── --}}
            <div class="table-responsive">
                <table class="portal-table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Join Time</th>
                            <th>Class</th>
                            <th>Duration</th>
                            <th>Hourly Wage</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>

                        @forelse($sessions as $session)
                            <tr>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <div>
                                            <small class="text-uppercase fw-bold text-muted font-size-10">Created:</small>
                                            <span
                                                class="text-dark fw-semibold d-block">{{ \Carbon\Carbon::parse($session->created_at)->format('d M Y') }}</span>
                                            <small
                                                class="text-primary fw-medium">{{ \Carbon\Carbon::parse($session->created_at)->format('h:i A') }}</small>
                                        </div>
                                        <div class="border-top pt-1 mt-1" style="border-color: rgba(0,0,0,0.05) !important;">
                                            <small class="text-uppercase fw-bold text-muted font-size-10">Updated:</small>
                                            <span
                                                class="text-dark fw-semibold d-block">{{ \Carbon\Carbon::parse($session->link_updated_at)->format('d M Y') }}</span>
                                            <small
                                                class="text-primary fw-medium">{{ \Carbon\Carbon::parse($session->link_updated_at)->format('h:i A') }}</small>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    @if($session->join_teacher_at)
                                        <div class="d-flex flex-column gap-1">
                                            <div>
                                                <small class="text-uppercase fw-bold text-muted font-size-10">Joined:</small>
                                                <span
                                                    class="text-dark fw-semibold d-block">{{ \Carbon\Carbon::parse($session->join_teacher_at)->format('d M Y') }}</span>
                                                <small
                                                    class="text-primary fw-medium">{{ \Carbon\Carbon::parse($session->join_teacher_at)->format('h:i A') }}</small>
                                            </div>
                                            @if($session->completed_at)
                                                <div class="border-top pt-1 mt-1" style="border-color: rgba(0,0,0,0.05) !important;">
                                                    <small class="text-uppercase fw-bold text-muted font-size-10">Completed:</small>
                                                    <span
                                                        class="text-dark fw-semibold d-block">{{ \Carbon\Carbon::parse($session->completed_at)->format('d M Y') }}</span>
                                                    <small
                                                        class="text-primary fw-medium">{{ \Carbon\Carbon::parse($session->completed_at)->format('h:i A') }}</small>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="d-flex flex-column">
                                        <span
                                            class="fw-bold text-muted font-size-12">{{ optional($session->classRoom->course)->name ?? '-' }}</span>
                                        <span class="text-dark font-size-14 mt-1"><a
                                                href="{{ route('teacher.classes.show', encrypt($session->classRoom->id)) }}">{{ $session->classRoom->name ?? '-' }}</a></span>
                                    </div>
                                </td>

                                <td>
                                    <span class="fw-semibold text-dark">
                                        @if($session->duration)
                                            {{ floor($session->duration / 60) }}h
                                            {{ $session->duration % 60 > 0 ? ($session->duration % 60) . 'm' : '' }}
                                        @else
                                            -
                                        @endif
                                    </span>
                                </td>

                                <td>
                                    <span class="fw-bold text-success">
                                        {{ $session->hourly_wage ? '₹' . $session->hourly_wage : '-' }}
                                    </span>
                                </td>

                                <td>
                                    @if($session->status == 'completed')
                                        <span class="portal-badge portal-badge-success">Completed</span>
                                    @else
                                        <span class="portal-badge portal-badge-warning">Pending</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="d-flex gap-2">
                                        @if($session->google_meet_link && $session->status == 'pending')
                                            @if(\Carbon\Carbon::parse($session->link_updated_at)->isToday())
                                                <a href="{{ $session->google_meet_link }}" target="_blank" rel="noopener"
                                                    class="portal-btn portal-btn-primary"
                                                    onclick="fetch('{{ route('teacher.class-hours.join', encrypt($session->id)) }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })">
                                                    <i class="fas fa-video"></i> Join
                                                </a>
                                            @else
                                                <!-- <button class="portal-btn btn-light text-nowrap" disabled
                                                                                                                                                                                                                    title="Google Meet link must be updated today to join.">
                                                                                                                                                                                                                    <i class="fas fa-video text-muted"></i> Join
                                                                                                                                                                                                                </button> -->
                                            @endif
                                        @endif

                                        @if($session->status == 'pending')
                                            <button class="portal-btn editClassHour text-white" data-id="{{ $session->id }}"
                                                data-link="{{ $session->google_meet_link }}"
                                                style="background-color: #f59e0b !important;">
                                                <i class="fas fa-edit"></i> Link
                                            </button>
                                        @else
                                            <button class="portal-btn btn-light" disabled>
                                                <i class="fas fa-lock text-muted"></i> Locked
                                            </button>
                                        @endif
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-0">
                                    <div class="portal-empty-state">
                                        <i class="fas fa-history portal-empty-state-icon"></i>
                                        <div class="portal-empty-state-title">No Sessions Found</div>
                                        <p class="text-muted small m-0">No teaching sessions match your filtering criteria.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $sessions->links() }}
            </div>

        </div>
    </div>

    {{-- Edit Session Modal --}}
    <div class="modal fade" id="editClassHourModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <form method="POST" id="editClassHourForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="fw-bold text-dark mb-0">Update Google Meet Link</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body py-4">
                        <div class="mb-3">
                            <label class="portal-label">Google Meet Link</label>
                            <input type="url" name="google_meet_link" id="edit_meet_link" class="portal-input"
                                placeholder="https://meet.google.com/..." required>
                            <span class="form-text text-danger font-size-12">Click Update Button to activate the join
                                button.</span>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button class="portal-btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
                        <button class="portal-btn portal-btn-primary" type="submit"
                            onclick="this.disabled=true; this.innerText='Updating...'; this.form.submit();">Update
                            Link</button>
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
@extends($routePrefix === 'admin' ? 'admin.layouts.master' : 'staff.layouts.master')

@section('title', 'Holidays & Alerts')

@section('content')

    @component('admin.breadcrumbs.breadcrumb')
    @slot('li_1') Administration @endslot
    @slot('li_2') Holidayss & Alerts @endslot
    @slot('title') Holiday & Alert Notifications @endslot
    @endcomponent

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">Holiday & Alert Notifications</h4>
                        <a href="{{ route($routePrefix . '.holidays.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i> Announce Holiday/Alert
                        </a>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th>Holiday/Alert Date</th>
                                    <th>Description</th>
                                    <th>Target Group</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($holidays as $holiday)
                                    <tr>
                                        <td>{{ $loop->iteration + ($holidays->currentPage() - 1) * $holidays->perPage() }}</td>
                                        <td class="fw-semibold">{{ $holiday->title }}</td>
                                        <td>
                                            <span class="badge bg-soft-info text-info font-size-12">
                                                {{ $holiday->date->format('d M Y') }}
                                            </span>
                                            @if($holiday->date->isPast())
                                                <span class="badge bg-soft-secondary text-secondary font-size-11 ms-1">Past</span>
                                            @else
                                                <span class="badge bg-soft-success text-success font-size-11 ms-1">Upcoming</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span title="{{ $holiday->description }}">
                                                {{ Str::limit($holiday->description, 50) ?: '-' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($holiday->target_type == 'all_teachers')
                                                <span class="badge bg-primary">All Teachers</span>
                                            @elseif($holiday->target_type == 'selected_teachers')
                                                <span class="badge bg-info">Selected Teachers
                                                    ({{ $holiday->teachers->count() }})</span>
                                            @elseif($holiday->target_type == 'all_students')
                                                <span class="badge bg-success">All Students</span>
                                            @elseif($holiday->target_type == 'selected_students')
                                                <span class="badge bg-warning text-dark">Selected Students
                                                    ({{ $holiday->students->count() }})</span>
                                            @elseif($holiday->target_type == 'classes')
                                                <span class="badge bg-secondary">
                                                    Classes ({{ $holiday->classRooms->count() }})
                                                    - For {{ ucfirst($holiday->class_target_type) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>{{ $holiday->created_at->format('d M Y, h:i A') }}</td>
                                        <td>
                                            <form action="{{ route($routePrefix . '.holidays.destroy', $holiday->id) }}"
                                                method="POST" class="d-inline-block"
                                                onsubmit="return confirm('Are you sure you want to delete this holiday notification?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    title="Delete Announcement">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            No holiday notifications announced yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $holidays->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
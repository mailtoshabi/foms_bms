@extends('admin.layouts.master')

@section('title', 'Homework')

@section('css')
    <link href="{{ URL::asset('assets/libs/select2/select2.min.css') }}" rel="stylesheet">
@endsection

@section('content')

    @component('admin.breadcrumbs.breadcrumb')
        @slot('li_1') Academics @endslot
        @slot('li_2') Homework @endslot
        @slot('title') Homework @endslot
    @endcomponent

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Homework Assignments ({{ $homeworks->total() }})</h4>
        </div>
        <div class="card-body">
            
            {{-- Filter Form --}}
            <form method="GET" action="{{ route('admin.homeworks.index') }}" class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Homework Title</label>
                    <input type="text" name="title" value="{{ request('title') }}" class="form-control" placeholder="Search by title...">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Class Room</label>
                    <select name="class_room_id" class="form-control select2">
                        <option value="">All Classes</option>
                        @foreach($classRooms as $class)
                            <option value="{{ $class->id }}" {{ request('class_room_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Teacher</label>
                    <select name="teacher_id" class="form-control select2">
                        <option value="">All Teachers</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                {{ $teacher->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.homeworks.index') }}" class="btn btn-light w-100">
                        <i class="fas fa-undo me-1"></i> Reset
                    </a>
                </div>
            </form>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-bordered align-middle table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Class Room</th>
                            <th>Teacher</th>
                            <th>Files</th>
                            <th>Submissions</th>
                            <th>Created Date</th>
                            <th width="150" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($homeworks as $hw)
                            <tr>
                                <td class="fw-bold text-dark">{{ $hw->title }}</td>
                                <td>{{ $hw->classRoom?->name ?? 'N/A' }}</td>
                                <td>{{ $hw->teacher?->name ?? 'N/A' }}</td>
                                <td>
                                    @if($hw->files->count() > 0)
                                        <span class="badge bg-soft-info text-info fs-7">
                                            <i class="fas fa-paperclip me-1"></i>
                                            {{ $hw->files->count() }} file(s)
                                        </span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-soft-primary text-primary fs-7">
                                        {{ $hw->submissions->count() }} submission(s)
                                    </span>
                                </td>
                                <td>
                                    {{ $hw->created_at->format('d M Y h:i A') }}
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('admin.homeworks.show', encrypt($hw->id)) }}" class="btn btn-sm btn-soft-info" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <form action="{{ route('admin.homeworks.destroy', encrypt($hw->id)) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this homework and all student submissions for it?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-soft-danger" title="Delete Homework">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No homework assignments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-3">
                {{ $homeworks->links() }}
            </div>

        </div>
    </div>

@endsection

@section('script')
    <script src="{{ URL::asset('assets/libs/select2/select2.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
@endsection

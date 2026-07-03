@extends('admin.layouts.master')

@section('title', 'Class Notes')

@section('css')
    <link href="{{ URL::asset('assets/libs/select2/select2.min.css') }}" rel="stylesheet">
@endsection

@section('content')

    @component('admin.breadcrumbs.breadcrumb')
        @slot('li_1') Academics @endslot
        @slot('li_2') Class Notes @endslot
        @slot('title') Class Notes @endslot
    @endcomponent

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Class Notes ({{ $notes->total() }})</h4>
        </div>
        <div class="card-body">
            
            {{-- Filter Form --}}
            <form method="GET" action="{{ route('admin.class-notes.index') }}" class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Note Title</label>
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
                    <a href="{{ route('admin.class-notes.index') }}" class="btn btn-light w-100">
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
                            <th>Created Date</th>
                            <th width="150" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($notes as $note)
                            <tr>
                                <td class="fw-bold text-dark">{{ $note->title }}</td>
                                <td>{{ $note->classRoom?->name ?? 'N/A' }}</td>
                                <td>{{ $note->teacher?->name ?? 'N/A' }}</td>
                                <td>
                                    @if($note->files->count() > 0)
                                        <span class="badge bg-soft-info text-info fs-7">
                                            <i class="fas fa-paperclip me-1"></i>
                                            {{ $note->files->count() }} file(s)
                                        </span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $note->created_at->format('d M Y h:i A') }}
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('admin.class-notes.show', encrypt($note->id)) }}" class="btn btn-sm btn-soft-info" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <form action="{{ route('admin.class-notes.destroy', encrypt($note->id)) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this class note?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-soft-danger" title="Delete Note">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No class notes found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-3">
                {{ $notes->links() }}
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

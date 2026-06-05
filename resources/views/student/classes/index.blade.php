@extends('student.layouts.master-layouts-noleft')

@section('title', 'My Classes')

@section('content')
    <div class="portal-page-header">
        <div class="d-flex align-items-center">
            <a href="javascript:window.history.back();" class="btn btn-sm btn-light border-0 shadow-sm me-3 rounded-circle" title="Go Back" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-chevron-left"></i>
            </a>
            <h4 class="m-0 fw-bold text-dark">My Classes</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if($classes->isEmpty())
                        <p class="text-muted">No classes assigned yet.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered  align-middle table-nowrap mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Class Name</th>
                                        <th>Course</th>
                                        <th>Teacher</th>
                                        <th>Schedule</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($classes as $class)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $class->name }} <br><small
                                                    class="badge bg-soft-primary text-primary">{{ ucwords($class->classType->name ?? '-') }}
                                                    Class</small></td>
                                            <td>{{ $class->course->name ?? '-' }}</td>
                                            <td>{{ $class->teachers->pluck('name')->join(', ') ?: '-' }}</td>
                                            <td>
                                                {{ implode(', ', $class->selected_days ?? []) }}
                                                <small>
                                                    <br>

                                                    {{ \Carbon\Carbon::createFromFormat('H:i', $class->time_slot)->format('h:i A') ?? '' }}

                                                </small>
                                            </td>
                                            <td>
                                                {{-- <a href="{{ route('student.classes.show', encrypt($class->id)) }}"
                                                    class="btn btn-sm btn-info">View</a> --}}
                                                <a href="{{ route('student.classes.show', encrypt($class->id)) }}"
                                                    class="btn btn-sm btn-primary mt-2">
                                                    <i class="fas fa-sign-in-alt"></i> Enter
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
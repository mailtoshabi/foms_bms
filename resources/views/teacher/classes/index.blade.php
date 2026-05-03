@extends('teacher.layouts.master')

@section('title', 'Assigned Classes')

@section('content')

    <div class="card">

        <div class="card-header">
            <h4>Assigned Classes</h4>
        </div>

        <div class="card-body table-responsive">

            <table class="table table-bordered">

                <thead>

                    <tr>
                        <th>Course</th>
                        <th>Class</th>
                        <th>Type</th>
                        <th>Schedule</th>
                        <th>Action</th>
                    </tr>

                </thead>

                <tbody>

                    @forelse($classes as $class)

                        <tr>

                            <td>{{ $class->course->name ?? '-' }}</td>

                            <td>
                                {{ $class->name }}
                                @if($class->is_completed)
                                    <span class="badge bg-success">Completed</span>
                                @endif
                            </td>

                            <td>{{ ucfirst($class->classType->name ?? '-') }}</td>

                            <td>

                                {{ implode(', ', $class->selected_days ?? []) }}
                                <small>
                                    <br>

                                    {{ \Carbon\Carbon::createFromFormat('H:i', $class->time_slot)->format('h:i A') ?? '' }}

                                </small>

                            </td>

                            <td>

                                <a href="{{ route('teacher.classes.show', encrypt($class->id)) }}"
                                    class="btn btn-sm btn-primary">
                                    <i class="fas fa-sign-in-alt me-1"></i> Enter
                                </a>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="text-center">
                                No Classes Assigned
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

            {{ $classes->links() }}

        </div>

    </div>

@endsection

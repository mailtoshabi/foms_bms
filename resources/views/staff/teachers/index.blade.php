@extends('staff.layouts.master')
@section('title', 'Teachers')

@section('content')

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">

        <div class="card-header d-flex justify-content-between align-items-center">

            <h4 class="mb-0">
                <a href="javascript:window.history.back();"
                    class="btn btn-sm btn-light border-0 shadow-sm me-2 rounded-circle" title="Go Back">
                    <i class="fas fa-chevron-left"></i>
                </a>
                Teachers ({{ $teachers->total() }})
            </h4>

            <a href="{{ route('staff.teachers.create') }}" class="btn btn-primary">
                Add Teacher
            </a>

        </div>


        <div class="card-body table-responsive">

            <form method="GET" class="row mb-3">

                <div class="col-md-4">
                    <label class="form-label fw-bold">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                        placeholder="Search name or contact">

                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Status</label>
                    <select name="status" class="form-control select2">

                        <option value="">All Status</option>

                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>
                            Active
                        </option>

                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>
                            Inactive
                        </option>

                    </select>

                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">

                    <button class="btn btn-primary">
                        Filter
                    </button>

                    <a href="{{ route('staff.teachers.index') }}" class="btn btn-light">
                        Reset
                    </a>

                </div>

            </form>


            <table class="table table-bordered align-middle">

                <thead>

                    <tr>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Salary Day</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>

                </thead>

                <tbody>

                    @forelse($teachers as $teacher)
                        @php
                            $rank = teacherRankData($teacher->id);
                        @endphp
                        <tr>

                            <td>
                                {{ $teacher->name }}
                                <br>

                                @isset($teacher->dob)
                                    <small>DOB: {{ $teacher->dob_formatted }}</small>
                                @endisset

                            </td>

                            <td>{{ $teacher->formatted_contact_number }}
                                @if($teacher->is_whatsapp_different)
                                    <br><small class="text-success"><i class="mdi mdi-whatsapp"></i>
                                        {{ $teacher->formatted_whatsapp_number }}</small>
                                @endif
                            </td>

                            <td>{{ $teacher->email ?? '-' }}</td>

                            <td>
                                @if($teacher->salary_cycle_day)
                                    <small class="text-muted d-block">Cycle:
                                        {{ $teacher->salary_cycle_day }}{{ in_array($teacher->salary_cycle_day, [1, 21, 31]) ? 'st' : (in_array($teacher->salary_cycle_day, [2, 22]) ? 'nd' : (in_array($teacher->salary_cycle_day, [3, 23]) ? 'rd' : 'th')) }}
                                        of month</small>
                                    <small class="text-success d-block">Credit: {{ $teacher->salary_credit_date }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <td>

                                <span
                                    class="badge {{ $teacher->status == 'active' ? 'bg-success' : '' }} {{ $teacher->status == 'inactive' ? 'bg-danger' : '' }}">
                                    {{ ucfirst($teacher->status) }}
                                </span>

                                <span class="badge bg-{{ $rank['color'] }}  px-3 ">{{ $rank['label'] }}</span><br>
                                @for($s = 1; $s <= 5; $s++)
                                    <span
                                        style="font-size:1rem; color: {{ $s <= $rank['stars'] ? '#f1c40f' : '#ccc' }}">&#9733;</span>
                                @endfor
                                <small class="text-muted">Score: {{ $rank['score'] }}</small>
                            </td>


                            <td>

                                @php
                                    $operationRoleId = utility('id_operation_dept');
                                    $staff = auth('staff')->user();
                                @endphp
                                <div class="d-flex gap-2">

                                    <a href="{{ route('staff.teachers.show', encrypt($teacher->id)) }}">
                                        <i class="mdi mdi-eye text-primary"></i>
                                    </a>

                                    <a href="{{ route('staff.teachers.edit', encrypt($teacher->id)) }}">
                                        <i class="mdi mdi-pencil text-success"></i>
                                    </a>
                                    @if($staff->hasRoleId($operationRoleId))
                                        <a href="#" data-plugin="delete-data" data-target-form="#delete_{{ $teacher->id }}">
                                            <i class="mdi mdi-trash-can text-danger"></i>
                                        </a>

                                        <form id="delete_{{ $teacher->id }}" method="POST"
                                            action="{{ route('staff.teachers.destroy', encrypt($teacher->id)) }}">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    @endif

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="text-center">No Teachers Found</td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

            {{ $teachers->links() }}

        </div>
    </div>

@endsection


@section('script')
    <script>
        $('.select2').select2();
    </script>
@endsection
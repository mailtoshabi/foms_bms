@extends('staff.layouts.master')
@section('title', 'Teacher Leads')

@section('content')

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h4>Teacher Leads ({{ $leads->total() }})</h4>

            <a href="{{ route('staff.teacher-leads.create') }}" class="btn btn-primary">
                Add Lead
            </a>

        </div>

        <div class="card-body table-responsive">

            <form method="GET" class="row mb-3">

                <div class="col-md-4">
                    <label class="form-label fw-bold">Status</label>
                    <select name="status" class="form-control select2">
                        <option value="">All Status</option>

                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                            Pending
                        </option>

                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>
                            Approved
                        </option>

                        <option value="not_interested" {{ request('status') == 'not_interested' ? 'selected' : '' }}>
                            Not Interested
                        </option>

                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Date</label>
                    <input type="date" name="date" value="{{ request('date') }}" class="form-control" placeholder="Date">
                </div>

                <div class="col-md-4 d-flex align-items-end gap-2">

                    <button class="btn btn-primary">
                        Filter
                    </button>

                    <a href="{{ route('staff.teacher-leads.index') }}" class="btn btn-light">
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
                        <th>Source</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($leads as $lead)

                        <tr>

                            <td>{{ $lead->name }}</td>
                            <td>{{ $lead->formatted_contact_number }}
                                @if($lead->is_whatsapp_different)
                                    <br><small class="text-success"><i class="mdi mdi-whatsapp"></i>
                                        {{ $lead->formatted_whatsapp_number }}</small>
                                @endif
                            </td>
                            <td>{{ $lead->email ?? '-' }}</td>
                            <td>{{ $lead->source->name ?? '-' }}</td>

                            <td>

                                <span class="badge
                                                        {{ $lead->status == 'pending' ? 'bg-warning' : '' }}
                                                        {{ $lead->status == 'approved' ? 'bg-success' : '' }}
                                                        {{ $lead->status == 'not_interested' ? 'bg-danger' : '' }}">

                                    {{ ucfirst(str_replace('_', ' ', $lead->status)) }}

                                </span>

                            </td>

                            <td>
                                @php
                                    $operationRoleId = utility('id_operation_dept');
                                    $staff = auth('staff')->user();
                                @endphp
                                <div class="d-flex gap-2">

                                    <a href="{{ route('staff.teacher-leads.edit', encrypt($lead->id)) }}">
                                        <i class="mdi mdi-pencil text-success"></i>
                                    </a>

                                    @if($staff->hasRoleId($operationRoleId))
                                        <a href="#" data-plugin="delete-data" data-target-form="#delete_{{ $lead->id }}">
                                            <i class="mdi mdi-trash-can text-danger"></i>
                                        </a>

                                        <form id="delete_{{ $lead->id }}" method="POST"
                                            action="{{ route('staff.teacher-leads.destroy', encrypt($lead->id)) }}">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    @endif
                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6" class="text-center">No Leads Found</td>
                        </tr>

                    @endforelse

                </tbody>
            </table>

            {{ $leads->links() }}

        </div>
    </div>

@endsection

@section('script')
    <script>
        $('.select2').select2();
    </script>
@endsection

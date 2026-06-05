@extends('staff.layouts.master')
@section('title', 'Student Leads')

@section('content')

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h4>Student Leads ({{ $leads->total() }})</h4>
            <a href="{{ route('staff.student-leads.create') }}" class="btn btn-primary">Add Lead</a>
        </div>

        <div class="card-body table-responsive">

            <form method="GET" class="row mb-3">

                <div class="col-md-3">
                    <label class="form-label fw-bold">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Name or contact">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Status</label>
                    <select name="status" class="form-control select2">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="follow_up" {{ request('status') == 'follow_up' ? 'selected' : '' }}>Follow Up</option>
                        <option value="no_response" {{ request('status') == 'no_response' ? 'selected' : '' }}>No Response
                        </option>
                        <option value="not_interested" {{ request('status') == 'not_interested' ? 'selected' : '' }}>Not
                            Interested</option>
                        <option value="interested" {{ request('status') == 'interested' ? 'selected' : '' }}>Interested
                        </option>
                        <option value="converted" {{ request('status') == 'converted' ? 'selected' : '' }}>Converted</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-bold">Source</label>
                    <select name="source_id" class="form-control select2">
                        <option value="">All Sources</option>
                        @foreach($sources as $source)
                            <option value="{{ $source->id }}" {{ request('source_id') == $source->id ? 'selected' : '' }}>
                                {{ $source->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-bold">Date</label>
                    <input type="date" name="date" value="{{ request('date') }}" class="form-control" placeholder="Date">
                </div>

                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button class="btn btn-primary">Filter</button>
                    <a href="{{ route('staff.student-leads.index') }}" class="btn btn-light">Reset</a>
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
                            <td><a href="{{ route('staff.student-leads.edit', encrypt($lead->id)) }}">{{ $lead->name }}</a></td>
                            <td>{{ $lead->formatted_contact_number }}
                                @if($lead->is_whatsapp_different)
                                    <br><small class="text-success"><i class="mdi mdi-whatsapp"></i>
                                        {{ $lead->formatted_whatsapp_number }}</small>
                                @endif
                            </td>
                            <td>{{ $lead->email ?? '-' }}</td>
                            <td>{{ $lead->source->name ?? '-' }}</td>
                            <td>
                                <span
                                    class="badge
                                                                        {{ $lead->status == 'pending' ? 'bg-warning' : '' }}
                                                                        {{ $lead->status == 'follow_up' ? 'bg-info' : '' }}
                                                                        {{ $lead->status == 'no_response' ? 'bg-secondary' : '' }}
                                                                        {{ $lead->status == 'not_interested' ? 'bg-danger' : '' }}
                                                                        {{ $lead->status == 'interested' ? 'bg-success' : '' }}
                                                                        {{ $lead->status == 'converted' ? 'bg-primary' : '' }}">

                                    {{ ucfirst(str_replace('_', ' ', $lead->status)) }}

                                </span>
                            </td>
                            <td>
                                @php
                                    $operationRoleId = utility('id_operation_dept');
                                    $staff = auth('staff')->user();
                                @endphp
                                <div class="d-flex gap-2">

                                    <a href="#" class="viewLeadNotes" data-name="{{ $lead->name }}" data-notes="{{ json_encode($lead->notes->values()->all()) }}" title="View Notes">
                                        <i class="mdi mdi-note-text-outline text-info"></i>
                                    </a>

                                    <a href="{{ route('staff.student-leads.edit', encrypt($lead->id)) }}">
                                        <i class="mdi mdi-pencil text-success"></i>
                                    </a>

                                    @if($staff->hasRoleId($operationRoleId))
                                        <a href="#" data-plugin="delete-data" data-target-form="#delete_{{ $lead->id }}">
                                            <i class="mdi mdi-trash-can text-danger"></i>
                                        </a>

                                        <form id="delete_{{ $lead->id }}" method="POST"
                                            action="{{ route('staff.student-leads.destroy', encrypt($lead->id)) }}">
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

            {{ $leads->appends(request()->query())->links() }}

        </div>
    </div>

    <x-lead_notes_modal />

@endsection

@section('script')
    <script>
        $('.select2').select2();
    </script>
@endsection
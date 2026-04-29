@extends('admin.layouts.master')
@section('title', 'Student Leads Report')

@section('content')

    <div class="card mb-3">
        <div class="card-body p-2">
            <ul class="nav nav-pills">

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.reports.student-leads') ? 'active' : '' }}"
                        href="{{ route('admin.reports.student-leads') }}">
                        Student Leads
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.reports.students') ? 'active' : '' }}"
                        href="{{ route('admin.reports.students') }}">
                        Students
                    </a>
                </li>

            </ul>
        </div>
    </div>

    <div class="card">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <a href="javascript:window.history.back();"
                    class="btn btn-sm btn-light border-0 shadow-sm me-2 rounded-circle" title="Go Back">
                    <i class="fas fa-chevron-left"></i>
                </a>
                Student Leads Report
            </h4>
        </div>



        <div class="card-body table-responsive">

            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="card p-3">Total Leads: {{ $totalLeads }}</div>
                </div>
                <div class="col-md-4">
                    <div class="card p-3">Converted: {{ $convertedLeads }}</div>
                </div>
                <div class="col-md-4">
                    <div class="card p-3">Pending: {{ $pendingLeads }}</div>
                </div>
            </div>

            <form method="GET" class="row mb-3 align-items-end">

                <div class="col-md-2">
                    <label class="form-label fw-bold">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-bold">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-bold">Status</label>
                    <select name="status" class="form-control">
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
                    <button class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.reports.student-leads') }}" class="btn btn-secondary">Reset</a>
                </div>

                <div class="col-md-2 text-end">
                    <a href="{{ route('admin.reports.student-leads.export', request()->all()) }}" class="btn btn-success">
                        Export
                    </a>
                </div>

            </form>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Source</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leads as $lead)
                        <tr>
                            <td>{{ $lead->name }}</td>
                            <td>{{ $lead->formatted_contact_number }}
                                @if($lead->is_whatsapp_different)
                                    <br><small class="text-success"><i class="mdi mdi-whatsapp"></i>
                                        {{ $lead->formatted_whatsapp_number }}</small>
                                @endif
                            </td>
                            <td>{{ $lead->source->name ?? '-' }}</td>
                            <td>{{ ucfirst($lead->status) }}</td>
                            <td>{{ $lead->created_at->format('d M Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $leads->links() }}

        </div>
    </div>

@endsection
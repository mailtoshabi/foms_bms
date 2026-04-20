@extends('admin.layouts.master')
@section('title','Teacher Lead Notes')

@section('content')

<div class="card mb-3">
    <div class="card-body p-2">
        <ul class="nav nav-pills">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.reports.teacher-leads') ? 'active' : '' }}"
                    href="{{ route('admin.reports.teacher-leads') }}">
                    Leads
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.reports.teacher-lead-notes') ? 'active' : '' }}"
                    href="{{ route('admin.reports.teacher-lead-notes') }}">
                    Lead Notes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.reports.teachers') ? 'active' : '' }}"
                    href="{{ route('admin.reports.teachers') }}">
                    Teachers
                </a>
            </li>
        </ul>
    </div>
</div>

<div class="card">

    <div class="card-header">
        <h4>Teacher Lead Notes</h4>
    </div>

    <div class="card-body table-responsive">

        <form method="GET" class="row mb-3 g-2">

            <div class="col-md-3">
                <input type="text" name="search" class="form-control"
                    placeholder="Search lead name or contact"
                    value="{{ request('search') }}">
            </div>

            <div class="col-md-2">
                <select name="status" class="form-control">
                    <option value="">All Status</option>
                    <option value="pending"           {{ request('status') === 'pending'           ? 'selected' : '' }}>Pending</option>
                    <option value="follow_up"         {{ request('status') === 'follow_up'         ? 'selected' : '' }}>Follow Up</option>
                    <option value="no_response"       {{ request('status') === 'no_response'       ? 'selected' : '' }}>No Response</option>
                    <option value="not_interested"    {{ request('status') === 'not_interested'    ? 'selected' : '' }}>Not Interested</option>
                    <option value="interested"        {{ request('status') === 'interested'        ? 'selected' : '' }}>Interested</option>
                    <option value="converted"         {{ request('status') === 'converted'         ? 'selected' : '' }}>Converted</option>
                </select>
            </div>

            <div class="col-md-2">
                <input type="date" name="from_date" class="form-control"
                    value="{{ request('from_date') }}">
            </div>

            <div class="col-md-2">
                <input type="date" name="to_date" class="form-control"
                    value="{{ request('to_date') }}">
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary">Filter</button>
                <a href="{{ route('admin.reports.teacher-lead-notes') }}" class="btn btn-light">Reset</a>
            </div>

        </form>

        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Lead</th>
                    <th>Note</th>
                    <th>Status</th>
                    <th>Staff</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                    <tr>
                        <td>{{ $data->firstItem() + $loop->index }}</td>
                        <td>
                            {{ $row->lead->name ?? '—' }}
                            <br><small class="text-muted">{{ $row->lead->formatted_contact_number ?? '' }}</small>
                            @if($row->lead->is_whatsapp_different)
                                <br><small class="text-success" style="font-size: 10px;">WA: {{ $row->lead->formatted_whatsapp_number }}</small>
                            @endif
                        </td>
                        <td>{{ $row->note }}</td>
                        <td>
                            @php
                                $badges = [
                                    'pending'        => 'secondary',
                                    'follow_up'      => 'info',
                                    'no_response'    => 'warning',
                                    'not_interested' => 'danger',
                                    'interested'     => 'primary',
                                    'converted'      => 'success',
                                ];
                                $color = $badges[$row->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $color }}">
                                {{ ucfirst(str_replace('_', ' ', $row->status)) }}
                            </span>
                        </td>
                        <td>{{ $row->staff->name ?? '—' }}</td>
                        <td>{{ $row->created_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No records found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $data->links() }}

    </div>
</div>

@endsection

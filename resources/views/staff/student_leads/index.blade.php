@extends('staff.layouts.master')
@section('title','Student Leads')

@section('content')

<div class="card">
<div class="card-header d-flex justify-content-between">
<h4>Student Leads ({{ $leads->total() }})</h4>
<a href="{{ route('staff.student-leads.create') }}" class="btn btn-primary">Add Lead</a>
</div>

<div class="card-body table-responsive">

<form method="GET" class="row mb-3">

<div class="col-md-3">
    <select name="status" class="form-control select2">
        <option value="">All Status</option>
        <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
        <option value="admitted" {{ request('status')=='admitted'?'selected':'' }}>Admitted</option>
    </select>
</div>

<div class="col-md-3">
    <select name="source_id" class="form-control select2">
        <option value="">All Sources</option>
        @foreach($sources as $source)
            <option value="{{ $source->id }}"
                {{ request('source_id') == $source->id ? 'selected' : '' }}>
                {{ $source->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="col-md-3">
    <input type="date"
           name="date"
           value="{{ request('date') }}"
           class="form-control">
</div>

<div class="col-md-3 d-flex gap-2">
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
<td>{{ $lead->name }}</td>
<td>{{ $lead->contact_number }}</td>
<td>{{ $lead->email ?? '-' }}</td>
<td>{{ $lead->source->name ?? '-' }}</td>
<td>
<span class="badge {{ $lead->status=='pending'?'bg-warning':'bg-success' }}">
{{ ucfirst($lead->status) }}
</span>
</td>
<td>
<div class="d-flex gap-2">

<a href="{{ route('staff.student-leads.edit',encrypt($lead->id)) }}">
<i class="mdi mdi-pencil text-success"></i>
</a>

<a href="#"
data-plugin="delete-data"
data-target-form="#delete_{{ $lead->id }}">
<i class="mdi mdi-trash-can text-danger"></i>
</a>

<form id="delete_{{ $lead->id }}"
method="POST"
action="{{ route('staff.student-leads.destroy',encrypt($lead->id)) }}">
@csrf
@method('DELETE')
</form>

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

@endsection

@section('script')
<script>
$('.select2').select2();
</script>
@endsection

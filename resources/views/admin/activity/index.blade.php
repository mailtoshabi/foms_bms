@extends('admin.layouts.master')
@section('title','Activity Logs')

@section('content')
<div class="card">
<div class="card-header"><h4>Activity Logs</h4></div>
<div class="card-body table-responsive">

<table class="table table-bordered">
<thead>
<tr>
    <th>User</th>
    <th>Action</th>
    <th>Description</th>
    <th>Module</th>
    <th>IP</th>
    <th>Time</th>
</tr>
</thead>
<tbody>
@foreach($logs as $log)
<tr>
    <td>{{ $log->user->name ?? 'System' }}</td>
    <td>{{ $log->action }}</td>
    <td>{{ $log->description }}</td>
    <td>{{ $log->module }}</td>
    <td>{{ $log->ip_address }}</td>
    <td>{{ $log->created_at->format('d M Y H:i') }}</td>
</tr>
@endforeach
</tbody>
</table>

{{ $logs->links() }}

</div>
</div>
@endsection

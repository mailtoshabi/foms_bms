@extends('teacher.layouts.master')

@section('title','Assigned Classes')

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

<td>{{ $class->name }}</td>

<td>{{ $class->classType->name }}</td>

<td>

{{ implode(', ',$class->selected_days ?? []) }}

<br>

{{ $class->time_slot }}

</td>

<td>

<a href="{{ route('teacher.classes.show',$class->id) }}"
class="btn btn-sm btn-primary">

View

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

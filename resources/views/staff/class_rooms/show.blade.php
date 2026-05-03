@extends('staff.layouts.master')

@section('title','Class Details')

@section('content')

<x-classrooms.show
    :class="$class"
    :teachers="$teachers"
    assignTeacherRoute="{{ route('staff.class_rooms.assign.teacher') }}"
    assignStudentRoute="{{ route('staff.class_rooms.assign.students') }}"
/>

@endsection

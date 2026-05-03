@extends('admin.layouts.master')

@section('title', 'Class Details')

@section('content')

    <x-classrooms.show :class="$class" :teachers="$teachers"
        assignTeacherRoute="{{ route('admin.class_rooms.assign.teacher') }}"
        assignStudentRoute="{{ route('admin.class_rooms.assign.students') }}" />

@endsection

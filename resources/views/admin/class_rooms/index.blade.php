@extends('admin.layouts.master')

@section('title')
@if(isset($class)) Edit Class @else Add Class @endif
@endsection

@section('css')
<link href="{{ URL::asset('assets/libs/select2/select2.min.css') }}" rel="stylesheet">
@endsection

@section('content')

@component('admin.breadcrumbs.breadcrumb')
@slot('li_1') Academics @endslot
@slot('li_2') Classes @endslot
@slot('title')
@if(isset($class)) Edit Class @else Add Class @endif
@endslot
@endcomponent

<x-classrooms.index
    :class_rooms="$class_rooms"
    :courses="$courses"
    :types="$types"
    createRoute="{{ route('admin.class_rooms.create') }}"
    indexRoute="{{ route('admin.class_rooms.index') }}"
    :editRoute="fn($id)=>route('admin.class_rooms.edit',$id)"
    :deleteRoute="fn($id)=>route('admin.class_rooms.destroy',$id)"
/>

@endsection

@section('script')
<script src="{{ URL::asset('assets/libs/select2/select2.min.js') }}"></script>

<script>
$('.select2').select2();
</script>
@endsection

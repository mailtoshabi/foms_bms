@extends('staff.layouts.master')

@section('title','Messages')

@section('content')

<x-message.index
    :messages="$messages"
    :createRoute="route('staff.messages.create')"
    :indexRoute="route('staff.messages.index')"
    :showRoute="fn($id)=>route('staff.messages.show', encrypt($id))"
/>

@endsection

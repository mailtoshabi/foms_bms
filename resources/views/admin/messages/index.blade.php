@extends('admin.layouts.master')

@section('title','Messages')

@section('content')

<x-message.index
    :messages="$messages"
    :createRoute="route('admin.messages.create')"
    :indexRoute="route('admin.messages.index')"
    :showRoute="fn($id)=>route('admin.messages.show', encrypt($id))"
/>

@endsection

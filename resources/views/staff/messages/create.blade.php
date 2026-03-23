@extends('staff.layouts.master')

@section('title','Create Message')

@section('content')

<x-message.create
    :users="$users"
    :currentUserId="auth('staff')->id()"
    :storeRoute="route('staff.messages.store')"
    :backRoute="route('staff.messages.index')"
/>

@endsection

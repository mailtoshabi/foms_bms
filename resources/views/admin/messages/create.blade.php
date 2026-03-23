@extends('admin.layouts.master')

@section('title','Create Message')

@section('content')

<x-message.create
    :users="$users"
    :currentUserId="auth('admin')->id()"
    :storeRoute="route('admin.messages.store')"
    :backRoute="route('admin.messages.index')"
/>

@endsection

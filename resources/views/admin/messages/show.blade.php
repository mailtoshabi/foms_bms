@extends('admin.layouts.master')

@section('title','Conversation')

@section('content')

<x-message.show
    :conversation="$conversation"
    :replies="$replies"
    currentType="admin"
    :currentId="auth('admin')->id()"
    :replyRoute="route('admin.messages.reply', encrypt($conversation->id))"
    :backRoute="route('admin.messages.index')" />

@endsection

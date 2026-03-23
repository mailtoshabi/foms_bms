@extends('staff.layouts.master')

@section('title','Conversation')

@section('content')

<x-message.show
    :conversation="$conversation"
    :replies="$replies"
    currentType="staff"
    :currentId="auth('staff')->id()"
    :replyRoute="route('staff.messages.reply', encrypt($conversation->id))"
    :backRoute="route('staff.messages.index')" />

@endsection

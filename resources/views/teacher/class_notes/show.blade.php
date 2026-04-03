@extends('teacher.layouts.master')
<x-class-notes.show
    :note="$note"
    :routePrefix="'teacher'"
    :isTeacher="true"
/>


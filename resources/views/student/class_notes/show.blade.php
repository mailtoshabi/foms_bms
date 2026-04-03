@extends('student.layouts.master-layouts-noleft')
<x-class-notes.show
    :note="$note"
    :routePrefix="'student'"
    :isTeacher="false"
/>


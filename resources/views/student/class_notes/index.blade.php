@extends('student.layouts.master-layouts-noleft')

<x-class-notes.index
    :notes="$notes"
    :routePrefix="'student'"
    :isTeacher="false"
/>

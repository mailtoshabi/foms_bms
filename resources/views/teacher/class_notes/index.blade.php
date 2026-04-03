@extends('teacher.layouts.master')

<x-class-notes.index
    :notes="$notes"
    :routePrefix="'teacher'"
    :isTeacher="true"
/>

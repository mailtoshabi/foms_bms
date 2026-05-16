@extends('teacher.layouts.master-layouts-noleft')
<x-class-notes.show :note="$note" :routePrefix="'teacher'" :isTeacher="true" />
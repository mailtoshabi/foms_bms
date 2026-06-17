@extends('admin.layouts.master')
<x-students.show
:student="$student ?? null"
:teachers="$teachers"
:attendance="$attendance"
:notes="$notes"
showButtons="true"
/>

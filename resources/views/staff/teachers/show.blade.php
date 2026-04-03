@extends('staff.layouts.master')
<x-teachers.show
:teacher="$teacher ?? null"
:notes="$notes"
:classRooms="$classRooms"
showButtons="true"
/>

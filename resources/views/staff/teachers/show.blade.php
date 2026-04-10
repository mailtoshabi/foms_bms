@extends('staff.layouts.master')
<x-teachers.show
:teacher="$teacher ?? null"
:notes="$notes"
showButtons="true"
/>

@extends('admin.layouts.master')
<x-teachers.show
:teacher="$teacher ?? null"
:notes="$notes"
showButtons="false"
/>

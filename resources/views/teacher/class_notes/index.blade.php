@extends('teacher.layouts.master-layouts-noleft')

<x-class-notes.index :notes="$notes" :routePrefix="'teacher'" :isTeacher="true" />
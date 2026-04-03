@extends('staff.layouts.master')

<x-fees.index
    :fees="$fees"
    :tab="$tab"
    :classRooms="$classRooms"
    isExport="false"
    isAction="true"
    :filterRoute="route('staff.fees.index')"
    :routeTemplateUnPaid="route('staff.fees.index', array_merge(request()->except('page'), ['tab'=>'unpaid']))"
    :routeTemplateOverdue="route('staff.fees.index', array_merge(request()->except('page'), ['tab'=>'overdue']))"
    :routeTemplatePaid="route('staff.fees.index', array_merge(request()->except('page'), ['tab'=>'paid']))"
/>

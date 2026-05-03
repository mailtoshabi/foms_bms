@extends('admin.layouts.master')

<x-fees.index :fees="$fees" :tab="$tab" :classRoomSearchUrl="$classRoomSearchUrl"
    :selectedClassName="$selectedClassName" isExport="true" isAction="false" :filterRoute="route('admin.reports.fee')"
    :routeTemplateUnPaid="route('admin.reports.fee', array_merge(request()->except('page'), ['tab' => 'unpaid']))"
    :routeTemplateOverdue="route('admin.reports.fee', array_merge(request()->except('page'), ['tab' => 'overdue']))"
    :routeTemplatePaid="route('admin.reports.fee', array_merge(request()->except('page'), ['tab' => 'paid']))"
    :totalAmount="$totalAmount" :isFiltered="$isFiltered" />

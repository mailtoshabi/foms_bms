@extends('staff.layouts.master')

@section('title')
@if(isset($class)) Edit Class @else Add Class @endif
@endsection

@section('css')
<link href="{{ URL::asset('assets/libs/select2/select2.min.css') }}" rel="stylesheet">
@endsection

@section('content')

@component('admin.breadcrumbs.breadcrumb')
@slot('li_1') Academics @endslot
@slot('li_2') Classes @endslot
@slot('title')
@if(isset($class)) Edit Class @else Add Class @endif
@endslot
@endcomponent

<x-classrooms.create
:class="$class ?? null"
:courses="$courses"
:types="$types"
formAction="{{ isset($class) ? route('staff.class_rooms.update') : route('staff.class_rooms.store') }}"
indexRoute="{{ route('staff.class_rooms.index') }}"
/>

@endsection

@section('script')
<script src="{{ URL::asset('assets/libs/select2/select2.min.js') }}"></script>

<script>
$('.select2').select2();
</script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>

flatpickr("#time_slot", {
    enableTime: true,
    noCalendar: true,
    dateFormat: "H:i",   // stored in DB
    altInput: true,
    altFormat: "h:i K"   // shown to user (AM/PM)
});

</script>


<script>

function updateClassesPerWeek() {

    let count = $('.class-day:checked').length;

    $('#classes_per_week').val(count);

}

$('.class-day').on('change', function () {

    updateClassesPerWeek();

});

$(document).ready(function () {

    updateClassesPerWeek();

});

</script>
<script>

var tooltipTriggerList = [].slice.call(
document.querySelectorAll('[data-bs-toggle="tooltip"]')
);

tooltipTriggerList.map(function (tooltipTriggerEl) {
return new bootstrap.Tooltip(tooltipTriggerEl);
});

</script>

<script>

$('#classes_per_week').on('click', function(){

let count = $('.class-day:checked').length;

if(count === 0){
    alert('Select days first');
}

});

</script>

<script>
$('select[name="class_type_id"]').on('change', function () {
    $('#admission-fee-label').text($(this).val() == '1' ? 'First Month Fee' : 'Admission Fee');
}).trigger('change');
</script>

@endsection

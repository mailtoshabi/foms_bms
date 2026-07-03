@extends($routePrefix === 'admin' ? 'admin.layouts.master' : 'staff.layouts.master')

@section('title', 'Announce Holidays & Alerts')

@section('css')
    <link href="{{ URL::asset('assets/libs/select2/select2.min.css') }}" rel="stylesheet">
@endsection

@section('content')

    @component('admin.breadcrumbs.breadcrumb')
    @slot('li_1') Administration @endslot
    @slot('li_2') Holidays & Alerts @endslot
    @slot('title') Announce Holidays/Alerts @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">New Holidays & Alerts</h4>

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route($routePrefix . '.holidays.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="title" class="form-label">Holiday/Alerts Title <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}"
                                required placeholder="e.g. Diwali Holiday, Christmas Break">
                        </div>

                        <div class="mb-3">
                            <label for="date" class="form-label">Holiday/Alert Date <span
                                    class="text-danger">*</span></label>
                            <input type="date" name="date" id="date" class="form-control"
                                value="{{ old('date', now()->format('Y-m-d')) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description / Message</label>
                            <textarea name="description" id="description" class="form-control" rows="4"
                                placeholder="Enter holiday details or instructions here...">{{ old('description') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="target_type" class="form-label">Target Group <span
                                    class="text-danger">*</span></label>
                            <select name="target_type" id="target_type" class="form-control form-select" required>
                                <option value="" disabled {{ old('target_type') === null ? 'selected' : '' }}>Select target
                                    group...</option>
                                <option value="all_teachers" {{ old('target_type') == 'all_teachers' ? 'selected' : '' }}>All
                                    Teachers</option>
                                <option value="selected_teachers" {{ old('target_type') == 'selected_teachers' ? 'selected' : '' }}>Selected Teachers</option>
                                <option value="all_students" {{ old('target_type') == 'all_students' ? 'selected' : '' }}>All
                                    Students</option>
                                <option value="selected_students" {{ old('target_type') == 'selected_students' ? 'selected' : '' }}>Selected Students</option>
                                <option value="classes" {{ old('target_type') == 'classes' ? 'selected' : '' }}>Particular
                                    Classes</option>
                            </select>
                        </div>

                        <!-- Target Teachers Selection -->
                        <div id="teacher_select_container" class="mb-3 d-none">
                            <label for="teacher_ids" class="form-label">Select Teachers <span
                                    class="text-danger">*</span></label>
                            <select name="teacher_ids[]" id="teacher_ids" class="form-control select2" multiple
                                style="width: 100%">
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" {{ is_array(old('teacher_ids')) && in_array($teacher->id, old('teacher_ids')) ? 'selected' : '' }}>
                                        {{ $teacher->name }} ({{ $teacher->contact_number }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Target Students Selection -->
                        <div id="student_select_container" class="mb-3 d-none">
                            <label for="student_ids" class="form-label">Select Students <span
                                    class="text-danger">*</span></label>
                            <select name="student_ids[]" id="student_ids" class="form-control select2" multiple
                                style="width: 100%">
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}" {{ is_array(old('student_ids')) && in_array($student->id, old('student_ids')) ? 'selected' : '' }}>
                                        {{ $student->name }} ({{ $student->contact_number }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Target Classes Selection -->
                        <div id="class_select_container" class="d-none">
                            <div class="mb-3">
                                <label for="class_ids" class="form-label">Select Classes <span
                                        class="text-danger">*</span></label>
                                <select name="class_ids[]" id="class_ids" class="form-control select2" multiple
                                    style="width: 100%">
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}" {{ is_array(old('class_ids')) && in_array($class->id, old('class_ids')) ? 'selected' : '' }}>
                                            {{ $class->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="class_target_type" class="form-label">Notify Who in these Classes? <span
                                        class="text-danger">*</span></label>
                                <select name="class_target_type" id="class_target_type" class="form-control form-select">
                                    <option value="both" {{ old('class_target_type') == 'both' ? 'selected' : '' }}>Both
                                        Teachers & Students</option>
                                    <option value="teachers" {{ old('class_target_type') == 'teachers' ? 'selected' : '' }}>
                                        Teachers Only</option>
                                    <option value="students" {{ old('class_target_type') == 'students' ? 'selected' : '' }}>
                                        Students Only</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route($routePrefix . '.holidays.index') }}" class="btn btn-light">Cancel</a>
                            <button type="submit" class="btn btn-primary">Announce Holidays/Alerts</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script src="{{ URL::asset('assets/libs/select2/select2.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            // Initialize Select2
            $('.select2').select2({
                placeholder: 'Select options...'
            });

            function toggleContainers() {
                var target = $('#target_type').val();

                // Hide all containers first
                $('#teacher_select_container').addClass('d-none');
                $('#student_select_container').addClass('d-none');
                $('#class_select_container').addClass('d-none');

                // Remove required attributes to prevent validation issues when hidden
                $('#teacher_ids').prop('required', false);
                $('#student_ids').prop('required', false);
                $('#class_ids').prop('required', false);
                $('#class_target_type').prop('required', false);

                // Show selected container
                if (target === 'selected_teachers') {
                    $('#teacher_select_container').removeClass('d-none');
                    $('#teacher_ids').prop('required', true);
                } else if (target === 'selected_students') {
                    $('#student_select_container').removeClass('d-none');
                    $('#student_ids').prop('required', true);
                } else if (target === 'classes') {
                    $('#class_select_container').removeClass('d-none');
                    $('#class_ids').prop('required', true);
                    $('#class_target_type').prop('required', true);
                }
            }

            // Bind change event and run initially
            $('#target_type').on('change', toggleContainers);
            toggleContainers();
        });
    </script>
@endsection